<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Related_Links_Model
 *
 * A universal, polymorphic relationship store that associates any source object
 * (a WP post, a service, a static page …) with related content:
 *   • Related Articles          (internal WP posts)
 *   • Related Calculators       (static-page HTML components)
 *   • Related Static Components  (static-page HTML components)
 *   • External Resources / Support Links (any URL)
 *   • Images, and any future type added via the `ah_related_link_types` filter
 *
 * Storage  : one row per relation in {prefix}ah_related_links (self-installing).
 * Source   : object_type + object_id   (e.g. 'wp_post' + 123).
 * Target   : target_kind ('url'|'wp_post'|'static_page') + target_id, or a raw url.
 * Grouping : `container` lets the front end render sections (e.g. "Calculators").
 *
 * Mirrors the AH_Content_Taxonomy_Model pattern (ensure_table + sync + render).
 * New content types need NO schema change - register a link type via the filter.
 */
class AH_Related_Links_Model {

	private string $table_suffix = 'related_links';
	private static bool $table_ready    = false;
	private static bool $assets_printed = false;

	public function table(): string {
		return AH_DB_Helper::table( $this->table_suffix );
	}

	// ── Table guard ──────────────────────────────────────────────────────────
	/**
	 * The table DDL lives ONLY in AH_DB_Schema (table 41c) - the single source of
	 * truth. This guard just self-heals: if the table is somehow missing (e.g. the
	 * version upgrade hasn't run yet), it triggers the idempotent installer once.
	 */
	public static function ensure_table(): void {
		if ( self::$table_ready ) {
			return;
		}
		global $wpdb;
		$table  = AH_DB_Helper::table( 'related_links' );
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( ! $exists && class_exists( 'AH_DB_Installer' ) ) {
			AH_DB_Installer::install();
		}
		self::$table_ready = true;
	}

	// ── Extensible link-type registry ────────────────────────────────────────
	/**
	 * Each type: label, icon, and the default target kind it resolves to.
	 * Add future content types by hooking `ah_related_link_types` - no schema change.
	 */
	public static function link_types(): array {
		$types = array(
			'article'          => array( 'label' => 'Related Article',   'icon' => '📄', 'target' => 'wp_post' ),
			'calculator'       => array( 'label' => 'Calculator Tool',   'icon' => '🧮', 'target' => 'static_page' ),
			'static_component' => array( 'label' => 'Static Component',  'icon' => '🧩', 'target' => 'static_page' ),
			'image'            => array( 'label' => 'Image',             'icon' => '🖼️', 'target' => 'url' ),
			'external'         => array( 'label' => 'External Resource', 'icon' => '🔗', 'target' => 'url' ),
			'support'          => array( 'label' => 'Support Link',      'icon' => '🛟', 'target' => 'url' ),
		);
		return apply_filters( 'ah_related_link_types', $types );
	}

	/** Suggested container/section names (datalist hints). Filterable. */
	public static function container_suggestions(): array {
		return apply_filters( 'ah_related_link_containers', array(
			'Related Articles', 'Calculators', 'Helpful Resources',
			'Support', 'Downloads', 'External Links',
		) );
	}

	private static function clean_object_type( string $t ): string {
		return substr( sanitize_key( $t ), 0, 50 );
	}

	private static function clean_target_kind( string $k ): string {
		return in_array( $k, array( 'url', 'wp_post', 'static_page' ), true ) ? $k : 'url';
	}

