<?php
defined( 'ABSPATH' ) || exit;

$plugin_ok = class_exists( 'AH_DB_Helper' );
$tab       = sanitize_key( $_GET['tab'] ?? 'types' );
$saved     = ! empty( $_GET['saved'] );

$types = [];
$terms = [];
$terms_by_type = [];

if ( $plugin_ok ) {
	global $wpdb;
	$tt = AH_DB_Helper::table( 'taxonomy_types' ); // wp_ah_taxonomy_types
	$tm = AH_DB_Helper::table( 'taxonomies' );      // wp_ah_taxonomies

	$types = $wpdb->get_results( "SELECT * FROM `{$tt}` ORDER BY id ASC" ) ?: []; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$terms = $wpdb->get_results( "SELECT tm.*, tt.name AS type_name, tt.slug AS type_slug FROM `{$tm}` tm INNER JOIN `{$tt}` tt ON tt.id = tm.type_id ORDER BY tt.id ASC, tm.sort_order ASC, tm.id ASC" ) ?: []; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	foreach ( $terms as $term ) {
		$terms_by_type[ (int) $term->type_id ][] = $term;
	}
}
?>
<div class="wrap ah-admin-wrap">

  <div class="ah-admin-header">
    <div class="ah-admin-logo">TX</div>
    <div>
      <h1><?php esc_html_e( 'Taxonomy Manager', 'ah-theme' ); ?></h1>
      <p><?php esc_html_e( 'Manage taxonomy types and their terms - stored in the CMS plugin tables.', 'ah-theme' ); ?></p>
    </div>
  </div>

  <?php if ( ! $plugin_ok ) : ?>
    <div class="ah-admin-notice ah-admin-notice--warn">
      <?php esc_html_e( 'The CMS plugin is not active. Activate it to manage taxonomy types and terms.', 'ah-theme' ); ?>
    </div>
  <?php else : ?>

  <?php if ( $saved ) : ?>
    <div class="ah-admin-notice ah-admin-notice--success"><?php esc_html_e( 'Saved successfully.', 'ah-theme' ); ?></div>
  <?php endif; ?>

  <!-- Tabs -->
  <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid #e2e8f0">
    <?php foreach ( [ 'types' => 'Types', 'terms' => 'Terms' ] as $t_key => $t_label ) : ?>
      <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ah-taxonomy', 'tab' => $t_key ], admin_url( 'admin.php' ) ) ); ?>"
         style="padding:10px 20px;font-weight:600;font-size:.875rem;text-decoration:none;border-radius:6px 6px 0 0;
                <?php echo $tab === $t_key ? 'background:#b7791f;color:white;' : 'color:#64748b;'; ?>">
        <?php echo esc_html( $t_label ); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ( $tab === 'types' ) : ?>
  <!-- ── Types tab ─────────────────────────────────────────────────────────── -->

    <div class="ah-admin-box">
      <h2><?php esc_html_e( 'Taxonomy Types', 'ah-theme' ); ?> <span style="font-weight:400;font-size:.85rem;color:#94a3b8"><?php esc_html_e( 'stored in wp_ah_taxonomy_types', 'ah-theme' ); ?></span></h2>

      <?php if ( empty( $types ) ) : ?>
        <p style="color:#94a3b8"><?php esc_html_e( 'No types yet. Use Install Mock Data to seed the defaults, or add one below.', 'ah-theme' ); ?></p>
      <?php else : ?>
        <table class="ah-admin-table" style="margin-bottom:24px">
          <thead>
            <tr>
              <th><?php esc_html_e( 'Name', 'ah-theme' ); ?></th>
              <th><?php esc_html_e( 'Slug', 'ah-theme' ); ?></th>
              <th><?php esc_html_e( 'Description', 'ah-theme' ); ?></th>
              <th><?php esc_html_e( 'Terms', 'ah-theme' ); ?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $types as $type ) :
              $type_terms_list = $terms_by_type[ (int) $type->id ] ?? [];
            ?>
            <tr>
              <td style="font-weight:600"><?php echo esc_html( $type->name ); ?></td>
              <td><code style="font-size:.8rem;background:#f1f5f9;padding:2px 6px;border-radius:4px"><?php echo esc_html( $type->slug ); ?></code></td>
              <td style="color:#64748b;font-size:.85rem"><?php echo esc_html( $type->description ?: '-' ); ?></td>
              <td>
                <?php if ( empty( $type_terms_list ) ) : ?>
                  <span style="color:#94a3b8;font-size:.8rem">none</span>
                <?php else : ?>
                  <div style="display:flex;flex-wrap:wrap;gap:4px">
                    <?php foreach ( $type_terms_list as $term ) : ?>
                      <span class="ah-badge ah-badge--ok"><?php echo esc_html( $term->name ); ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                      onsubmit="return confirm('Delete this type and all its terms?')">
                  <?php wp_nonce_field( 'ah_taxonomy' ); ?>
                  <input type="hidden" name="action"  value="ah_taxonomy">
                  <input type="hidden" name="_action" value="delete_type">
                  <input type="hidden" name="_tab"    value="types">
                  <input type="hidden" name="type_id" value="<?php echo esc_attr( $type->id ); ?>">
                  <button type="submit" class="button ah-btn-danger" style="padding:2px 10px;height:auto;font-size:.8rem">
                    <?php esc_html_e( 'Delete', 'ah-theme' ); ?>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <details style="border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px">
        <summary style="font-weight:600;cursor:pointer;color:#0f172a"><?php esc_html_e( '+ Add New Type', 'ah-theme' ); ?></summary>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
              style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <?php wp_nonce_field( 'ah_taxonomy' ); ?>
          <input type="hidden" name="action"  value="ah_taxonomy">
          <input type="hidden" name="_action" value="add_type">
          <input type="hidden" name="_tab"    value="types">
          <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px"><?php esc_html_e( 'Name *', 'ah-theme' ); ?></label>
            <input type="text" name="type_name" required class="regular-text" placeholder="e.g. Highlight Names">
          </div>
          <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px"><?php esc_html_e( 'Slug (auto if blank)', 'ah-theme' ); ?></label>
            <input type="text" name="type_slug" class="regular-text" placeholder="e.g. highlight-names">
          </div>
          <div style="grid-column:1/-1">
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px"><?php esc_html_e( 'Description', 'ah-theme' ); ?></label>
            <input type="text" name="type_description" class="large-text" placeholder="Optional description">
          </div>
          <div style="grid-column:1/-1">
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Add Type', 'ah-theme' ); ?></button>
          </div>
        </form>
      </details>
    </div>

  <?php else : ?>
  <!-- ── Terms tab ─────────────────────────────────────────────────────────── -->

    <div class="ah-admin-box">
      <h2><?php esc_html_e( 'Taxonomy Terms', 'ah-theme' ); ?> <span style="font-weight:400;font-size:.85rem;color:#94a3b8"><?php esc_html_e( 'stored in wp_ah_taxonomies', 'ah-theme' ); ?></span></h2>

      <?php if ( empty( $terms ) ) : ?>
        <p style="color:#94a3b8"><?php esc_html_e( 'No terms yet. Add a type first, then add terms to it.', 'ah-theme' ); ?></p>
      <?php else : ?>
        <table class="ah-admin-table" style="margin-bottom:24px">
          <thead>
            <tr>
              <th><?php esc_html_e( 'Term Name', 'ah-theme' ); ?></th>
              <th><?php esc_html_e( 'Slug', 'ah-theme' ); ?></th>
              <th><?php esc_html_e( 'Type', 'ah-theme' ); ?></th>
              <th><?php esc_html_e( 'Status', 'ah-theme' ); ?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $terms as $term ) : ?>
            <tr>
              <td style="font-weight:600"><?php echo esc_html( $term->name ); ?></td>
              <td><code style="font-size:.8rem;background:#f1f5f9;padding:2px 6px;border-radius:4px"><?php echo esc_html( $term->slug ); ?></code></td>
              <td><span class="ah-badge" style="background:#eff6ff;color:#1d4ed8"><?php echo esc_html( $term->type_name ); ?></span></td>
              <td>
                <span class="ah-badge <?php echo $term->status === 'active' ? 'ah-badge--ok' : 'ah-badge--warn'; ?>">
                  <?php echo esc_html( $term->status ); ?>
                </span>
              </td>
              <td>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                      onsubmit="return confirm('Delete this term?')">
                  <?php wp_nonce_field( 'ah_taxonomy' ); ?>
                  <input type="hidden" name="action"   value="ah_taxonomy">
                  <input type="hidden" name="_action"  value="delete_term">
                  <input type="hidden" name="_tab"     value="terms">
                  <input type="hidden" name="term_id"  value="<?php echo esc_attr( $term->id ); ?>">
                  <button type="submit" class="button ah-btn-danger" style="padding:2px 10px;height:auto;font-size:.8rem">
                    <?php esc_html_e( 'Delete', 'ah-theme' ); ?>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ( ! empty( $types ) ) : ?>
      <details style="border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px">
        <summary style="font-weight:600;cursor:pointer;color:#0f172a"><?php esc_html_e( '+ Add New Term', 'ah-theme' ); ?></summary>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
              style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <?php wp_nonce_field( 'ah_taxonomy' ); ?>
          <input type="hidden" name="action"  value="ah_taxonomy">
          <input type="hidden" name="_action" value="add_term">
          <input type="hidden" name="_tab"    value="terms">
          <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px"><?php esc_html_e( 'Type *', 'ah-theme' ); ?></label>
            <select name="term_type_id" required class="regular-text">
              <option value=""><?php esc_html_e( '- select type -', 'ah-theme' ); ?></option>
              <?php foreach ( $types as $type ) : ?>
                <option value="<?php echo esc_attr( $type->id ); ?>"><?php echo esc_html( $type->name ); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px"><?php esc_html_e( 'Term Name *', 'ah-theme' ); ?></label>
            <input type="text" name="term_name" required class="regular-text" placeholder="e.g. Related Articles">
          </div>
          <div>
            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px"><?php esc_html_e( 'Slug (auto if blank)', 'ah-theme' ); ?></label>
            <input type="text" name="term_slug" class="regular-text" placeholder="e.g. related-articles">
          </div>
          <div style="grid-column:1/-1">
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Add Term', 'ah-theme' ); ?></button>
          </div>
        </form>
      </details>
      <?php else : ?>
        <p style="color:#94a3b8;font-size:.875rem"><?php esc_html_e( 'Add at least one type before adding terms.', 'ah-theme' ); ?></p>
      <?php endif; ?>
    </div>

  <?php endif; ?>
  <?php endif; // plugin_ok ?>

</div>
