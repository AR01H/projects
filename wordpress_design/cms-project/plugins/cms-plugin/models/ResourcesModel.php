<?php
defined( 'ABSPATH' ) || exit;

class AH_Resources_Model extends AH_Model_Base {

	protected string $table_suffix = 'resources';

	public static function ensure_table(): void {
		global $wpdb;
		$t  = $wpdb->prefix . 'ah_resources';
		$cs = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$t}'" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return;
		}

		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"CREATE TABLE IF NOT EXISTS `{$t}` (
				id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				type          ENUM('youtube','shorts','instagram','facebook','twitter','tiktok','image','audio','pdf','embed') NOT NULL DEFAULT 'youtube',
				title         VARCHAR(255) NOT NULL DEFAULT '',
				url           VARCHAR(1000) NOT NULL DEFAULT '',
				embed_code    TEXT DEFAULT NULL,
				thumbnail_url VARCHAR(1000) DEFAULT NULL,
				description      TEXT DEFAULT NULL,
				link_url         VARCHAR(1000) DEFAULT NULL,
				highlight_label  VARCHAR(80) DEFAULT NULL,
				context       VARCHAR(200) NOT NULL DEFAULT '',
				tags          VARCHAR(500) DEFAULT NULL,
				sort_order    INT DEFAULT 0,
				status        ENUM('active','inactive') DEFAULT 'active',
				created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				KEY idx_type   (type),
				KEY idx_status (status)
			) ENGINE=InnoDB {$cs}"
		);
	}

	public function get_paginated( int $page = 1, string $search = '', string $status = '', string $type = '', string $context = '' ): array {
		$where    = array();
		$where_in = array();

		if ( $search ) {
			$s        = AH_DB_Helper::search_where( array( 'title', 'description', 'tags' ), $search );
			$where[]  = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
		}
		if ( $status )  { $where[] = 'status = %s'; $where_in[] = $status; }
		if ( $type )    { $where[] = 'type = %s';   $where_in[] = $type; }
		if ( $context ) { $where[] = 'FIND_IN_SET(%s, context) > 0'; $where_in[] = $context; }

		$args = array( 'order_by' => 'sort_order', 'order' => 'ASC' );
		if ( $where ) {
			$args['where']    = implode( ' AND ', $where );
			$args['where_in'] = $where_in;
		}

		return $this->paginate( $page, $args );
	}

	/** Return active resources for a given context (category, home, tools, or empty for all). */
	public function get_active( string $context = '', string $type = '', int $limit = 0 ): array {
		global $wpdb;
		$t = $this->table();

		$sql    = "SELECT * FROM `{$t}` WHERE status = 'active'";
		$params = array();

		if ( $context ) {
			$sql     .= ' AND FIND_IN_SET(%s, context) > 0';
			$params[] = $context;
		}
		if ( $type ) {
			$sql     .= ' AND type = %s';
			$params[] = $type;
		}

		$sql .= ' ORDER BY sort_order ASC, id ASC';

		if ( $limit > 0 ) {
			$sql     .= ' LIMIT %d';
			$params[] = $limit;
		}

		if ( $params ) {
			$sql = $wpdb->prepare( $sql, ...$params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return $wpdb->get_results( $sql ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/** Render a single resource row as HTML. */
	public static function render_resource( object $r, array $opts = array() ): string {
		$class = sanitize_html_class( 'ah-resource ah-resource--' . $r->type );
		if ( ! empty( $opts['class'] ) ) {
			$class .= ' ' . esc_attr( $opts['class'] );
		}

		ob_start();
		echo '<div class="' . esc_attr( $class ) . '" data-type="' . esc_attr( $r->type ) . '">';

		switch ( $r->type ) {

			case 'youtube':
			case 'shorts':
				$vid_id = self::youtube_id( (string) $r->url );
				if ( $vid_id ) {
					$ratio = ( 'shorts' === $r->type ) ? '56.25' : '56.25';
					echo '<div class="ah-resource__embed" style="position:relative;padding-bottom:' . esc_attr( $ratio ) . '%;height:0;overflow:hidden;">';
					echo '<iframe src="https://www.youtube.com/embed/' . esc_attr( $vid_id ) . '" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" allowfullscreen loading="lazy"></iframe>';
					echo '</div>';
				}
				break;

			case 'instagram':
			case 'facebook':
			case 'twitter':
			case 'tiktok':
				if ( ! empty( $r->embed_code ) ) {
					echo '<div class="ah-resource__embed">' . wp_kses_post( $r->embed_code ) . '</div>';
				} elseif ( ! empty( $r->url ) ) {
					$oembed = wp_oembed_get( esc_url_raw( $r->url ) );
					if ( $oembed ) {
						echo '<div class="ah-resource__embed">' . $oembed . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
				break;

			case 'image':
				if ( ! empty( $r->url ) ) {
					echo '<div class="ah-resource__image">';
					echo '<img src="' . esc_url( $r->url ) . '" alt="' . esc_attr( $r->title ) . '" loading="lazy" style="max-width:100%;height:auto;">';
					echo '</div>';
				}
				break;

			case 'audio':
				if ( ! empty( $r->url ) ) {
					echo '<div class="ah-resource__audio">';
					echo '<audio controls style="width:100%"><source src="' . esc_url( $r->url ) . '"><a href="' . esc_url( $r->url ) . '">' . esc_html( $r->title ) . '</a></audio>';
					echo '</div>';
				}
				break;

			case 'pdf':
				if ( ! empty( $r->url ) ) {
					echo '<div class="ah-resource__pdf">';
					echo '<iframe src="' . esc_url( $r->url ) . '" style="width:100%;height:500px;border:1px solid #e5e7eb;border-radius:8px;" loading="lazy"></iframe>';
					echo '<p style="margin-top:8px;font-size:0.85rem;"><a href="' . esc_url( $r->url ) . '" target="_blank" rel="noopener noreferrer">Open PDF ↗</a></p>';
					echo '</div>';
				}
				break;

			case 'embed':
			default:
				if ( ! empty( $r->embed_code ) ) {
					echo '<div class="ah-resource__embed">' . wp_kses_post( $r->embed_code ) . '</div>';
				}
				break;
		}

		if ( ! empty( $opts['show_title'] ) && $r->title ) {
			echo '<p class="ah-resource__title"><strong>' . esc_html( $r->title ) . '</strong></p>';
		}
		if ( ! empty( $opts['show_desc'] ) && $r->description ) {
			echo '<p class="ah-resource__desc">' . esc_html( $r->description ) . '</p>';
		}
		if ( ! empty( $r->link_url ) ) {
			echo '<p class="ah-resource__link"><a href="' . esc_url( $r->link_url ) . '" target="_blank" rel="noopener noreferrer">Learn more &rarr;</a></p>';
		}

		echo '</div>';
		return ob_get_clean();
	}

	/** Extract YouTube video ID from any YouTube URL format. */
	public static function youtube_id( string $url ): string {
		$patterns = array(
			'~[?&]v=([a-zA-Z0-9_-]{11})~',
			'~youtu\.be/([a-zA-Z0-9_-]{11})~',
			'~/shorts/([a-zA-Z0-9_-]{11})~',
			'~/embed/([a-zA-Z0-9_-]{11})~',
		);
		foreach ( $patterns as $p ) {
			if ( preg_match( $p, $url, $m ) ) {
				return $m[1];
			}
		}
		return '';
	}

	public static function type_labels(): array {
		return array(
			'youtube'   => 'YouTube Video',
			'shorts'    => 'YouTube Shorts',
			'instagram' => 'Instagram Post / Reel',
			'facebook'  => 'Facebook Video / Post',
			'twitter'   => 'Twitter / X Post',
			'tiktok'    => 'TikTok Video',
			'image'     => 'Image',
			'audio'     => 'Audio',
			'pdf'       => 'PDF Document',
			'embed'     => 'Custom Embed Code',
		);
	}

	public static function context_labels(): array {
		return array(
			'category' => 'Category Pages',
			'home'     => 'Home Page',
			'tools'    => 'Tools / Calculators',
			'global'   => 'Global (everywhere)',
		);
	}
}