	// ── Read ─────────────────────────────────────────────────────────────────
	public function get_for( string $object_type, int $object_id, array $args = array() ): array {
		global $wpdb;
		self::ensure_table();

		$table       = $this->table();
		$status_sql  = ! empty( $args['only_active'] ) ? " AND status = 'active'" : '';
		$type_filter = '';
		$params      = array( self::clean_object_type( $object_type ), $object_id );

		if ( ! empty( $args['link_type'] ) ) {
			$type_filter = ' AND link_type = %s';
			$params[]    = sanitize_key( $args['link_type'] );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$table}`
			 WHERE object_type = %s AND object_id = %d {$status_sql}{$type_filter}
			 ORDER BY container ASC, sort_order ASC, id ASC",
			...$params
		) ) ?: array();
	}

	/** Reverse lookup: which objects relate TO a given target (e.g. "what uses this calculator?"). */
	public function get_referrers( string $target_kind, int $target_id, array $args = array() ): array {
		global $wpdb;
		self::ensure_table();
		$table  = $this->table();
		$status = ! empty( $args['only_active'] ) ? " AND status = 'active'" : '';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE target_kind = %s AND target_id = %d {$status} ORDER BY object_type, object_id",
			self::clean_target_kind( $target_kind ), $target_id
		) ) ?: array();
	}

	// ── Resolve a stored row to a usable URL + label ────────────────────────
	public function resolve_url( $row ): string {
		$row  = (array) $row;
		$kind = $row['target_kind'] ?? 'url';
		if ( in_array( $kind, array( 'wp_post', 'static_page' ), true ) && ! empty( $row['target_id'] ) ) {
			$permalink = get_permalink( (int) $row['target_id'] );
			if ( $permalink ) {
				return $permalink;
			}
		}
		return (string) ( $row['url'] ?? '' );
	}

	public function resolve_label( $row ): string {
		$row = (array) $row;
		if ( ! empty( $row['label'] ) ) {
			return $row['label'];
		}
		if ( ! empty( $row['target_id'] ) ) {
			$title = get_the_title( (int) $row['target_id'] );
			if ( $title ) {
				return $title;
			}
		}
		return $this->resolve_url( $row );
	}

	/**
	 * Front-end-ready data, grouped by container, with everything resolved.
	 * Returns: [ [ 'container' => 'Calculators', 'items' => [ [label,url,link_type,type_label,icon,target] ] ] ]
	 */
	public function get_grouped( string $object_type, int $object_id ): array {
		$rows   = $this->get_for( $object_type, $object_id, array( 'only_active' => true ) );
		$types  = self::link_types();
		$groups = array();

		foreach ( $rows as $row ) {
			$url = $this->resolve_url( $row );
			if ( ! $url ) {
				continue;
			}
			$container = $row->container ? $row->container : 'Related';
			if ( ! isset( $groups[ $container ] ) ) {
				$groups[ $container ] = array( 'container' => $container, 'items' => array() );
			}
			$lt = $row->link_type;
			$groups[ $container ]['items'][] = array(
				'label'      => $this->resolve_label( $row ),
				'url'        => $url,
				'link_type'  => $lt,
				'type_label' => $types[ $lt ]['label'] ?? ucfirst( str_replace( '_', ' ', $lt ) ),
				'icon'       => $types[ $lt ]['icon']  ?? '🔗',
				'target'     => ( $row->target_window === '_blank' ) ? '_blank' : '_self',
			);
		}

		return array_values( $groups );
	}

	// ── Write: replace all relations for an object ──────────────────────────
	/**
	 * @param array $rows Each row accepts either:
	 *   - 'target' => "static_page:12" | "wp_post:34" | "" (combined select value), or
	 *   - 'target_kind' + 'target_id' explicitly.
	 *   Plus: link_type, url, label, container, target_window, sort_order.
	 */
	public function sync( string $object_type, int $object_id, array $rows ): void {
		global $wpdb;
		self::ensure_table();

		$object_type = self::clean_object_type( $object_type );
		$table       = $this->table();
		$types       = self::link_types();

		$wpdb->delete( $table, array( 'object_type' => $object_type, 'object_id' => $object_id ), array( '%s', '%d' ) );

		$auto_order = 0;
		foreach ( $rows as $row ) {
			$row = (array) $row;

			$link_type = sanitize_key( $row['link_type'] ?? 'external' );
			if ( ! isset( $types[ $link_type ] ) ) {
				$link_type = 'external';
			}

			// Determine target kind/id - combined 'target' value wins when present.
			$target_kind = '';
			$target_id   = 0;
			if ( ! empty( $row['target'] ) && strpos( (string) $row['target'], ':' ) !== false ) {
				list( $tk, $tid ) = explode( ':', (string) $row['target'], 2 );
				$target_kind = self::clean_target_kind( sanitize_key( $tk ) );
				$target_id   = (int) $tid;
			} elseif ( ! empty( $row['target_kind'] ) ) {
				$target_kind = self::clean_target_kind( sanitize_key( $row['target_kind'] ) );
				$target_id   = (int) ( $row['target_id'] ?? 0 );
			}

			$url   = esc_url_raw( trim( (string) ( $row['url'] ?? '' ) ) );
			$label = sanitize_text_field( $row['label'] ?? '' );
			$container = sanitize_text_field( $row['container'] ?? '' );
			$window    = ( ( $row['target_window'] ?? '_self' ) === '_blank' ) ? '_blank' : '_self';
			$sort      = ( isset( $row['sort_order'] ) && $row['sort_order'] !== '' )
				? (int) $row['sort_order']
				: $auto_order;

			// Decide final kind: an internal target with an id, otherwise a url.
			if ( in_array( $target_kind, array( 'wp_post', 'static_page' ), true ) && $target_id > 0 ) {
				$final_kind = $target_kind;
			} else {
				$final_kind = 'url';
				$target_id  = 0;
			}

			// Skip rows that resolve to nothing.
			if ( $final_kind === 'url' && $url === '' ) {
				continue;
			}

			$wpdb->insert( $table, array(
				'object_type'   => $object_type,
				'object_id'     => $object_id,
				'link_type'     => $link_type,
				'target_kind'   => $final_kind,
				'target_id'     => $target_id ?: null,
				'url'           => $url,
				'label'         => $label,
				'container'     => $container,
				'target_window' => $window,
				'sort_order'    => $sort,
				'status'        => 'active',
			), array( '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s' ) );

			$auto_order++;
		}
	}

	public function delete_for( string $object_type, int $object_id ): void {
		global $wpdb;
		self::ensure_table();
		$wpdb->delete(
			$this->table(),
			array( 'object_type' => self::clean_object_type( $object_type ), 'object_id' => $object_id ),
			array( '%s', '%d' )
		);
	}

	// ── Target option sources ────────────────────────────────────────────────
	/** Static pages = HTML components managed at ah-static-pages. */
	public static function static_page_options(): array {
		$dir   = get_template_directory() . '/static/';
		$files = glob( $dir . '*.html' ) ?: array();
		$opts  = array();
		foreach ( $files as $f ) {
			$slug = basename( $f, '.html' );
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				continue; // only those backed by a real WP page can resolve a permalink
			}
			$opts[] = array(
				'id'    => (int) $page->ID,
				'title' => get_the_title( $page->ID ) ?: ucwords( str_replace( '-', ' ', $slug ) ),
			);
		}
		return $opts;
	}

	/** Recent published posts for the "Related Article" picker. */
	public static function post_options( int $limit = 50, int $exclude = 0 ): array {
		$q = get_posts( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'exclude'        => $exclude ? array( $exclude ) : array(),
			'fields'         => 'ids',
		) );
		$opts = array();
		foreach ( $q as $pid ) {
			$opts[] = array( 'id' => (int) $pid, 'title' => get_the_title( $pid ) ?: '(no title)' );
		}
		return $opts;
	}

	// ── Admin UI (Edit-Meta quick-edit panel) ────────────────────────────────
	/**
	 * Renders the manager for one object: existing rows + a hidden template + Add button.
	 * The interactive JS (add/remove/collect-on-save) lives in admin/pages/posts.php,
	 * mirroring the existing Highlight Links pattern.
	 */
	public function render_admin_panel( string $object_type, int $object_id ): void {
		self::ensure_table();
		$rows         = $this->get_for( $object_type, $object_id );
		$dl_id        = 'ah-rl-containers';
		$static_pages = self::static_page_options();
		$posts        = self::post_options( 50, $object_id );
		?>
		<div class="ah-rl-wrap" data-id="<?php echo esc_attr( $object_id ); ?>">
			<div class="ah-rl-rows">
				<?php foreach ( $rows as $row ) : ?>
					<?php $this->render_row( $row, $static_pages, $posts, $dl_id ); ?>
				<?php endforeach; ?>
			</div>

			<?php // Hidden template cloned by JS for new rows. ?>
			<div class="ah-rl-template" style="display:none;">
				<?php $this->render_row( null, $static_pages, $posts, $dl_id ); ?>
			</div>

			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-rl-add" style="margin-top:4px;font-size:.78rem;">+ Add Related Link</button>
		</div>
		<?php
		self::print_shared_assets( $dl_id );
	}

	/** One editable row. $row null = blank template row. */
	private function render_row( $row, array $static_pages, array $posts, string $dl_id ): void {
		$row = $row ? (array) $row : array();

		$cur_type   = $row['link_type']     ?? 'article';
		$cur_kind   = $row['target_kind']   ?? '';
		$cur_tid    = (int) ( $row['target_id'] ?? 0 );
		$cur_url    = $row['url']           ?? '';
		$cur_label  = $row['label']         ?? '';
		$cur_cont   = $row['container']     ?? '';
		$cur_window = $row['target_window'] ?? '_self';
		$cur_order  = isset( $row['sort_order'] ) ? (int) $row['sort_order'] : '';
		$cur_target = ( in_array( $cur_kind, array( 'wp_post', 'static_page' ), true ) && $cur_tid )
			? $cur_kind . ':' . $cur_tid
			: '';

		$inp = 'padding:4px 6px;border:1px solid #d1dae8;border-radius:4px;font-size:.78rem;outline:none;box-sizing:border-box;';
		?>
		<div class="ah-rl-row" style="border:1px solid #e2ecf9;border-radius:6px;padding:8px;margin-bottom:6px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;background:#fbfdff;">
			<select class="ah-rl-type" title="Link Type" style="<?php echo $inp; ?>flex:0 0 132px;">
				<?php foreach ( self::link_types() as $key => $meta ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $cur_type, $key ); ?>>
						<?php echo esc_html( $meta['icon'] . ' ' . $meta['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<select class="ah-rl-target" title="Pick an internal target (or leave to use the URL field)" style="<?php echo $inp; ?>flex:1 1 170px;">
				<option value="">- Use URL field -</option>
				<?php if ( $static_pages ) : ?>
					<optgroup label="Static Pages / Components">
						<?php foreach ( $static_pages as $sp ) :
							$val = 'static_page:' . $sp['id']; ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $cur_target, $val ); ?>><?php echo esc_html( $sp['title'] ); ?></option>
						<?php endforeach; ?>
					</optgroup>
				<?php endif; ?>
				<?php if ( $posts ) : ?>
					<optgroup label="Posts / Articles">
						<?php foreach ( $posts as $po ) :
							$val = 'wp_post:' . $po['id']; ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $cur_target, $val ); ?>><?php echo esc_html( $po['title'] ); ?></option>
						<?php endforeach; ?>
					</optgroup>
				<?php endif; ?>
			</select>

			<input type="text" class="ah-rl-url" value="<?php echo esc_attr( $cur_url ); ?>" placeholder="/slug/ or https://… (if no target above)" style="<?php echo $inp; ?>flex:1 1 170px;">
			<input type="text" class="ah-rl-label" value="<?php echo esc_attr( $cur_label ); ?>" placeholder="Label (optional)" style="<?php echo $inp; ?>flex:1 1 130px;">
			<input type="text" class="ah-rl-container" list="<?php echo esc_attr( $dl_id ); ?>" value="<?php echo esc_attr( $cur_cont ); ?>" placeholder="Section / Container" style="<?php echo $inp; ?>flex:0 0 130px;">

			<select class="ah-rl-window" title="Open link in" style="<?php echo $inp; ?>flex:0 0 92px;">
				<option value="_self"  <?php selected( $cur_window, '_self' ); ?>>Same tab</option>
				<option value="_blank" <?php selected( $cur_window, '_blank' ); ?>>New tab</option>
			</select>

			<input type="number" class="ah-rl-order" value="<?php echo esc_attr( $cur_order ); ?>" placeholder="#" title="Display order" style="<?php echo $inp; ?>flex:0 0 48px;width:48px;">
			<button type="button" class="ah-btn ah-btn-danger ah-btn-sm ah-rl-remove" style="flex:0 0 auto;padding:3px 8px;">✕</button>
		</div>
		<?php
	}

	/** Container datalist - printed once per page. */
	private static function print_shared_assets( string $dl_id ): void {
		if ( self::$assets_printed ) {
			return;
		}
		self::$assets_printed = true;
		?>
		<datalist id="<?php echo esc_attr( $dl_id ); ?>">
			<?php foreach ( self::container_suggestions() as $c ) : ?>
				<option value="<?php echo esc_attr( $c ); ?>"></option>
			<?php endforeach; ?>
		</datalist>
		<?php
	}
}
