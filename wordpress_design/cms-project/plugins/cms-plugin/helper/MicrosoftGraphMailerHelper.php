<?php
/**
 * AH_Microsoft_Graph_Mailer - send transactional mail through the Microsoft Graph API.
 *
 * Lives here (not pasted into a Workflow CODE action) so the class is declared
 * exactly once per request. A `class` declaration inside eval() fatals with
 * "Cannot declare class ... already in use" the moment two rules - or two queued
 * actions in one cron batch - run the same snippet.
 *
 * Usage from a Workflow Manager CODE action:
 *
 *   $mail = new AH_Microsoft_Graph_Mailer(
 *       $ctx['tenant_id'],
 *       $ctx['client_id'],
 *       $ctx['client_secret'],
 *       $ctx['from_sender_mail'],
 *       $ctx['from_sender_name']
 *   );
 *   $mail->send( 'Subject', $html, $ctx['email'] );
 *
 * Key names depend on where the value is defined:
 *   Variable Profile / rule variable → $ctx['tenant_id']        (key as-is)
 *   Global Config Variable           → $ctx['config_tenant_id'] ("config_" prefix)
 * Read them from $ctx rather than writing {{tenant_id}} in the snippet: tokens are
 * substituted into the source before eval(), so a value containing a quote breaks
 * the PHP, and a value from a public form could inject code.
 *
 * @package Ah\Cms
 */

defined( 'ABSPATH' ) || exit;

class AH_Microsoft_Graph_Mailer {

	private string $tenant_id;
	private string $client_id;
	private string $client_secret;
	private string $sender_mail;
	private string $sender_name;

	/** Tokens are valid ~60 min; cache per credential set so a batch re-auths once. */
	private static array $token_cache = array();

	public function __construct(
		string $tenant_id,
		string $client_id,
		string $client_secret,
		string $sender_mail,
		string $sender_name = ''
	) {
		$this->tenant_id     = trim( $tenant_id );
		$this->client_id     = trim( $client_id );
		$this->client_secret = trim( $client_secret );
		$this->sender_mail   = trim( $sender_mail );
		$this->sender_name   = trim( $sender_name );
	}

	/**
	 * Client-credentials token, cached for the rest of the request.
	 *
	 * @throws Exception When Microsoft rejects the credentials.
	 */
	private function token(): string {
		$cache_key = md5( $this->tenant_id . '|' . $this->client_id . '|' . $this->client_secret );
		if ( ! empty( self::$token_cache[ $cache_key ] ) ) {
			return self::$token_cache[ $cache_key ];
		}

		$response = wp_remote_post(
			'https://login.microsoftonline.com/' . rawurlencode( $this->tenant_id ) . '/oauth2/v2.0/token',
			array(
				'timeout' => 20,
				'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
				'body'    => array(
					'client_id'     => $this->client_id,
					'client_secret' => $this->client_secret,
					'scope'         => 'https://graph.microsoft.com/.default',
					'grant_type'    => 'client_credentials',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Graph auth transport error: ' . $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['access_token'] ) ) {
			// error_description carries the real reason (bad secret, wrong tenant, ...).
			throw new Exception( 'Graph auth failed: ' . ( $body['error_description'] ?? wp_remote_retrieve_body( $response ) ) );
		}

		self::$token_cache[ $cache_key ] = (string) $body['access_token'];
		return self::$token_cache[ $cache_key ];
	}

	/** Accepts an array, a comma-separated string, or a single address. */
	private function recipients( $list ): array {
		if ( empty( $list ) ) {
			return array();
		}
		if ( ! is_array( $list ) ) {
			$list = explode( ',', (string) $list );
		}
		$out = array();
		foreach ( $list as $email ) {
			$email = trim( (string) $email );
			if ( '' !== $email && is_email( $email ) ) {
				$out[] = array( 'emailAddress' => array( 'address' => $email ) );
			}
		}
		return $out;
	}

	/**
	 * Send an HTML email.
	 *
	 * @param string       $subject   Subject line.
	 * @param string       $html      HTML body.
	 * @param array|string $to        Recipient(s).
	 * @param array|string $cc        Optional CC.
	 * @param array|string $bcc       Optional BCC.
	 * @param array|string $reply_to  Optional Reply-To.
	 * @return array{success:bool,http_code:int,response:string}
	 * @throws Exception On transport failure or when no valid recipient is given.
	 */
	public function send( string $subject, string $html, $to, $cc = array(), $bcc = array(), $reply_to = array() ): array {
		$to_list = $this->recipients( $to );
		if ( ! $to_list ) {
			throw new Exception( 'Graph send aborted: no valid "to" address.' );
		}

		$message = array(
			'subject'      => $subject,
			'body'         => array(
				'contentType' => 'HTML',
				'content'     => $html,
			),
			'toRecipients' => $to_list,
		);

		if ( '' !== $this->sender_name ) {
			$message['from'] = array(
				'emailAddress' => array(
					'address' => $this->sender_mail,
					'name'    => $this->sender_name,
				),
			);
		}
		foreach ( array( 'ccRecipients' => $cc, 'bccRecipients' => $bcc, 'replyTo' => $reply_to ) as $key => $value ) {
			$formatted = $this->recipients( $value );
			if ( $formatted ) {
				$message[ $key ] = $formatted;
			}
		}

		$response = wp_remote_post(
			'https://graph.microsoft.com/v1.0/users/' . rawurlencode( $this->sender_mail ) . '/sendMail',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->token(),
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'message'         => $message,
						'saveToSentItems' => true,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Graph send transport error: ' . $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = (string) wp_remote_retrieve_body( $response );

		// Throwing on a non-2xx makes the Workflow action log as "failed" with the
		// Graph error visible in Trigger Logs, instead of silently reporting success.
		if ( $code < 200 || $code >= 300 ) {
			throw new Exception( 'Graph send failed (HTTP ' . $code . '): ' . $body );
		}

		return array(
			'success'   => true,
			'http_code' => $code,
			'response'  => $body,
		);
	}
}
