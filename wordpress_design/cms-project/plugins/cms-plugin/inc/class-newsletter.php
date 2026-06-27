<?php
/**
 * inc/class-newsletter.php - Newsletter subscriber management.
 *
 * Table: {prefix}ah_newsletter_subscribers
 * One row per subscriber. Tracks email, optional name, source, and status.
 */

defined( 'ABSPATH' ) || exit;

class AH_Newsletter {

	const DB_VERSION = '1';
	const DB_OPTION  = 'ah_newsletter_db_v';

	// ── Table ────────────────────────────────────────────────────────────────

	public static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'ah_newsletter_subscribers';
	}

	private static function table_exists(): bool {
		global $wpdb;
		$t = self::table();
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t;
	}

	public static function install(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$t       = self::table();
		dbDelta( "CREATE TABLE {$t} (
			id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			email             VARCHAR(200)    NOT NULL,
			name              VARCHAR(200)    NOT NULL DEFAULT '',
			source            VARCHAR(100)    NOT NULL DEFAULT 'website',
			status            VARCHAR(20)     NOT NULL DEFAULT 'active',
			created_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			unsubscribed_at   DATETIME        DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY email (email)
		) {$charset};" );
		update_option( self::DB_OPTION, self::DB_VERSION );
	}

	public static function maybe_install(): void {
		if ( get_option( self::DB_OPTION ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	// ── Subscription ──────────────────────────────────────────────────────────

	/**
	 * Subscribe an email. Returns 'subscribed', 'already_subscribed', or 'error'.
	 */
	public static function subscribe( string $email, string $name = '', string $source = 'website' ): string {
		global $wpdb;
		if ( ! self::table_exists() ) { self::install(); }

		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) { return 'error'; }

		$existing = $wpdb->get_row(
			$wpdb->prepare( 'SELECT id, status FROM ' . self::table() . ' WHERE email = %s', $email ),
			ARRAY_A
		);

		if ( $existing ) {
			if ( 'active' === $existing['status'] ) {
				return 'already_subscribed';
			}
			// Re-subscribe.
			$wpdb->update(
				self::table(),
				array(
					'status'            => 'active',
					'name'              => sanitize_text_field( $name ),
					'source'            => sanitize_key( $source ),
					'unsubscribed_at'   => null,
				),
				array( 'id' => (int) $existing['id'] )
			);
			return 'subscribed';
		}

		$result = $wpdb->insert( self::table(), array(
			'email'      => $email,
			'name'       => sanitize_text_field( $name ),
			'source'     => sanitize_key( $source ),
			'status'     => 'active',
			'created_at' => current_time( 'mysql' ),
		) );

		return $result ? 'subscribed' : 'error';
	}

	/**
	 * Unsubscribe by email.
	 */
	public static function unsubscribe( string $email ): bool {
		global $wpdb;
		if ( ! self::table_exists() ) { return false; }
		return (bool) $wpdb->update(
			self::table(),
			array( 'status' => 'unsubscribed', 'unsubscribed_at' => current_time( 'mysql' ) ),
			array( 'email'  => sanitize_email( $email ) )
		);
	}

	/**
	 * Delete a subscriber by ID.
	 */
	public static function delete( int $id ): void {
		global $wpdb;
		if ( ! self::table_exists() ) { return; }
		$wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
	}

	// ── Queries ───────────────────────────────────────────────────────────────

	public static function get_all( string $status = '', int $limit = 200, int $offset = 0 ): array {
		global $wpdb;
		if ( ! self::table_exists() ) { return array(); }
		$t = self::table();
		if ( '' !== $status ) {
			return $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$t} WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d", $status, $limit, $offset ),
				ARRAY_A
			) ?: array();
		}
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$t} ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ),
			ARRAY_A
		) ?: array();
	}

	public static function count( string $status = '' ): int {
		global $wpdb;
		if ( ! self::table_exists() ) { return 0; }
		$t = self::table();
		if ( '' !== $status ) {
			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE status = %s", $status ) );
		}
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t}" );
	}

	// ── Unsubscribe token ────────────────────────────────────────────────────────

	/**
	 * Generate a one-way token for an email address (used in unsubscribe links).
	 */
	public static function unsub_token( string $email ): string {
		return substr( hash_hmac( 'sha256', strtolower( $email ), wp_salt( 'auth' ) ), 0, 32 );
	}

	/**
	 * Build the public unsubscribe URL for a given email.
	 */
	public static function unsub_url( string $email ): string {
		return add_query_arg( array(
			'ah_nl_unsub' => '1',
			'email'       => rawurlencode( $email ),
			'token'       => self::unsub_token( $email ),
		), home_url( '/' ) );
	}

	// ── Broadcast ─────────────────────────────────────────────────────────────

	/**
	 * Send a notification to all active subscribers via the Rules Engine.
	 *
	 * Fires AH_Workflow_Manager::evaluate( 'notification_send', $context, true ) for
	 * each active subscriber (immediate mode). The Rules Engine decides delivery -
	 * configure a rule with trigger "Notification – Send" and any action:
	 * send_email, WhatsApp, webhook, or a combination.
	 *
	 * Available context tokens in rule actions:
	 *   {email}  {name}  {subject}  {body}  {from_name}  {from_email}  {unsubscribe_url}
	 *
	 * Returns array( 'sent' => int, 'failed' => int ).
	 *
	 * @param string $subject
	 * @param string $body        Supports {name} and {unsubscribe_url} before passing to RE.
	 * @param string $from_name
	 * @param string $from_email
	 */
	public static function send_broadcast( string $subject, string $body, string $from_name = '', string $from_email = '' ): array {
		if ( ! self::table_exists() )             { return array( 'sent' => 0, 'failed' => 0 ); }
		if ( ! class_exists( 'AH_Workflow_Manager' ) ) { return array( 'sent' => 0, 'failed' => 0 ); }

		$from_email = $from_email ?: get_option( 'admin_email' );
		$from_name  = $from_name  ?: get_bloginfo( 'name' );

		$subscribers = self::get_all( 'active', 5000, 0 );
		$sent        = 0;

		foreach ( $subscribers as $sub ) {
			$unsub         = self::unsub_url( $sub['email'] );
			$name          = '' !== $sub['name'] ? $sub['name'] : 'there';
			$body_rendered = str_replace(
				array( '{name}', '{unsubscribe_url}' ),
				array( $name,    $unsub              ),
				$body
			);
			$body_rendered .= "\n\n---\nTo unsubscribe, visit: " . $unsub;

			AH_Workflow_Manager::evaluate( 'notification_send', array(
				'email'           => $sub['email'],
				'name'            => $name,
				'subject'         => $subject,
				'body'            => $body_rendered,
				'from_name'       => $from_name,
				'from_email'      => $from_email,
				'unsubscribe_url' => $unsub,
			), true );

			$sent++;
		}

		return array( 'sent' => $sent, 'failed' => 0 );
	}

	// ── Broadcast log (wp_option) ─────────────────────────────────────────────

	public static function log_broadcast( string $subject, int $sent, int $failed ): void {
		$log   = get_option( 'ah_newsletter_broadcasts', array() );
		$log[] = array(
			'subject' => $subject,
			'sent'    => $sent,
			'failed'  => $failed,
			'sent_at' => current_time( 'mysql' ),
		);
		// Keep last 50 entries.
		if ( count( $log ) > 50 ) { $log = array_slice( $log, -50 ); }
		update_option( 'ah_newsletter_broadcasts', $log );
	}

	public static function get_broadcast_log(): array {
		$log = get_option( 'ah_newsletter_broadcasts', array() );
		return array_reverse( $log );
	}

	// ── Export ────────────────────────────────────────────────────────────────

	/**
	 * Export all active subscribers as CSV string.
	 */
	public static function export_csv(): string {
		$rows = self::get_all( 'active', 10000 );
		if ( empty( $rows ) ) { return 'email,name,source,subscribed_at' . "\n"; }
		$out = 'email,name,source,subscribed_at' . "\n";
		foreach ( $rows as $row ) {
			$out .= sprintf(
				"%s,%s,%s,%s\n",
				'"' . str_replace( '"', '""', $row['email'] ) . '"',
				'"' . str_replace( '"', '""', $row['name'] ) . '"',
				'"' . str_replace( '"', '""', $row['source'] ) . '"',
				'"' . str_replace( '"', '""', $row['created_at'] ) . '"'
			);
		}
		return $out;
	}
}
