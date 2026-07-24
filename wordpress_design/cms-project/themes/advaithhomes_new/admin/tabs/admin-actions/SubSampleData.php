<?php
/**
 * admin/tabs/admin-actions/sub-sample-data.php
 * Install or remove mock/demo CSV data in the CMS plugin.
 */

defined( 'ABSPATH' ) || exit;

$cms_ready    = function_exists( 'adn_cms_available' ) && adn_cms_available();
$mockdata_dir = ADN_THEME_DIR . '/data/mockdata';
$datasets     = array();
if ( is_dir( $mockdata_dir ) ) {
    foreach ( scandir( $mockdata_dir ) as $entry ) {
        if ( '.' === $entry || '..' === $entry ) { continue; }
        if ( is_dir( $mockdata_dir . '/' . $entry ) ) {
            $datasets[] = $entry;
        }
    }
}
sort( $datasets );

$data_types = array(
    'taxonomy' => array(
        'label'     => __( 'Taxonomy', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Guide parents & topics (journey cards)', ADN_TEXT_DOMAIN ),
        'file'      => 'taxonomy.csv',
        'icon'      => 'fa-sitemap',
        'color'     => '#4f46e5',
        'needs_cms' => true,
    ),
    'guides' => array(
        'label'     => __( 'Guides', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Articles linked to topics', ADN_TEXT_DOMAIN ),
        'file'      => 'guides.csv',
        'icon'      => 'fa-book-open',
        'color'     => '#059669',
        'needs_cms' => true,
    ),
    'news' => array(
        'label'     => __( 'News', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'News posts linked to News term', ADN_TEXT_DOMAIN ),
        'file'      => 'news.csv',
        'icon'      => 'fa-newspaper',
        'color'     => '#0891b2',
        'needs_cms' => true,
    ),
    'faqs' => array(
        'label'     => __( 'FAQs', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Frequently asked questions', ADN_TEXT_DOMAIN ),
        'file'      => 'faqs.csv',
        'icon'      => 'fa-circle-question',
        'color'     => '#7c3aed',
        'needs_cms' => false,
    ),
    'reviews' => array(
        'label'     => __( 'Reviews', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Testimonials &amp; star ratings', ADN_TEXT_DOMAIN ),
        'file'      => 'reviews.csv',
        'icon'      => 'fa-star',
        'color'     => '#b45309',
        'needs_cms' => false,
    ),
    'members' => array(
        'label'     => __( 'Members', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Experts &amp; team profiles', ADN_TEXT_DOMAIN ),
        'file'      => 'members.csv',
        'icon'      => 'fa-user-tie',
        'color'     => '#0369a1',
        'needs_cms' => false,
    ),
    'terms' => array(
        'label'     => __( 'Glossary Terms', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Terminology &amp; definitions', ADN_TEXT_DOMAIN ),
        'file'      => 'terms.csv',
        'icon'      => 'fa-spell-check',
        'color'     => '#be185d',
        'needs_cms' => true,
    ),
    'banners' => array(
        'label'     => __( 'Banners', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Expert hero banner &amp; section banners', ADN_TEXT_DOMAIN ),
        'file'      => 'banners.csv',
        'icon'      => 'fa-images',
        'color'     => '#0f766e',
        'needs_cms' => false,
    ),
    'notices' => array(
        'label'     => __( 'Site Notice', ADN_TEXT_DOMAIN ),
        'desc'      => __( 'Active site-wide popup notice', ADN_TEXT_DOMAIN ),
        'file'      => 'notices.csv',
        'icon'      => 'fa-bell',
        'color'     => '#dc2626',
        'needs_cms' => false,
    ),
);

// Check what's already installed (by reading the install log).
$installed_log = get_option( 'adn_mock_install_log', array() );
$has_installed = ! empty( $installed_log );

$fa_i = function( $cls, $extra_style = '' ) {
    $s = $extra_style ? ' style="' . esc_attr( $extra_style ) . '"' : '';
    echo '<i class="fa-solid ' . esc_attr( $cls ) . '"' . $s . '></i>';
};
?>
<style>
.adn-sd-type-icon { width:28px;height:28px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:12px;flex-shrink:0;margin-right:6px; }
.adn-sd-type-cell { display:flex;align-items:center;padding:10px 8px; }
.adn-sd-type-label { font-weight:600;font-size:13px;line-height:1.2; }
.adn-sd-type-desc { color:#6b7280;font-size:11px;line-height:1.3;margin-top:2px; }
.adn-sd-remove-zone { background:#fff8f8;border:1px solid #fca5a5;border-radius:6px;padding:16px 20px; }
.adn-sd-remove-zone h3 { margin:0 0 6px;color:#b91c1c; }
</style>

<div class="card" style="max-width:960px;">
    <h2>
        <?php $fa_i( 'fa-database', 'margin-right:8px;color:#4f46e5;' ); ?>
        <?php esc_html_e( 'Install Sample Data', ADN_TEXT_DOMAIN ); ?>
    </h2>
    <p class="description" style="font-size:13px;margin-bottom:16px;">
        <?php esc_html_e( 'Select a dataset and the data types to install. All installed items are tagged so they can be removed cleanly when going live. Existing slugs are never duplicated.', ADN_TEXT_DOMAIN ); ?>
    </p>

    <?php if ( ! $cms_ready ) : ?>
        <div class="notice notice-warning inline" style="margin:0 0 16px;">
            <p>
                <?php $fa_i( 'fa-triangle-exclamation', 'margin-right:5px;' ); ?>
                <?php esc_html_e( 'CMS plugin not active. Taxonomy, Guides, News and Glossary types require the CMS plugin. FAQs, Reviews, Members and Notices can still be installed.', ADN_TEXT_DOMAIN ); ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if ( empty( $datasets ) ) : ?>
        <div class="notice notice-error inline" style="margin:0 0 16px;">
            <p><?php esc_html_e( 'No mock dataset folders found in data/mockdata/. Check the theme installation.', ADN_TEXT_DOMAIN ); ?></p>
        </div>
    <?php else : ?>

    <?php /* master toggle */ ?>
    <p style="margin-bottom:8px;">
        <button type="button" class="button adn-sd-toggle-all" data-state="1"
                style="display:inline-flex;align-items:center;gap:5px;font-size:12px;">
            <i class="fa-solid fa-check-double"></i>
            <span><?php esc_html_e( 'Select All', ADN_TEXT_DOMAIN ); ?></span>
        </button>
    </p>

    <form id="adn-seed-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="adn_seed_content">
        <?php wp_nonce_field( 'adn_seed_content' ); ?>

        <table class="widefat adn-seed-table" style="margin-bottom:20px;border-collapse:collapse;border:1px solid #c3c4c7;">
            <thead style="background:#f6f7f7;">
                <tr>
                    <th style="padding:10px 12px;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#646970;min-width:140px;">
                        <?php $fa_i( 'fa-database', 'margin-right:4px;' ); ?>
                        <?php esc_html_e( 'Dataset', ADN_TEXT_DOMAIN ); ?>
                    </th>
                    <?php foreach ( $data_types as $type_key => $type_def ) :
                        $csv_exists_any = false;
                        foreach ( $datasets as $ds ) {
                            if ( file_exists( $mockdata_dir . '/' . $ds . '/' . $type_def['file'] ) ) {
                                $csv_exists_any = true; break;
                            }
                        }
                    ?>
                    <th style="text-align:center;padding:10px 6px;min-width:82px;font-weight:500;" data-col="<?php echo esc_attr( $type_key ); ?>">
                        <div class="adn-sd-type-icon" style="background:<?php echo esc_attr( $type_def['color'] ); ?>;margin:0 auto 4px;">
                            <i class="fa-solid <?php echo esc_attr( $type_def['icon'] ); ?>"></i>
                        </div>
                        <div style="font-size:11px;font-weight:600;"><?php echo esc_html( $type_def['label'] ); ?></div>
                        <div style="font-size:10px;color:<?php echo $csv_exists_any ? '#059669' : '#d97706'; ?>;margin-top:2px;">
                            <?php echo $csv_exists_any ? esc_html__( '✓ CSV', ADN_TEXT_DOMAIN ) : esc_html__( '✗ no CSV', ADN_TEXT_DOMAIN ); ?>
                        </div>
                        <button type="button" class="adn-sd-col-toggle button button-small"
                                data-col="<?php echo esc_attr( $type_key ); ?>"
                                style="margin-top:4px;font-size:10px;padding:1px 5px;height:auto;line-height:1.4;"
                                title="<?php echo esc_attr( sprintf( __( 'Toggle all %s', ADN_TEXT_DOMAIN ), $type_def['label'] ) ); ?>">
                            <?php esc_html_e( '↕ Col', ADN_TEXT_DOMAIN ); ?>
                        </button>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $datasets as $ds ) :
                    $ds_label = ucwords( str_replace( '-', ' ', $ds ) );
                ?>
                <tr style="border-top:1px solid #e5e7eb;" data-row="<?php echo esc_attr( $ds ); ?>">
                    <td style="padding:10px 12px;">
                        <strong style="font-size:13px;"><?php echo esc_html( $ds_label ); ?></strong>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px;font-family:monospace;"><?php echo esc_html( $ds ); ?></div>
                        <button type="button" class="adn-sd-row-toggle button button-small"
                                data-row="<?php echo esc_attr( $ds ); ?>"
                                style="margin-top:5px;font-size:10px;padding:1px 7px;height:auto;line-height:1.4;">
                            <i class="fa-solid fa-arrows-left-right" style="font-size:9px;"></i>
                            <?php esc_html_e( 'Row', ADN_TEXT_DOMAIN ); ?>
                        </button>
                    </td>
                    <?php foreach ( $data_types as $type_key => $type_def ) :
                        $csv_path  = $mockdata_dir . '/' . $ds . '/' . $type_def['file'];
                        $has_csv   = file_exists( $csv_path );
                        $needs_cms = $type_def['needs_cms'];
                        $disabled  = ( ! $has_csv ) || ( $needs_cms && ! $cms_ready );
                    ?>
                    <td style="text-align:center;padding:10px 6px;"
                        data-row="<?php echo esc_attr( $ds ); ?>"
                        data-col="<?php echo esc_attr( $type_key ); ?>">
                        <label title="<?php echo $disabled ? esc_attr( $has_csv ? __( 'CMS plugin required', ADN_TEXT_DOMAIN ) : __( 'CSV not found', ADN_TEXT_DOMAIN ) ) : esc_attr( $type_def['desc'] ); ?>">
                            <input type="checkbox"
                                   class="adn-seed-cb"
                                   data-row="<?php echo esc_attr( $ds ); ?>"
                                   data-col="<?php echo esc_attr( $type_key ); ?>"
                                   name="seed[<?php echo esc_attr( $ds ); ?>][<?php echo esc_attr( $type_key ); ?>]"
                                   value="1"
                                   <?php checked( $has_csv && ! $disabled ); ?>
                                   <?php disabled( $disabled ); ?>>
                        </label>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <script>
        (function(){
            function enabledCbs(selector) {
                return Array.from(document.querySelectorAll(selector)).filter(function(cb){ return !cb.disabled; });
            }

            /* master toggle */
            var masterBtn = document.querySelector('.adn-sd-toggle-all');
            if (masterBtn) {
                masterBtn.addEventListener('click', function(){
                    var selecting = masterBtn.dataset.state === '1';
                    enabledCbs('.adn-seed-cb').forEach(function(cb){ cb.checked = selecting; });
                    masterBtn.dataset.state = selecting ? '0' : '1';
                    masterBtn.querySelector('span').textContent = selecting
                        ? '<?php echo esc_js( __( 'Deselect All', ADN_TEXT_DOMAIN ) ); ?>'
                        : '<?php echo esc_js( __( 'Select All', ADN_TEXT_DOMAIN ) ); ?>';
                    masterBtn.querySelector('i').className = selecting ? 'fa-solid fa-xmark' : 'fa-solid fa-check-double';
                });
            }

            /* row toggles */
            document.querySelectorAll('.adn-sd-row-toggle').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var row = btn.dataset.row;
                    var cbs = enabledCbs('.adn-seed-cb[data-row="' + row + '"]');
                    var allChecked = cbs.every(function(cb){ return cb.checked; });
                    cbs.forEach(function(cb){ cb.checked = !allChecked; });
                });
            });

            /* column toggles */
            document.querySelectorAll('.adn-sd-col-toggle').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var col = btn.dataset.col;
                    var cbs = enabledCbs('.adn-seed-cb[data-col="' + col + '"]');
                    var allChecked = cbs.every(function(cb){ return cb.checked; });
                    cbs.forEach(function(cb){ cb.checked = !allChecked; });
                });
            });
        })();
        </script>

        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <?php foreach ( $data_types as $type_def ) : ?>
            <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:#6b7280;">
                <span class="adn-sd-type-icon" style="background:<?php echo esc_attr( $type_def['color'] ); ?>;width:18px;height:18px;font-size:9px;border-radius:4px;">
                    <i class="fa-solid <?php echo esc_attr( $type_def['icon'] ); ?>"></i>
                </span>
                <span><strong style="color:#374151;"><?php echo esc_html( $type_def['label'] ); ?></strong> - <?php echo wp_kses_data( $type_def['desc'] ); ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <p>
            <button type="submit" class="button button-primary" style="display:inline-flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-download"></i>
                <?php esc_html_e( 'Install Selected Data', ADN_TEXT_DOMAIN ); ?>
            </button>
            <span class="description" style="margin-left:12px;font-size:12px;">
                <?php esc_html_e( 'Checked items only. Existing slugs are skipped. All installed data is tagged for easy removal.', ADN_TEXT_DOMAIN ); ?>
            </span>
        </p>
    </form>

    <?php endif; ?>
</div>

<?php /* ── Remove Mock Data ─────────────────────────────────────────────── */ ?>
<div class="card adn-sd-remove-zone" style="max-width:960px;margin-top:20px;">
    <h3>
        <i class="fa-solid fa-trash-can" style="margin-right:6px;"></i>
        <?php esc_html_e( 'Remove All Mock Data', ADN_TEXT_DOMAIN ); ?>
    </h3>
    <p class="description" style="font-size:13px;margin-bottom:12px;">
        <?php esc_html_e( 'Removes all posts, FAQs, reviews, experts and glossary terms that were installed by this tool. This is safe to run before going live - it only removes tagged demo content, never your real content.', ADN_TEXT_DOMAIN ); ?>
    </p>

    <?php if ( $has_installed ) : ?>
        <?php
        $log_summary = array();
        foreach ( $installed_log as $table => $ids ) {
            if ( ! empty( $ids ) ) {
                $count = count( (array) $ids );
                $log_summary[] = $count . ' ' . esc_html( $table );
            }
        }
        ?>
        <p style="font-size:12px;color:#6b7280;margin-bottom:10px;">
            <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
            <?php
            /* translators: %s: comma-separated list of installed item counts */
            printf( esc_html__( 'Installed: %s', ADN_TEXT_DOMAIN ), implode( ', ', $log_summary ) );
            ?>
        </p>
    <?php else : ?>
        <p style="font-size:12px;color:#9ca3af;margin-bottom:10px;">
            <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>
            <?php esc_html_e( 'No mock data log found. If you installed data before this feature existed, the remove button will still clean up tagged WordPress posts.', ADN_TEXT_DOMAIN ); ?>
        </p>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
          onsubmit="return confirm('<?php echo esc_js( __( 'Remove all tagged mock/demo data? This cannot be undone.', ADN_TEXT_DOMAIN ) ); ?>');">
        <input type="hidden" name="action" value="adn_remove_mock_data">
        <?php wp_nonce_field( 'adn_remove_mock_data' ); ?>
        <button type="submit" class="button" style="display:inline-flex;align-items:center;gap:6px;color:#b91c1c;border-color:#fca5a5;">
            <i class="fa-solid fa-trash-can"></i>
            <?php esc_html_e( 'Remove All Mock Data', ADN_TEXT_DOMAIN ); ?>
        </button>
    </form>
</div>
