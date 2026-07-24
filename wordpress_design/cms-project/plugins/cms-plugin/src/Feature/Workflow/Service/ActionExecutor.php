<?php

namespace Ah\Cms\Feature\Workflow\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Action Executor — dispatches workflow actions (email, WhatsApp, HTTP, curl, code, update_option).
 * Contains all action implementations, template interpolation, and curl helpers.
 */
class ActionExecutor {

	// ── Action runner ─────────────────────────────────────────────────────────

	/**
	 * Execute a list of actions against a context.
	 *
	 * @param array $actions  Action configurations.
	 * @param array $context  Template variables.
	 * @return array Results for each action.
	 */
	public static function execute( array $actions, array $context ): array {
		$cfg     = RuleEngine::getConfig();
		$results = array();
		foreach ( $actions as $a ) {
			$type = $a['type'] ?? '';
			if ( 'send_email' === $type ) {
				$channel = ! empty( $a['channel_id'] ) ? RuleEngine::getEmailChannel( $a['channel_id'] ) : null;
				if ( $channel ) {
					$method = $channel['email_send_method'] ?? null;
					if ( $method === 'smtp' ) {
						$result = self::actionEmailSmtp( $a, $context );
					} elseif ( $method === 'api' ) {
						$result = self::actionEmail( $a, $context );
					} else {
						if ( ! empty( $channel['host'] ) ) {
							$result = self::actionEmailSmtp( $a, $context );
						} elseif ( ! empty( $channel['api_key'] ) || ! empty( $channel['password'] ) ) {
							$result = self::actionEmail( $a, $context );
						} else {
							$result = ( 'smtp' === ( $cfg['email_send_method'] ?? 'api' ) ) ? self::actionEmailSmtp( $a, $context ) : self::actionEmail( $a, $context );
						}
					}
				} else {
					$result = ( 'smtp' === ( $cfg['email_send_method'] ?? 'api' ) ) ? self::actionEmailSmtp( $a, $context ) : self::actionEmail( $a, $context );
				}
			} else {
				$result = match ( $type ) {
					'whatsapp'      => self::actionWhatsapp( $a, $context ),
					'http_request'  => self::actionHttp( $a, $context ),
					'curl_command'  => self::actionCurlCommand( $a, $context ),
					'code'          => self::actionCode( $a, $context ),
					'update_option' => self::actionUpdateOption( $a, $context ),
					default         => array(),
				};
			}
			$results[] = is_array( $result ) ? $result : array();
		}
		return $results;
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	private static function actionResultSummary( string $channel_name, int $response_code, ?string $body = null, ?string $message_id = null ): string {
		$parts = array( trim( $channel_name ) );
		if ( $response_code > 0 ) {
			$parts[] = 'HTTP ' . $response_code;
		}
		if ( $message_id ) {
			$parts[] = 'messageId=' . $message_id;
		}
		if ( $body ) {
			$parts[] = self::trimLogText( $body, 220 );
		}
		return trim( implode( ' | ', array_filter( $parts ) ) );
	}

	public static function trimLogText( string $text, int $limit = 320 ): string {
		$text = trim( preg_replace( '/\s+/', ' ', $text ) ?? $text );
		return mb_strimwidth( $text, 0, $limit, '…' );
	}

	public static function configTokens(): array {
		$tokens = array();
		foreach ( RuleEngine::getConfig() as $k => $v ) {
			$tokens[ 'config_' . $k ] = $v;
		}
		foreach ( RuleEngine::getCustomVars() as $var ) {
			if ( ! empty( $var['key'] ) ) {
				$tokens[ 'config_' . $var['key'] ] = $var['value'] ?? '';
			}
		}
		return $tokens;
	}

	// ── Placeholder interpolation ─────────────────────────────────────────────

	/**
	 * Replace {key} tokens with context values (plain text - no escaping).
	 */
	public static function fill( string $tpl, array $ctx ): string {
		foreach ( $ctx as $k => $v ) {
			$val = (string) $v;
			$tpl = str_replace( '{{' . $k . '}}', $val, $tpl );
			$tpl = str_replace( '((' . $k . '))', $val, $tpl );
		}
		$tpl = preg_replace_callback('/\{\{eval:(.*?)\}\}/is', function( $m ) {
			$expr = trim( $m[1] );
			if ( preg_match( '/^[0-9\+\-\*\/\.\(\)\s\>\<\=\!]+$/', $expr ) ) {
				try {
					$result = eval( 'return ' . $expr . ';' );
					if ( is_bool( $result ) ) {
						return $result ? '1' : '0';
					}
					return (string) $result;
				} catch ( \Throwable $e ) { }
			}
			return $m[0];
		}, $tpl);
		return $tpl;
	}

	/**
	 * Replace token - values are HTML-escaped (safe for HTML email).
	 */
	public static function fillHtml( string $tpl, array $ctx ): string {
		foreach ( $ctx as $k => $v ) {
			$val = esc_html( (string) $v );
			$tpl = str_replace( '{{' . $k . '}}', $val, $tpl );
			$tpl = str_replace( '((' . $k . '))', $val, $tpl );
		}
		$tpl = preg_replace_callback('/\{\{eval:(.*?)\}\}/is', function( $m ) {
			$expr = trim( $m[1] );
			if ( preg_match( '/^[0-9\+\-\*\/\.\(\)\s\>\<\=\!]+$/', $expr ) ) {
				try {
					$result = eval( 'return ' . $expr . ';' );
					if ( is_bool( $result ) ) {
						return $result ? '1' : '0';
					}
					return esc_html( (string) $result );
				} catch ( \Throwable $e ) { }
			}
			return $m[0];
		}, $tpl);
		return $tpl;
	}

	// ── send_email (SMTP) ────────────────────────────────────────────────────

	private static function actionEmailSmtp( array $a, array $context ): array {
		$ctx = array_merge( self::configTokens(), $context );
		$cfg = RuleEngine::getConfig();

		$to_list = is_array( $a['to'] ?? null ) ? $a['to'] : array( $a['to'] ?? '' );
		$to_recipients = array();
		foreach ( $to_list as $to_addr ) {
			$filled = self::fill( (string) $to_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email && ! RuleEngine::isEmailBlocked( $email ) ) $to_recipients[] = $email;
		}
		if ( empty( $to_recipients ) ) {
			throw new \Exception( 'No valid recipient email addresses were provided for this email action.' );
		}
		$to = implode( ', ', $to_recipients );

		$subject  = self::fill( $a['subject'] ?? 'Notification', $ctx );
		$body_tpl = $a['body'] ?? '';
		$is_html  = ! empty( $a['html'] );

		$body = $is_html
			? self::fillHtml( $body_tpl, $ctx )
			: self::fill( $body_tpl, $ctx );

		$headers = array( 'Content-Type: ' . ( $is_html ? 'text/html' : 'text/plain' ) . '; charset=UTF-8' );

		$channel = ! empty( $a['channel_id'] ) ? RuleEngine::getEmailChannel( $a['channel_id'] ) : null;

		$from_name = sanitize_text_field(
			$channel ? $channel['from_name'] : $cfg['email_from_name']
		);
		$from_email = sanitize_email(
			$channel ? $channel['from_email'] : $cfg['email_from_email']
		);
		if ( $from_name || $from_email ) {
			$headers[] = "From: {$from_name} <{$from_email}>";
		}

		// Handle CC as array
		$cc_list = is_array( $a['cc'] ?? null ) ? $a['cc'] : ( ! empty( $a['cc'] ) ? array( $a['cc'] ) : array() );
		$cc_recipients = array();
		foreach ( $cc_list as $cc_addr ) {
			$filled = self::fill( (string) $cc_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email ) $cc_recipients[] = $email;
		}
		if ( ! empty( $cc_recipients ) ) {
			$headers[] = 'CC: ' . implode( ', ', $cc_recipients );
		}

		// Handle BCC - from rule action AND global config
		$bcc_list = is_array( $a['bcc'] ?? null ) ? $a['bcc'] : ( ! empty( $a['bcc'] ) ? array( $a['bcc'] ) : array() );
		$bcc_recipients = array();
		foreach ( $bcc_list as $bcc_addr ) {
			$filled = self::fill( (string) $bcc_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email ) $bcc_recipients[] = $email;
		}
		if ( ! empty( $context['_direct_bcc'] ) ) {
			foreach ( (array) $context['_direct_bcc'] as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $bcc_recipients, true ) ) {
					$bcc_recipients[] = $email;
				}
			}
		}
		if ( ! empty( $cfg['email_bcc'] ) ) {
			$global_bcc = is_array( $cfg['email_bcc'] ) ? $cfg['email_bcc'] : array_filter( array_map( 'trim', explode( ',', $cfg['email_bcc'] ) ) );
			foreach ( $global_bcc as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $bcc_recipients, true ) ) {
					$bcc_recipients[] = $email;
				}
			}
		}
		if ( ! empty( $bcc_recipients ) ) {
			$headers[] = 'BCC: ' . implode( ', ', $bcc_recipients );
		}

		// Per-channel SMTP override via phpmailer_init
		$smtp_hook = null;
		if ( $channel && ! empty( $channel['host'] ) ) {
			$ch        = $channel;
			$smtp_hook = static function ( $mailer ) use ( $ch, $is_html ) {
				$mailer->isSMTP();
				$mailer->Host       = $ch['host'];
				$mailer->Port       = (int) $ch['port'];
				$mailer->SMTPAuth   = ( '' !== (string) $ch['username'] );
				$mailer->Username   = $ch['username'];
				$mailer->Password   = $ch['password'];
				$enc = $ch['encryption'] ?? 'tls';
				$mailer->SMTPSecure = ( 'ssl' === $enc ) ? 'ssl' : ( 'none' === $enc ? false : 'tls' );
				if ( 'none' === $enc ) {
					$mailer->SMTPAutoTLS = false;
				}
				$mailer->SMTPOptions = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true
					)
				);
				$mailer->isHTML( $is_html );
				$mailer->SMTPDebug   = 2;
				$mailer->Debugoutput = function ( $str, $level ) {
					error_log( "PHPMailer debug: $str" );
				};
			};
			add_action( 'phpmailer_init', $smtp_hook );
		}

		$_mail_error = '';
		$error_catcher = function ( $wp_error ) use ( &$_mail_error ) {
			if ( is_wp_error( $wp_error ) ) {
				$_mail_error = $wp_error->get_error_message();
			}
		};
		add_action( 'wp_mail_failed', $error_catcher );

		$status = wp_mail( $to, $subject, $body, $headers );

		remove_action( 'wp_mail_failed', $error_catcher );

		if ( $smtp_hook ) {
			remove_action( 'phpmailer_init', $smtp_hook );
		}

		if ( ! $status ) {
			$err_msg = ! empty( $_mail_error ) ? $_mail_error : 'wp_mail returned false (unknown error).';
			if ( $channel ) {
				$err_msg .= "\n\n--- Channel Config Dump ---\n";
				$err_msg .= "Host: " . ( $channel['host'] ?? 'N/A' ) . "\n";
				$err_msg .= "Port: " . ( $channel['port'] ?? 'N/A' ) . "\n";
				$err_msg .= "Encryption: " . ( $channel['encryption'] ?? 'N/A' ) . "\n";
				$err_msg .= "Username: " . ( $channel['username'] ?? 'N/A' ) . "\n";
				$err_msg .= "From: $from_name <$from_email>\n";
			}
			throw new \Exception( $err_msg );
		}

		return array(
			'status'          => 'sent',
			'response_summary' => self::actionResultSummary(
				'SMPP/SMTP',
				200,
				$to,
				null
			) . ' | ' . self::trimLogText( sprintf(
				"Channel: host=%s port=%s enc=%s from=%s",
					$channel['host'] ?? 'N/A',
					$channel['port'] ?? 'N/A',
					$channel['encryption'] ?? 'N/A',
					( isset( $from_name ) && isset( $from_email ) ) ? ( $from_name . ' <' . $from_email . '>' ) : ''
				), 220 ),
		);
	}

	// ── send_email (API/Brevo) ───────────────────────────────────────────────

	private static function actionEmail( array $a, array $context ): array {
		$ctx = array_merge( self::configTokens(), $context );
		$cfg = RuleEngine::getConfig();

		$to_list = is_array( $a['to'] ?? null ) ? $a['to'] : array( $a['to'] ?? '' );
		$to_recipients = array();
		foreach ( $to_list as $to_addr ) {
			$filled = self::fill( (string) $to_addr, $ctx );
			$email = sanitize_email( $filled );
			if ( $email && ! RuleEngine::isEmailBlocked( $email ) ) $to_recipients[] = array( 'email' => $email );
		}
		if ( empty( $to_recipients ) ) {
			throw new \Exception( 'No valid recipient email addresses were provided for this email action.' );
		}

		$subject  = self::fill( $a['subject'] ?? 'Notification', $ctx );
		$body_tpl = $a['body'] ?? '';
		$is_html  = ! empty( $a['html'] );
		$body     = $is_html ? self::fillHtml( $body_tpl, $ctx ) : self::fill( $body_tpl, $ctx );

		$channel = ! empty( $a['channel_id'] ) ? RuleEngine::getEmailChannel( $a['channel_id'] ) : null;

		$api_key = $channel['api_key'] ?? $channel['password'] ?? '';
		if ( ! $api_key ) {
			throw new \Exception( 'No Brevo API key configured for this channel.' );
		}

		$sender_name  = sanitize_text_field( $channel['from_name'] ?? ( $cfg['email_from_name'] ?? '' ) );
		$sender_email = sanitize_email( $channel['from_email'] ?? ( $cfg['email_from_email'] ?? '' ) );
		if ( ! $sender_email ) {
			throw new \Exception( 'No sender email configured for this Brevo channel.' );
		}

		// Handle CC as array
		$cc_list = is_array( $a['cc'] ?? null ) ? $a['cc'] : ( ! empty( $a['cc'] ) ? array( $a['cc'] ) : array() );
		$cc_recipients = array();
		foreach ( $cc_list as $cc_addr ) {
			$email = sanitize_email( self::fill( (string) $cc_addr, $ctx ) );
			if ( $email ) $cc_recipients[] = array( 'email' => $email );
		}

		// Handle BCC - from rule action AND global config
		$bcc_list = is_array( $a['bcc'] ?? null ) ? $a['bcc'] : ( ! empty( $a['bcc'] ) ? array( $a['bcc'] ) : array() );
		$bcc_recipients = array();
		foreach ( $bcc_list as $bcc_addr ) {
			$email = sanitize_email( self::fill( (string) $bcc_addr, $ctx ) );
			if ( $email ) $bcc_recipients[] = array( 'email' => $email );
		}
		if ( ! empty( $context['_direct_bcc'] ) ) {
			$existing_bcc = array_column( $bcc_recipients, 'email' );
			foreach ( (array) $context['_direct_bcc'] as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $existing_bcc, true ) ) {
					$bcc_recipients[] = array( 'email' => $email );
					$existing_bcc[] = $email;
				}
			}
		}
		if ( ! empty( $cfg['email_bcc'] ) ) {
			$global_bcc = is_array( $cfg['email_bcc'] ) ? $cfg['email_bcc'] : array_filter( array_map( 'trim', explode( ',', $cfg['email_bcc'] ) ) );
			$existing = array_column( $bcc_recipients, 'email' );
			foreach ( $global_bcc as $bcc_addr ) {
				$email = sanitize_email( $bcc_addr );
				if ( $email && ! in_array( $email, $existing, true ) ) {
					$bcc_recipients[] = array( 'email' => $email );
					$existing[] = $email;
				}
			}
		}

		$payload = array(
			'sender'  => array( 'name' => $sender_name, 'email' => $sender_email ),
			'to'      => $to_recipients,
			'subject' => $subject,
		);
		$is_html ? $payload['htmlContent'] = $body : $payload['textContent'] = $body;
		if ( ! empty( $cc_recipients ) )  $payload['cc']  = $cc_recipients;
		if ( ! empty( $bcc_recipients ) ) $payload['bcc'] = $bcc_recipients;

		$endpoint = $channel['api_endpoint'] ?? 'https://api.brevo.com/v3/smtp/email';
		$response = wp_remote_post( $endpoint, array(
			'timeout' => 15,
			'headers' => array(
				'api-key'      => $api_key,
				'Content-Type' => 'application/json',
				'accept'       => 'application/json',
			),
			'body' => wp_json_encode( $payload ),
		) );

		$curl_str = "curl -X POST " . $endpoint . " \\\n+";
		$curl_str .= "-H \"api-key: " . ( $api_key ? substr($api_key, 0, 8) . '...' : 'MISSING' ) . "\" \\\n";
		$curl_str .= "-H \"Content-Type: application/json\" \\\n";
		$curl_str .= "-d '" . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES ) . "'";

		$dump = "\n\n--- API Config Dump ---\n";
		$dump .= "Endpoint: " . $endpoint . "\n";
		$dump .= "cURL Equivalent:\n" . $curl_str;

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() . $dump );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$resp_body = wp_remote_retrieve_body( $response );
		if ( $code < 200 || $code >= 300 ) {
			$err = json_decode( $resp_body, true );
			$msg = $err['message'] ?? $err['error'] ?? ( $resp_body ?: "Brevo API returned HTTP {$code}." );
			throw new \Exception( $msg . $dump );
		}

		$decoded = json_decode( $resp_body, true );
		$message_id = '';
		if ( is_array( $decoded ) ) {
			$message_id = (string) ( $decoded['messageId'] ?? $decoded['message_id'] ?? $decoded['id'] ?? '' );
		}

		$response_summary = self::actionResultSummary(
			'Brevo API',
			$code,
			is_array( $decoded ) ? wp_json_encode( $decoded, JSON_UNESCAPED_SLASHES ) : $resp_body,
			$message_id ?: null
		);

		$response_summary .= ' | ' . self::trimLogText( $curl_str, 220 );

		error_log( 'AH_Workflow_Manager email success: ' . $response_summary );

		return array(
			'status'           => 'sent',
			'response_summary' => $response_summary,
		);
	}

	// ── whatsapp ──────────────────────────────────────────────────────────────

	private static function actionWhatsapp( array $a, array $context ): void {
		$ctx   = array_merge( self::configTokens(), $context );
		$cfg   = RuleEngine::getConfig();
		$url   = esc_url_raw( self::fill( ! empty( $a['api_url'] ) ? $a['api_url'] : $cfg['wa_api_url'], $ctx ) );
		$token = self::fill( ! empty( $a['auth_token'] ) ? $a['auth_token'] : $cfg['wa_auth_token'], $ctx );
		$phone = preg_replace( '/\D/', '', self::fill( $a['to_phone'] ?? '', $ctx ) );
		$msg   = self::fill( $a['message'] ?? '', $ctx );

		if ( ! $url || ! $phone || ! $msg ) return;

		if ( ! empty( $a['body_json'] ) ) {
			$body_raw = self::fill( $a['body_json'], $ctx );
			$body     = json_decode( $body_raw, true );
		} else {
			$body = array(
				'to'          => $phone,
				'type'        => 'text',
				'text'        => array( 'body' => $msg ),
			);
		}

		$headers = array( 'Content-Type' => 'application/json' );
		if ( $token ) {
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => 15,
		) );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'WhatsApp API connection failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			$body_resp = wp_remote_retrieve_body( $response );
			throw new \Exception( sprintf( 'WhatsApp API returned HTTP %d: %s', $code, mb_strimwidth( $body_resp, 0, 150, '...' ) ) );
		}
	}

	// ── http_request ──────────────────────────────────────────────────────────

	private static function actionHttp( array $a, array $context ): void {
		$ctx    = array_merge( self::configTokens(), $context );
		$url    = esc_url_raw( self::fill( $a['url'] ?? '', $ctx ) );
		$method = strtoupper( $a['method'] ?? 'POST' );
		if ( ! $url ) return;

		$headers  = array();
		$raw_hdrs = self::fill( $a['headers'] ?? '', $ctx );
		if ( $raw_hdrs ) {
			$decoded = json_decode( $raw_hdrs, true );
			if ( is_array( $decoded ) ) {
				$headers = $decoded;
			} else {
				foreach ( explode( "\n", $raw_hdrs ) as $line ) {
					if ( str_contains( $line, ':' ) ) {
						[ $k, $v ] = explode( ':', $line, 2 );
						$headers[ trim( $k ) ] = trim( $v );
					}
				}
			}
		}

		$auth_type = $a['auth_type'] ?? 'none';
		if ( 'bearer' === $auth_type && ! empty( $a['auth_value'] ) ) {
			$headers['Authorization'] = 'Bearer ' . self::fill( $a['auth_value'], $ctx );
		} elseif ( 'basic' === $auth_type && ! empty( $a['auth_value'] ) ) {
			$headers['Authorization'] = 'Basic ' . base64_encode( self::fill( $a['auth_value'], $ctx ) );
		}

		$body_tpl     = $a['body'] ?? '';
		$content_type = $a['content_type'] ?? 'json';

		if ( 'json' === $content_type ) {
			$filled = self::fill( $body_tpl, $ctx );
			$headers['Content-Type'] = 'application/json';
			$body = $filled;
		} else {
			$tpl_data = json_decode( $body_tpl, true ) ?: array();
			$body = array();
			foreach ( $tpl_data as $k => $v ) {
				$body[ $k ] = self::fill( (string) $v, $ctx );
			}
		}

		$args = array(
			'method'  => $method,
			'headers' => $headers,
			'timeout' => 15,
		);
		if ( 'GET' !== $method ) $args['body'] = $body;

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'HTTP request connection failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			$body_resp = wp_remote_retrieve_body( $response );
			throw new \Exception( sprintf( 'HTTP request returned HTTP %d: %s', $code, mb_strimwidth( $body_resp, 0, 150, '...' ) ) );
		}
	}

	// ── curl_command ──────────────────────────────────────────────────────────

	private static function actionCurlCommand( array $a, array $context ): array {
		$ctx = array_merge( self::configTokens(), $context );
		$curl_str = self::fill( $a['curl_string'] ?? '', $ctx );
		if ( ! $curl_str ) {
			return array(
				'status' => 'skipped',
				'response_summary' => 'No cURL command provided.',
			);
		}

		$status_file = self::fill( $a['status_file'] ?? '', $ctx );
		if ( ! $status_file ) {
			$status_file = self::getActionStatusFilePath( 'curl' );
		}

		if ( ! str_starts_with( trim( $curl_str ), 'curl ' ) ) {
			$message = 'Invalid cURL command: must start with "curl ".';
			self::writeActionStatusFile( $status_file, 'failure', array( 'message' => $message ) );
			throw new \Exception( $message );
		}

		$curl_request = self::parseCurlCommandToRequestOptions( $curl_str );
		if ( $curl_request === null ) {
			$message = 'Unable to parse cURL command into HTTP request options.';
			self::writeActionStatusFile( $status_file, 'failure', array( 'message' => $message ) );
			throw new \Exception( $message );
		}

		try {
			$shell_available = self::isShellCommandAvailable() && strtoupper( substr( PHP_OS, 0, 3 ) ) !== 'WIN';
			if ( $shell_available ) {
				$curl_str = self::prepareWindowsCurlCommand( $curl_str );

				error_log( 'AH_Workflow_Manager::action_curl_command execute shell: ' . $curl_str );
				$output = array();
				$result = -1;
				self::runCommandWithOutput( $curl_str, $output, $result );

				if ( $result === 0 ) {
					$output_text = implode( ' ', array_filter( $output ) );
					self::writeActionStatusFile( $status_file, 'success', array( 'path' => $status_file, 'method' => 'shell' ) );
					return array(
						'status' => 'sent',
						'response_summary' => self::trimLogText(
							'cURL command executed successfully via shell.' . ( $output_text ? ' | ' . $output_text : '' ),
							220
						),
					);
				}

				error_log( 'AH_Workflow_Manager::action_curl_command shell failed: ' . $curl_str . ' | output: ' . implode( " \n", $output ) );
			} else {
				error_log( 'AH_Workflow_Manager::action_curl_command skipping shell execution on Windows or disabled shell functions' );
			}

			error_log( 'AH_Workflow_Manager::action_curl_command fallback to WP_HTTP' );
			$http_result = self::runWpRemoteForCurlRequest( $curl_request );
			self::writeActionStatusFile( $status_file, 'success', array( 'path' => $status_file, 'method' => 'wp_remote_request' ) );

			return array(
				'status' => 'sent',
				'response_summary' => self::trimLogText(
					self::actionResultSummary(
						'cURL',
						$http_result['code'] ?? 0,
						$http_result['body'] ?? null,
						null
					) . ' | ' . sprintf(
						'%s %s',
						$curl_request['args']['method'] ?? 'GET',
						$curl_request['url'] ?? ''
					),
					240
				),
			);
		} catch ( \Throwable $e ) {
			self::writeActionStatusFile( $status_file, 'failure', array( 'message' => $e->getMessage() ) );
			throw $e;
		}
	}

	private static function runCommandWithOutput( string $cmd, array &$output, int &$exitCode ): void {
		$output = array();
		$exitCode = -1;
		$disabled = strtolower( ini_get( 'disable_functions' ) );

		if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
			if ( function_exists( 'proc_open' ) && strpos( $disabled, 'proc_open' ) === false ) {
				$args = self::parseCommandString( $cmd );
				$descriptors = array(
					1 => array( 'pipe', 'w' ),
					2 => array( 'pipe', 'w' ),
				);
				$process = proc_open( $args, $descriptors, $pipes );
				if ( is_resource( $process ) ) {
					$stdout = stream_get_contents( $pipes[1] );
					$stderr = stream_get_contents( $pipes[2] );
					fclose( $pipes[1] );
					fclose( $pipes[2] );
					$exitCode = proc_close( $process );
					$output = array_filter( array_merge( explode( "\n", $stdout ), explode( "\n", $stderr ) ) );
					return;
				}
			}

			if ( function_exists( 'exec' ) && strpos( $disabled, 'exec' ) === false ) {
				exec( $cmd . ' 2>&1', $output, $exitCode );
				return;
			}

			return;
		}

		exec( $cmd . ' 2>&1', $output, $exitCode );
	}

	private static function isShellCommandAvailable(): bool {
		$disabled = strtolower( ini_get( 'disable_functions' ) );
		return ( function_exists( 'proc_open' ) && strpos( $disabled, 'proc_open' ) === false ) || ( function_exists( 'exec' ) && strpos( $disabled, 'exec' ) === false );
	}

	private static function runWpRemoteForCurlRequest( array $request ): array {
		$url = $request['url'] ?? '';
		$args = $request['args'] ?? array();

		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'WP HTTP request failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( $code < 200 || $code >= 300 ) {
			throw new \Exception( sprintf( 'WP HTTP request returned HTTP %d: %s', $code, mb_strimwidth( $body, 0, 150, '...' ) ) );
		}

		return array(
			'code' => $code,
			'body' => $body,
		);
	}

	private static function writeActionStatusFile( string $path, string $status, array $data = array() ): void {
		if ( ! $path ) {
			return;
		}

		$payload = array_merge(
			array(
				'status'    => $status,
				'timestamp' => current_time( 'mysql' ),
			),
			$data
		);

		$dir = dirname( $path );
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true );
		}

		@file_put_contents( $path, wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
	}

	private static function getActionStatusFilePath( string $prefix ): string {
		$path = wp_tempnam( "ah-{$prefix}-status-" );
		if ( ! $path ) {
			$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "ah-{$prefix}-status-" . uniqid() . '.json';
		}
		return $path;
	}

	private static function parseCurlCommandToRequestOptions( string $curl_str ): ?array {
		$opts = array(
			'url'     => '',
			'headers' => array(),
			'method'  => 'GET',
			'body'    => null,
		);

		$tokens = self::parseCommandString( $curl_str );
		$current = null;
		$use_body = false;

		foreach ( $tokens as $token ) {
			if ( $current ) {
				switch ( $current ) {
					case '-X':
					case '--request':
						$opts['method'] = strtoupper( $token );
						break;
					case '--url':
						$opts['url'] = $token;
						break;
					case '-H':
					case '--header':
						if ( str_contains( $token, ':' ) ) {
							[ $name, $value ] = explode( ':', $token, 2 );
							$opts['headers'][ trim( $name ) ] = trim( $value );
						}
						break;
					case '-d':
					case '--data':
					case '--data-raw':
					case '--data-binary':
						$opts['body'] = $token;
						$use_body = true;
						break;
				}
				$current = null;
				continue;
			}

			if ( str_starts_with( $token, 'curl' ) ) {
				continue;
			}

			if ( str_starts_with( $token, 'http://' ) || str_starts_with( $token, 'https://' ) ) {
				$opts['url'] = $token;
				continue;
			}

			if ( in_array( $token, array( '-X', '--request', '--url', '-H', '--header', '-d', '--data', '--data-raw', '--data-binary' ), true ) ) {
				$current = $token;
				if ( in_array( $token, array( '-d', '--data', '--data-raw', '--data-binary' ), true ) ) {
					$opts['method'] = 'POST';
				}
				continue;
			}

			if ( str_starts_with( $token, '--url=' ) ) {
				$opts['url'] = substr( $token, 6 );
				continue;
			}

			if ( str_starts_with( $token, '-H' ) && strlen( $token ) > 2 ) {
				$hdr = substr( $token, 2 );
				if ( str_contains( $hdr, ':' ) ) {
					[ $name, $value ] = explode( ':', $hdr, 2 );
					$opts['headers'][ trim( $name ) ] = trim( $value );
				}
				continue;
			}

			if ( str_starts_with( $token, '-d' ) && strlen( $token ) > 2 ) {
				$opts['body'] = substr( $token, 2 );
				$use_body = true;
				$opts['method'] = 'POST';
				continue;
			}
		}

		if ( ! $opts['url'] ) {
			return null;
		}

		$args = array(
			'method'  => $opts['method'],
			'headers' => $opts['headers'],
			'timeout' => 15,
		);

		if ( $use_body ) {
			$args['body'] = $opts['body'];
		}

		return array(
			'url'  => $opts['url'],
			'args' => $args,
		);
	}

	private static function parseCommandString( string $cmd ): array {
		$args = array();
		$length = strlen( $cmd );
		$index = 0;

		while ( $index < $length ) {
			while ( $index < $length && ctype_space( $cmd[ $index ] ) ) {
				$index++;
			}

			if ( $index >= $length ) {
				break;
			}

			$char = $cmd[ $index ];
			$token = '';

			if ( $char === '"' || $char === "'" ) {
				$quote = $char;
				$index++;
				while ( $index < $length ) {
					$c = $cmd[ $index++ ];
					if ( $c === $quote ) {
						break;
					}
					if ( $c === '\\' && $index < $length ) {
						$next = $cmd[ $index++ ];
						if ( $next === "\r" || $next === "\n" ) {
							while ( $index < $length && ctype_space( $cmd[ $index ] ) ) {
								$index++;
							}
							continue;
						}
						$token .= $next;
						continue;
					}
					$token .= $c;
				}
			} else {
				while ( $index < $length && ! ctype_space( $cmd[ $index ] ) ) {
					$c = $cmd[ $index++ ];
					if ( $c === '\\' && $index < $length ) {
						$next = $cmd[ $index++ ];
						if ( $next === "\r" || $next === "\n" ) {
							while ( $index < $length && ctype_space( $cmd[ $index ] ) ) {
								$index++;
							}
							continue;
						}
						$token .= $next;
						continue;
					}
					$token .= $c;
				}
			}

			if ( $token !== '' ) {
				$args[] = $token;
			}
		}

		return $args;
	}

	private static function prepareWindowsCurlCommand( string $curl_str ): string {
		$curl_str = str_replace( array( "\r", "\n" ), ' ', $curl_str );
		$curl_str = str_replace( array( '\\"', "\\'" ), array( '"', "'" ), $curl_str );

		$curl_str = preg_replace_callback(
			'/\b(-H|--header)\s+(?:\'([^\']*)\'|"([^"]*)")/i',
			function ( $matches ) {
				$value = isset( $matches[2] ) && $matches[2] !== '' ? $matches[2] : $matches[3];
				$value = str_replace( '"', '\\"', $value );
				return $matches[1] . ' "' . $value . '"';
			},
			$curl_str
		);

		$curl_str = preg_replace_callback(
			'/\b(-d|--data|--data-raw|--data-binary)\s+(?:\'([^\']*)\'|"([^"]*)")/i',
			function ( $matches ) {
				$data = isset( $matches[2] ) && $matches[2] !== '' ? $matches[2] : $matches[3];
				$data = str_replace( '\\"', '"', $data );
				$tmp_file = wp_tempnam( 'ah-curl-body-' );
				if ( ! $tmp_file ) {
					$tmp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ah-curl-body-' . uniqid() . '.txt';
				}
				file_put_contents( $tmp_file, $data );
				if ( str_contains( $tmp_file, ' ' ) ) {
					return $matches[1] . ' @"' . str_replace( '"', '\\"', $tmp_file ) . '"';
				}
				return $matches[1] . ' @' . $tmp_file;
			},
			$curl_str
		);

		return $curl_str;
	}

	// ── code ──────────────────────────────────────────────────────────────────

	private static function actionCode( array $a, array $context ): void {
		$ctx = array_merge( self::configTokens(), $context );
		$code = trim( self::fill( $a['code'] ?? '', $ctx ) );
		if ( ! $code ) {
			return;
		}

		try {
			eval( $code );
		} catch ( \Throwable $e ) {
			throw new \Exception( 'CODE execution failed: ' . $e->getMessage() );
		}
	}

	public static function escapeForCurlTemplate( $value ): string {
		if ( is_array( $value ) ) {
			return $value;
		}

		$value = (string) $value;
		$jsonEscaped = substr( json_encode( $value ), 1, -1 );
		return str_replace( "'", "'\\''", $jsonEscaped );
	}

	// ── update_option ─────────────────────────────────────────────────────────

	private static function actionUpdateOption( array $a, array $context ): void {
		$ctx = array_merge( self::configTokens(), $context );
		$key = sanitize_key( self::fill( $a['option_key'] ?? '', $ctx ) );
		if ( ! $key ) return;
		$value = sanitize_text_field( self::fill( $a['option_value'] ?? '', $ctx ) );
		update_option( $key, $value );
	}
}
