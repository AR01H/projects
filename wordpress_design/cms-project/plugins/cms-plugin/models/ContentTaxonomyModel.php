<?php
defined( 'ABSPATH' ) || exit;

class AH_Content_Taxonomy_Model {

	private string $table_suffix = 'content_taxonomies';
	private static bool $table_ready = false;

	public function table(): string {
		return AH_DB_Helper::table( $this->table_suffix );
	}

	public static function ensure_table(): void {
		if ( self::$table_ready ) {
			return;
		}

		global $wpdb;
		$table = AH_DB_Helper::table( 'content_taxonomies' );
		$cs    = $wpdb->get_charset_collate();

		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"CREATE TABLE IF NOT EXISTS `{$table}` (
				id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				object_type VARCHAR(50) NOT NULL,
				object_id   BIGINT UNSIGNED NOT NULL,
				taxonomy_id INT UNSIGNED NOT NULL,
				created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_object_taxonomy (object_type, object_id, taxonomy_id),
				KEY idx_object (object_type, object_id),
				KEY idx_taxonomy (taxonomy_id)
			) ENGINE=InnoDB {$cs}"
		);

		self::migrate_legacy_tables();
		self::$table_ready = true;
	}

	private static function migrate_legacy_tables(): void {
		global $wpdb;

		$table = AH_DB_Helper::table( 'content_taxonomies' );
		$legacy = array(
			array( 'table' => AH_DB_Helper::table( 'post_taxonomies' ),    'object_type' => 'ah_post', 'object_col' => 'post_id' ),
			array( 'table' => AH_DB_Helper::table( 'service_taxonomies' ), 'object_type' => 'service', 'object_col' => 'service_id' ),
		);

		foreach ( $legacy as $source ) {
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $source['table'] ) );
			if ( ! $exists ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO `{$table}` (object_type, object_id, taxonomy_id)
				 SELECT %s, `{$source['object_col']}`, taxonomy_id FROM `{$source['table']}`",
				$source['object_type']
			) );
		}
	}

	public function get_terms( string $object_type, int $object_id ): array {
		global $wpdb;
		self::ensure_table();

		$table = $this->table();
		$tax   = AH_DB_Helper::table( 'taxonomies' );
		$types = AH_DB_Helper::table( 'taxonomy_types' );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT t.*, tt.name AS type_name, tt.slug AS type_slug
			 FROM `{$tax}` t
			 INNER JOIN `{$table}` ct ON ct.taxonomy_id = t.id
			 LEFT JOIN `{$types}` tt ON tt.id = t.type_id
			 WHERE ct.object_type = %s AND ct.object_id = %d
			 ORDER BY tt.name ASC, t.name ASC",
			$this->sanitize_object_type( $object_type ),
			$object_id
		) ) ?: array();
	}

	public function get_term_ids( string $object_type, int $object_id ): array {
		global $wpdb;
		self::ensure_table();

		$table = $this->table();
		$ids   = $wpdb->get_col( $wpdb->prepare(
			"SELECT taxonomy_id FROM `{$table}` WHERE object_type = %s AND object_id = %d",
			$this->sanitize_object_type( $object_type ),
			$object_id
		) );

		return array_map( 'intval', $ids ?: array() );
	}

	public function sync_terms( string $object_type, int $object_id, array $taxonomy_ids ): void {
		global $wpdb;
		self::ensure_table();

		$object_type  = $this->sanitize_object_type( $object_type );
		$taxonomy_ids = array_values( array_unique( array_filter( array_map( 'absint', $taxonomy_ids ) ) ) );
		$table        = $this->table();

		$wpdb->delete( $table, array(
			'object_type' => $object_type,
			'object_id'   => $object_id,
		), array( '%s', '%d' ) );

		foreach ( $taxonomy_ids as $taxonomy_id ) {
			$wpdb->insert( $table, array(
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'taxonomy_id' => $taxonomy_id,
			), array( '%s', '%d', '%d' ) );
		}
	}

	public function get_active_terms_grouped(): array {
		global $wpdb;

		$tax   = AH_DB_Helper::table( 'taxonomies' );
		$types = AH_DB_Helper::table( 'taxonomy_types' );
		$type_rows = $wpdb->get_results( "SELECT * FROM `{$types}` ORDER BY name ASC" ) ?: array();
		$rows  = $wpdb->get_results(
			"SELECT t.*, tt.name AS type_name, tt.slug AS type_slug
			 FROM `{$tax}` t
			 LEFT JOIN `{$types}` tt ON tt.id = t.type_id
			 WHERE t.status = 'active'
			 ORDER BY tt.name ASC, t.name ASC"
		) ?: array();

		$groups = array();
		foreach ( $type_rows as $type ) {
			$groups[ $type->slug ] = array(
				'label' => $type->name,
				'items' => array(),
			);
		}

		foreach ( $rows as $row ) {
			$key = $row->type_slug ?: 'terms';
			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array(
					'label' => $row->type_name ?: 'Terms',
					'items' => array(),
				);
			}
			$groups[ $key ]['items'][] = $row;
		}

		return $groups;
	}

	public function render_picker( string $object_type, int $object_id = 0, array $selected_ids = array() ): void {
		$selected_ids = $object_id ? $this->get_term_ids( $object_type, $object_id ) : array_map( 'absint', $selected_ids );
		$groups       = $this->get_active_terms_grouped();
		?>
		<div class="ah-taxonomy-picker">
			<?php if ( empty( $groups ) ) : ?>
				<p style="font-size:12px;color:var(--ah-muted);margin:0;">No active terms yet. Add taxonomy types and terms first.</p>
			<?php else : ?>
				<?php foreach ( $groups as $group ) : ?>
					<div class="ah-taxonomy-picker-group">
						<div class="ah-taxonomy-picker-heading">
							<strong><?php echo esc_html( $group['label'] ); ?></strong>
						</div>
						<div class="ah-taxonomy-picker-options">
							<?php if ( empty( $group['items'] ) ) : ?>
								<p class="ah-taxonomy-picker-empty">No terms in this type yet.</p>
							<?php endif; ?>
							<?php foreach ( $group['items'] as $term ) : ?>
								<label class="ah-taxonomy-chip">
									<input type="checkbox" name="taxonomy_ids[]" value="<?php echo esc_attr( $term->id ); ?>" <?php checked( in_array( (int) $term->id, $selected_ids, true ) ); ?>>
									<span><?php echo esc_html( $term->name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function get_terms_used_by( string $object_type ): array {
		global $wpdb;
		self::ensure_table();
		$table = $this->table();
		$tax   = AH_DB_Helper::table( 'taxonomies' );
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT t.id, t.name
			 FROM `{$tax}` t
			 INNER JOIN `{$table}` ct ON ct.taxonomy_id = t.id AND ct.object_type = %s
			 WHERE t.status = 'active'
			 ORDER BY t.name ASC",
			$this->sanitize_object_type( $object_type )
		) ) ?: array();
	}

	public function render_badges( string $object_type, int $object_id ): void {
		$terms = $this->get_terms( $object_type, $object_id );
		if ( empty( $terms ) ) {
			echo '<span style="color:var(--ah-muted);font-size:12px;">-</span>';
			return;
		}

		echo '<div class="ah-taxonomy-badges">';
		foreach ( $terms as $term ) {
			printf( '<span>%s</span>', esc_html( $term->name ) );
		}
		echo '</div>';
	}

	private function sanitize_object_type( string $object_type ): string {
		return substr( sanitize_key( $object_type ), 0, 50 );
	}
}
