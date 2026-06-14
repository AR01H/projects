<?php
/**
 * admin/tabs/home/sub-newsblocks.php
 *
 * Post picker for "Latest Regulations & Updates" and "Hot Topics" home sections.
 * Each section lets the admin search + select up to 5 WordPress posts.
 * Data is saved to wp_option 'adn_home_newsblocks'.
 */

defined( 'ABSPATH' ) || exit;

$saved    = get_option( 'adn_home_newsblocks', array() );
$reg_raw  = ( isset( $saved['regulations']['items'] ) && is_array( $saved['regulations']['items'] ) )
            ? $saved['regulations']['items'] : array();
$ht_raw   = ( isset( $saved['hot_topics']['items'] ) && is_array( $saved['hot_topics']['items'] ) )
            ? $saved['hot_topics']['items'] : array();

$notice = isset( $_GET['adn_saved'] ) ? (string) $_GET['adn_saved'] : '';
?>

<style>
.adn-post-pill{display:flex;gap:8px;align-items:center;margin-bottom:8px;background:#f6f7f7;padding:6px 10px;border-radius:4px;border:1px solid #dcdcde;}
.adn-post-pill .pill-title{flex:1;font-size:13px;color:#1d2327;}
.adn-search-wrap{position:relative;margin-bottom:6px;}
.adn-search-results{display:none;position:absolute;z-index:200;background:#fff;border:1px solid #c3c4c7;box-shadow:0 4px 12px rgba(0,0,0,.12);width:100%;max-height:220px;overflow-y:auto;border-radius:0 0 4px 4px;}
.adn-search-results .sr-item{padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f1;}
.adn-search-results .sr-item:last-child{border-bottom:0;}
.adn-search-results .sr-item:hover{background:#f0f6fc;}
.adn-search-results .sr-empty{padding:10px 12px;color:#999;font-size:13px;}
</style>

<?php if ( 'regulations' === $notice ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Regulations & Hot Topics settings saved.', ADN_TEXT_DOMAIN ); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="adn_save_home_newsblocks">
    <?php wp_nonce_field( 'adn_save_home_newsblocks' ); ?>

    <?php /* ── Latest Regulations & Updates ─────────────────────────────────── */ ?>
    <div class="card" style="max-width:none;margin-bottom:20px;">
        <h2><?php esc_html_e( 'Latest Regulations & Updates', ADN_TEXT_DOMAIN ); ?></h2>
        <p class="description"><?php esc_html_e( 'Search and select WordPress posts to display in this section. Up to 5. Title and URL are always pulled live from WordPress.', ADN_TEXT_DOMAIN ); ?></p>

        <p style="margin:16px 0 6px;font-weight:600;"><?php esc_html_e( 'Selected Posts', ADN_TEXT_DOMAIN ); ?></p>
        <div class="adn-search-wrap" style="max-width:500px;">
            <input type="text" id="reg-search" class="regular-text" placeholder="<?php esc_attr_e( 'Type to search posts…', ADN_TEXT_DOMAIN ); ?>" autocomplete="off" style="width:100%;">
            <div id="reg-search-results" class="adn-search-results"></div>
        </div>
        <div id="reg-selected">
            <?php foreach ( $reg_raw as $i => $row ) :
                $pid  = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
                $post = $pid ? get_post( $pid ) : null;
                if ( ! $post ) { continue; }
            ?>
                <div class="adn-post-pill">
                    <span class="pill-title"><?php echo esc_html( $post->post_title ); ?></span>
                    <input type="text"
                        name="regulations[items][<?php echo (int) $i; ?>][badge]"
                        value="<?php echo esc_attr( isset( $row['badge'] ) ? $row['badge'] : 'GOV UK' ); ?>"
                        placeholder="GOV UK" style="width:90px;font-size:12px;" title="Badge lines (one per line)">
                    <input type="hidden" name="regulations[items][<?php echo (int) $i; ?>][post_id]" value="<?php echo $pid; ?>">
                    <button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="description" style="margin-top:6px;"><?php esc_html_e( 'Badge: short lines shown on the GOV badge (e.g. "GOV UK"). One per line.', ADN_TEXT_DOMAIN ); ?></p>
    </div>

    <?php /* ── Hot Topics ───────────────────────────────────────────────────── */ ?>
    <div class="card" style="max-width:none;margin-bottom:20px;">
        <h2><?php esc_html_e( 'Hot Topics', ADN_TEXT_DOMAIN ); ?></h2>
        <p class="description"><?php esc_html_e( 'Search and select WordPress posts to display as Hot Topics. Up to 5. Set an emoji icon for each.', ADN_TEXT_DOMAIN ); ?></p>

        <p style="margin:16px 0 6px;font-weight:600;"><?php esc_html_e( 'Selected Posts', ADN_TEXT_DOMAIN ); ?></p>
        <div class="adn-search-wrap" style="max-width:500px;">
            <input type="text" id="ht-search" class="regular-text" placeholder="<?php esc_attr_e( 'Type to search posts…', ADN_TEXT_DOMAIN ); ?>" autocomplete="off" style="width:100%;">
            <div id="ht-search-results" class="adn-search-results"></div>
        </div>
        <div id="ht-selected">
            <?php foreach ( $ht_raw as $i => $row ) :
                $pid  = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
                $post = $pid ? get_post( $pid ) : null;
                if ( ! $post ) { continue; }
            ?>
                <div class="adn-post-pill">
                    <input type="text"
                        name="hot_topics[items][<?php echo (int) $i; ?>][icon]"
                        value="<?php echo esc_attr( isset( $row['icon'] ) ? $row['icon'] : '🔥' ); ?>"
                        placeholder="🔥" style="width:52px;text-align:center;" title="Icon emoji">
                    <span class="pill-title"><?php echo esc_html( $post->post_title ); ?></span>
                    <input type="hidden" name="hot_topics[items][<?php echo (int) $i; ?>][post_id]" value="<?php echo $pid; ?>">
                    <button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="description" style="margin-top:6px;"><?php esc_html_e( 'Description auto-filled from post excerpt. Up to 5 items.', ADN_TEXT_DOMAIN ); ?></p>
    </div>

    <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', ADN_TEXT_DOMAIN ); ?></button></p>
</form>

<script>
(function () {
    var _ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
    var _nonce   = '<?php echo esc_js( wp_create_nonce( 'adn_cat_search' ) ); ?>';

    /* Generic pill-remove */
    document.addEventListener('click', function (e) {
        var rm = e.target.closest('.adn-pill-remove');
        if (rm) { rm.closest('.adn-post-pill').remove(); renumber(); }
    });

    function renumber() {
        ['reg-selected', 'ht-selected'].forEach(function (id) {
            var wrap = document.getElementById(id);
            if (!wrap) { return; }
            wrap.querySelectorAll('.adn-post-pill').forEach(function (pill, idx) {
                pill.querySelectorAll('[name]').forEach(function (el) {
                    el.name = el.name.replace(/\[\d+\]/, '[' + idx + ']');
                });
            });
        });
    }

    function SearchPicker(searchId, resultsId, selectedId, maxItems, buildPill) {
        var si = document.getElementById(searchId);
        var ri = document.getElementById(resultsId);
        var sd = document.getElementById(selectedId);
        if (!si || !ri || !sd) { return; }
        var _t;

        si.addEventListener('input', function () {
            clearTimeout(_t);
            var q = this.value.trim();
            if (q.length < 1) { ri.style.display = 'none'; return; }
            _t = setTimeout(function () {
                fetch(_ajaxUrl + '?action=adn_cat_post_search&nonce=' + encodeURIComponent(_nonce) + '&q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data || !data.success || !data.data || !data.data.length) {
                            ri.innerHTML = '<div class="sr-empty">No results</div>';
                        } else {
                            ri.innerHTML = data.data.map(function (p) {
                                return '<div class="sr-item" data-id="' + p.id + '" data-title="' + (p.title || '').replace(/"/g, '&quot;') + '">'
                                    + (p.title || '') + '</div>';
                            }).join('');
                        }
                        ri.style.display = 'block';
                    })
                    .catch(function () { ri.style.display = 'none'; });
            }, 280);
        });

        ri.addEventListener('click', function (e) {
            var item = e.target.closest('.sr-item');
            if (!item) { return; }
            var currentCount = sd.querySelectorAll('.adn-post-pill').length;
            if (currentCount >= maxItems) {
                alert('Maximum ' + maxItems + ' items reached.');
                return;
            }
            var pill = document.createElement('div');
            pill.className = 'adn-post-pill';
            pill.innerHTML = buildPill(currentCount, item.dataset);
            sd.appendChild(pill);
            ri.style.display = 'none';
            si.value = '';
        });

        document.addEventListener('click', function (e) {
            if (!ri.contains(e.target) && e.target !== si) { ri.style.display = 'none'; }
        });
    }

    SearchPicker('reg-search', 'reg-search-results', 'reg-selected', 5, function (idx, d) {
        return '<span class="pill-title">' + d.title.replace(/</g, '&lt;') + '</span>'
            + '<input type="text" name="regulations[items][' + idx + '][badge]" value="GOV UK" placeholder="GOV UK" style="width:90px;font-size:12px;" title="Badge">'
            + '<input type="hidden" name="regulations[items][' + idx + '][post_id]" value="' + d.id + '">'
            + '<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>';
    });

    SearchPicker('ht-search', 'ht-search-results', 'ht-selected', 5, function (idx, d) {
        return '<input type="text" name="hot_topics[items][' + idx + '][icon]" value="🔥" placeholder="🔥" style="width:52px;text-align:center;" title="Icon">'
            + '<span class="pill-title">' + d.title.replace(/</g, '&lt;') + '</span>'
            + '<input type="hidden" name="hot_topics[items][' + idx + '][post_id]" value="' + d.id + '">'
            + '<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>';
    });
})();
</script>
