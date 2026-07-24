<?php
namespace Ah\Cms\Feature\Navigation\Controller;

defined( 'ABSPATH' ) || exit;

class NavigationAdminController {

	public static function handle_save(): void {
		check_admin_referer( 'ah_cms_navigation' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		// Load existing data first — only overwrite fields from the submitted tab.
		$existing_nav = self::normalize_navigation( json_decode( get_option( 'ah_cms_navigation', '[]' ), true ) ?? array() );
		$existing_cta = json_decode( get_option( 'ah_cms_nav_cta', '{}' ), true ) ?? array();
		$existing_footer = json_decode( get_option( 'ah_cms_footer', '{}' ), true ) ?? array();

		// ── Main Navigation (only submitted on main-nav tab) ──
		if ( isset( $_POST['nav_items'] ) ) {
			$nav_items = array();
			foreach ( (array) $_POST['nav_items'] as $item ) {
				$label = sanitize_text_field( $item['label'] ?? '' );
				if ( $label === '' ) {
					continue;
				}
				$type    = ( $item['type'] ?? 'link' ) === 'dropdown' ? 'dropdown' : 'link';
				$submenu = array();
				foreach ( (array) ( $item['submenu'] ?? array() ) as $sub_item ) {
					$sub_label = sanitize_text_field( $sub_item['label'] ?? '' );
					$sub_url   = self::clean_nav_url( (string) ( $sub_item['url'] ?? '' ) );
					if ( $sub_label === '' || $sub_url === '' ) {
						continue;
					}
					$submenu[] = array(
						'label'       => $sub_label,
						'url'         => $sub_url,
						'description' => sanitize_text_field( $sub_item['description'] ?? '' ),
						'icon'        => sanitize_text_field( $sub_item['icon'] ?? '' ),
						'highlight'   => ! empty( $sub_item['highlight'] ),
					);
				}
				$nav_items[] = array(
					'id'          => sanitize_title( $item['id'] ?? $label ),
					'label'       => $label,
					'type'        => $type,
					'url'         => $type === 'link' ? self::clean_nav_url( (string) ( $item['url'] ?? '' ) ) : '',
					'visible'     => ! empty( $item['visible'] ),
					'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
					'description' => sanitize_text_field( $item['description'] ?? '' ),
					'css_class'   => sanitize_text_field( $item['css_class'] ?? '' ),
					'panel_image' => esc_url_raw( $item['panel_image'] ?? '' ),
					'submenu'     => $submenu,
				);
			}
			$existing_nav = $nav_items;
		}

		if ( isset( $_POST['nav_cta'] ) ) {
			$existing_cta = array(
				'label' => sanitize_text_field( $_POST['nav_cta']['label'] ?? 'Get Guidance' ),
				'url'   => self::clean_nav_url( (string) ( $_POST['nav_cta']['url'] ?? '/ask-expert/' ) ),
			);
		}

		// ── Footer Settings + Columns + Legal Links (only submitted on their tabs) ──
		if ( isset( $_POST['footer_brand_description'] ) || isset( $_POST['footer_badge_text'] ) || isset( $_POST['footer_cta'] ) ) {
			$existing_footer['brand_description'] = wp_kses_post( $_POST['footer_brand_description'] ?? $existing_footer['brand_description'] ?? '' );
			$existing_footer['badge_text']        = sanitize_text_field( $_POST['footer_badge_text'] ?? $existing_footer['badge_text'] ?? '' );
			$existing_footer['cta']               = array(
				'label' => sanitize_text_field( $_POST['footer_cta']['label'] ?? $existing_footer['cta']['label'] ?? '' ),
				'url'   => self::clean_nav_url( (string) ( $_POST['footer_cta']['url'] ?? $existing_footer['cta']['url'] ?? '' ) ),
			);
		}

		if ( isset( $_POST['footer_columns'] ) ) {
			$footer_columns = array();
			foreach ( (array) $_POST['footer_columns'] as $column ) {
				$title = sanitize_text_field( $column['title'] ?? '' );
				$items = array();
				foreach ( (array) ( $column['items'] ?? array() ) as $item ) {
					$label = sanitize_text_field( $item['label'] ?? '' );
					$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
					if ( $label === '' || $url === '' ) {
						continue;
					}
					$items[] = array(
						'label'     => $label,
						'url'       => $url,
						'highlight' => ! empty( $item['highlight'] ),
					);
				}
				if ( $title !== '' || ! empty( $items ) ) {
					$footer_columns[] = array(
						'title' => $title ?: 'Links',
						'items' => $items,
					);
				}
			}
			$existing_footer['columns'] = $footer_columns;
		}

		if ( isset( $_POST['footer_legal_links'] ) ) {
			$legal_links = array();
			foreach ( (array) $_POST['footer_legal_links'] as $item ) {
				$label = sanitize_text_field( $item['label'] ?? '' );
				$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
				if ( $label === '' || $url === '' ) {
					continue;
				}
				$legal_links[] = array(
					'label' => $label,
					'url'   => $url,
				);
			}
			$existing_footer['legal_links'] = $legal_links;
		}

		// Save only the options that were updated
		if ( isset( $_POST['nav_items'] ) ) {
			\update_option( 'ah_cms_navigation', \wp_json_encode( $existing_nav ) );
		}
		if ( isset( $_POST['nav_cta'] ) ) {
			\update_option( 'ah_cms_nav_cta', \wp_json_encode( $existing_cta ) );
		}
		if ( isset( $_POST['footer_brand_description'] ) || isset( $_POST['footer_badge_text'] ) || isset( $_POST['footer_cta'] ) || isset( $_POST['footer_columns'] ) || isset( $_POST['footer_legal_links'] ) ) {
			\update_option( 'ah_cms_footer', \wp_json_encode( $existing_footer ) );
		}

		$redirect_args = array( 'page' => 'ah-navigation', 'saved' => '1' );
		if ( ! empty( $_POST['active_tab'] ) ) {
			$redirect_args['tab'] = \sanitize_key( $_POST['active_tab'] );
		}
		\AH_Admin_Bootstrap::redirect( \add_query_arg( $redirect_args, \admin_url( 'admin.php' ) ) );
	}

	public static function get_navigation_data(): array {
		$opt = self::decode_option( get_option( 'ah_cms_navigation', array() ) );
		if ( empty( $opt ) ) {
			$opt = self::decode_option( get_option( 'ah_theme_navigation', array() ) );
		}

		return self::normalize_navigation( is_array( $opt ) ? $opt : array() );
	}

	public static function get_nav_cta_data(): array {
		$defaults = array(
			'label' => 'Get Help',
			'url'   => '/contact/',
		);
		$opt = self::decode_option( get_option( 'ah_cms_nav_cta', array() ) );
		if ( empty( $opt ) ) {
			$opt = self::decode_option( get_option( 'ah_nav_cta', array() ) );
		}

		return array_merge( $defaults, is_array( $opt ) ? $opt : array() );
	}

	public static function get_footer_data(): array {
		$opt = self::decode_option( get_option( 'ah_cms_footer', array() ) );
		if ( empty( $opt ) ) {
			$opt = self::decode_option( get_option( 'ah_theme_footer', array() ) );
		}

		return self::normalize_footer( is_array( $opt ) ? $opt : array() );
	}

	public static function get_nav_link_suggestions(): array {
		$suggestions = array();

		$push = static function ( string $label, string $url, string $type ) use ( &$suggestions ): void {
			$key = strtolower( $label . '|' . $url );
			if ( isset( $suggestions[ $key ] ) ) {
				return;
			}

			$suggestions[ $key ] = array(
				'label' => $label,
				'url'   => $url,
				'type'  => $type,
			);
		};

		$push( 'Home', home_url( '/' ), 'page' );
		$push( 'Blog', home_url( '/blog/' ), 'page' );
		$push( 'Services', home_url( '/services/' ), 'page' );
		$push( 'Contact', home_url( '/contact/' ), 'page' );

		foreach ( get_pages( array( 'post_status' => array( 'publish', 'draft', 'private' ), 'sort_column' => 'post_title' ) ) as $page ) {
			$push(
				$page->post_title ?: ucwords( str_replace( '-', ' ', $page->post_name ) ),
				get_permalink( $page->ID ) ?: home_url( '/' . $page->post_name . '/' ),
				'wp-page'
			);
		}

		foreach ( get_posts( array(
			'post_type'      => 'post',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) ) as $post ) {
			$push(
				get_the_title( $post ) ?: 'Post #' . $post->ID,
				get_permalink( $post ) ?: home_url( '/?p=' . $post->ID ),
				'post'
			);
		}

		$static_dir = trailingslashit( get_template_directory() ) . 'static/';
		foreach ( glob( $static_dir . '*.html' ) ?: array() as $file ) {
			$slug  = basename( $file, '.html' );
			$label = ucwords( str_replace( '-', ' ', $slug ) );
			$page  = get_page_by_path( $slug );
			$push(
				$label,
				$page ? get_permalink( $page->ID ) : home_url( '/' . $slug . '/' ),
				'static-page'
			);
		}

		return array_values( $suggestions );
	}

	public static function decode_option( $value ) {
		if ( is_string( $value ) ) {
			return json_decode( $value, true ) ?: array();
		}

		return $value;
	}

	public static function normalize_navigation( array $items ): array {
		$normalized = array();
		foreach ( $items as $index => $item ) {
			$item  = (array) $item;
			$label = sanitize_text_field( $item['label'] ?? '' );
			if ( $label === '' ) {
				continue;
			}

			$type    = ( $item['type'] ?? 'link' ) === 'dropdown' ? 'dropdown' : 'link';
			$submenu = array();
			foreach ( (array) ( $item['submenu'] ?? array() ) as $sub_item ) {
				$sub_item  = (array) $sub_item;
				$sub_label = sanitize_text_field( $sub_item['label'] ?? '' );
				$sub_url   = self::clean_nav_url( (string) ( $sub_item['url'] ?? '' ) );
				if ( $sub_label === '' || $sub_url === '' ) {
					continue;
				}

				$submenu[] = array(
					'label'       => $sub_label,
					'url'         => $sub_url,
					'description' => sanitize_text_field( $sub_item['description'] ?? '' ),
					'icon'        => sanitize_text_field( $sub_item['icon'] ?? '' ),
					'css_class'   => sanitize_text_field( $sub_item['css_class'] ?? '' ),
					'highlight'   => ! empty( $sub_item['highlight'] ),
				);
			}

			$normalized[] = array(
				'id'          => sanitize_title( $item['id'] ?? $label ?: 'nav-' . $index ),
				'label'       => $label,
				'type'        => $type,
				'url'         => $type === 'link' ? self::clean_nav_url( (string) ( $item['url'] ?? '' ), home_url( '/' ) ) : '',
				'visible'     => isset( $item['visible'] ) ? (bool) $item['visible'] : true,
				'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
				'description' => sanitize_text_field( $item['description'] ?? '' ),
				'css_class'   => sanitize_text_field( $item['css_class'] ?? '' ),
				'panel_image' => esc_url_raw( $item['panel_image'] ?? '' ),
				'submenu'     => $submenu,
			);
		}

		return $normalized;
	}

	public static function normalize_footer( array $footer ): array {
		$columns = array();
		foreach ( (array) ( $footer['columns'] ?? array() ) as $column ) {
			$column = (array) $column;
			$title  = sanitize_text_field( $column['title'] ?? '' );
			$items  = array();
			foreach ( (array) ( $column['items'] ?? array() ) as $item ) {
				$item  = (array) $item;
				$label = sanitize_text_field( $item['label'] ?? '' );
				$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
				if ( $label === '' || $url === '' ) {
					continue;
				}

				$items[] = array(
					'label'     => $label,
					'url'       => $url,
					'highlight' => ! empty( $item['highlight'] ),
				);
			}

			if ( $title !== '' || ! empty( $items ) ) {
				$columns[] = array(
					'title' => $title ?: 'Links',
					'items' => $items,
				);
			}
		}

		$legal_links = array();
		foreach ( (array) ( $footer['legal_links'] ?? array() ) as $item ) {
			$item  = (array) $item;
			$label = sanitize_text_field( $item['label'] ?? '' );
			$url   = self::clean_nav_url( (string) ( $item['url'] ?? '' ) );
			if ( $label === '' || $url === '' ) {
				continue;
			}

			$legal_links[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}

		return array(
			'brand_description' => wp_kses_post( $footer['brand_description'] ?? '' ),
			'badge_text'        => sanitize_text_field( $footer['badge_text'] ?? '' ),
			'columns'           => $columns,
			'cta'               => array(
				'label' => sanitize_text_field( $footer['cta']['label'] ?? '' ),
				'url'   => self::clean_nav_url( (string) ( $footer['cta']['url'] ?? '' ), '/contact/' ),
			),
			'legal_links'       => $legal_links,
		);
	}

	public static function clean_nav_url( string $url, string $fallback = '' ): string {
		$url = trim( wp_unslash( $url ) );
		if ( $url === '' ) {
			return $fallback;
		}
		if ( preg_match( '#^(https?:)?//#i', $url ) || strpos( $url, '#' ) === 0 || preg_match( '#^[a-z][a-z0-9+.-]*:#i', $url ) ) {
			return esc_url_raw( $url );
		}

		return '/' . trim( sanitize_text_field( $url ), '/' ) . '/';
	}
}
