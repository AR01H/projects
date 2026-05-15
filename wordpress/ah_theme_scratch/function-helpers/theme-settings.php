<?php
/**
 * scratch/function-helpers/theme-settings.php
 * Elite Admin Portal: Comprehensive Dashboard + Mega Menu Navigator.
 */
defined('ABSPATH') || exit;

function ah_theme_add_admin_menu() {
    $parent = 'advaith-homes';
    add_menu_page('Advaith Homes', 'Advaith Homes', 'manage_options', $parent, 'ah_dashboard_view', 'dashicons-admin-home', 3);
    add_submenu_page($parent, 'Portal Dashboard', 'Dashboard Hub', 'manage_options', $parent, 'ah_dashboard_view');
    add_submenu_page($parent, 'Articles Portal',  'Content Hub',  'manage_options', 'ah-articles', 'ah_articles_portal_view');
    add_submenu_page($parent, 'Navigation Portal', 'Menu Navigator', 'manage_options', 'ah-navigation', 'ah_navigation_view');
    add_submenu_page($parent, 'Global Settings',  'Site Settings', 'manage_options', 'ah-settings', 'ah_settings_view');
}
add_action('admin_menu', 'ah_theme_add_admin_menu');

/* ═══════════════════════════════════════════════
   1. DASHBOARD & ARTICLES (Existing Logic)
   ═══════════════════════════════════════════════ */
function ah_dashboard_view() {
    $cards = [
        ['title' => 'Articles Portal', 'icon' => 'admin-post', 'link' => 'admin.php?page=ah-articles'],
        ['title' => 'Menu Navigator',  'icon' => 'menu-alt3', 'link' => 'admin.php?page=ah-navigation'],
        ['title' => 'Client Projects', 'icon' => 'admin-home', 'link' => 'edit.php?post_type=ah_project'],
        ['title' => 'User Reviews',    'icon' => 'star-filled','link' => 'edit.php?post_type=ah_review'],
        ['title' => 'Contact Leads',   'icon' => 'email-alt', 'link' => 'edit.php?post_type=ah_lead'],
        ['title' => 'Site Settings',   'icon' => 'admin-generic','link' => 'admin.php?page=ah-settings'],
    ];
    ?>
    <style>
        .ah-wrap { padding:40px; }
        .ah-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:24px; }
        .ah-card { background:#fff; border-radius:16px; padding:40px 20px; text-align:center; text-decoration:none; border:1px solid #e2e8f0; transition:0.2s; display:block; }
        .ah-card:hover { transform:translateY(-5px); box-shadow:0 10px 25px -5px rgba(0,0,0,0.05); border-color:#3b82f6; }
        .ah-icon { width:64px; height:64px; border-radius:16px; background:#f8fafc; display:inline-flex; align-items:center; justify-content:center; margin-bottom:20px; color:#64748b; font-size:32px; }
        .ah-card:hover .ah-icon { color:#3b82f6; background:#eff6ff; }
        .ah-label { display:block; font-size:16px; font-weight:700; color:#475569; }
    </style>
    <div class="ah-wrap">
        <h1 style="font-weight:900; margin-bottom:40px;">Portal Dashboard</h1>
        <div class="ah-grid">
            <?php foreach ($cards as $c): ?>
                <a href="<?php echo admin_url($c['link']); ?>" class="ah-card">
                    <div class="ah-icon"><span class="dashicons dashicons-<?php echo $c['icon']; ?>"></span></div>
                    <span class="ah-label"><?php echo $c['title']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════
   2. NAVIGATION PORTAL (Mega Menu Builder)
   ═══════════════════════════════════════════════ */
function ah_navigation_view() {
    $config = get_option('ah_mega_menu_config', '[]');
    if (isset($_POST['save_nav'])) {
        update_option('ah_mega_menu_config', stripslashes($_POST['ah_nav_json']));
        echo '<div class="updated"><p>Mega Menu Configuration Saved!</p></div>';
        $config = stripslashes($_POST['ah_nav_json']);
    }
    ?>
    <style>
        .ah-nav-builder { background:#f1f5f9; padding:30px; border-radius:20px; }
        .ah-col-box { background:#fff; border:1px solid #e2e8f0; border-radius:15px; padding:25px; margin-bottom:20px; }
        .ah-nav-item { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:15px; margin-bottom:10px; position:relative; }
        .ah-remove { position:absolute; top:10px; right:10px; color:#ef4444; cursor:pointer; font-weight:900; font-size:10px; }
        .ah-input { width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; margin-bottom:10px; font-size:13px; }
        .ah-btn-add { background:#6366f1; color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:700; font-size:11px; }
    </style>
    <div class="ah-wrap">
        <h1 style="font-weight:900; margin-bottom:30px;">Menu Navigator (Mega Menu)</h1>
        <form method="post" class="ah-nav-builder">
            <div id="ah_nav_wrap"></div>
            <button type="button" class="ah-btn-add" id="add_col">+ Add Menu Column</button>
            <input type="hidden" name="ah_nav_json" id="ah_nav_json" value="<?php echo esc_attr($config); ?>">
            <div style="margin-top:30px;"><?php submit_button('Save Mega Menu Configuration', 'primary', 'save_nav'); ?></div>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let jsonInput = $('#ah_nav_json'), wrap = $('#ah_nav_wrap');

        function sync() {
            let data = [];
            $('.ah-col-box').each(function() {
                let col = { title: $(this).find('.col-title').val(), items: [] };
                $(this).find('.ah-nav-item').each(function() {
                    col.items.push({
                        icon: $(this).find('.it-icon').val(),
                        title: $(this).find('.it-title').val(),
                        sub: $(this).find('.it-sub').val(),
                        url: $(this).find('.it-url').val()
                    });
                });
                data.push(col);
            });
            jsonInput.val(JSON.stringify(data, null, 2));
        }

        $('#add_col').click(function() {
            wrap.append(`
                <div class="ah-col-box">
                    <span class="ah-remove">REMOVE COLUMN</span>
                    <label style="font-weight:800; font-size:11px; color:#64748b; text-transform:uppercase;">Column Title</label>
                    <input type="text" class="ah-input col-title" placeholder="e.g. BLOGS OR LAWS">
                    <div class="ah-items-wrap"></div>
                    <button type="button" class="ah-btn-add add-it">+ Add Menu Item</button>
                </div>
            `);
            sync();
        });

        $(document).on('click', '.add-it', function() {
            $(this).siblings('.ah-items-wrap').append(`
                <div class="ah-nav-item">
                    <span class="ah-remove">REMOVE ITEM</span>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:10px;">
                        <input type="text" class="ah-input it-icon" placeholder="Icon (e.g. 🏠)">
                        <input type="text" class="ah-input it-title" placeholder="Title">
                    </div>
                    <input type="text" class="ah-input it-sub" placeholder="Subtitle (e.g. Save £20k easily)">
                    <input type="text" class="ah-input it-url" placeholder="URL">
                </div>
            `);
            sync();
        });

        $(document).on('click', '.ah-remove', function() { $(this).parent().remove(); sync(); });
        $(document).on('input', '.ah-input', sync);

        // Load Initial
        try {
            let d = JSON.parse(jsonInput.val());
            d.forEach(c => {
                let $col = $(`<div class="ah-col-box"><span class="ah-remove">REMOVE COLUMN</span><label style="font-weight:800;font-size:11px;color:#64748b;text-transform:uppercase;">Column Title</label><input type="text" class="ah-input col-title" value="${c.title}"><div class="ah-items-wrap"></div><button type="button" class="ah-btn-add add-it">+ Add Menu Item</button></div>`);
                c.items.forEach(it => {
                    $col.find('.ah-items-wrap').append(`<div class="ah-nav-item"><span class="ah-remove">REMOVE ITEM</span><div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;"><input type="text" class="ah-input it-icon" value="${it.icon}"><input type="text" class="ah-input it-title" value="${it.title}"></div><input type="text" class="ah-input it-sub" value="${it.sub}"><input type="text" class="ah-input it-url" value="${it.url}"></div>`);
                });
                wrap.append($col);
            });
        } catch(e) {}
    });
    </script>
    <?php
}

function ah_articles_portal_view() {
    // (Existing articles portal logic remains here)
    include_once 'theme-settings-parts/articles-portal.php'; // Example of moving large parts out
}

function ah_settings_view() {
    $options = ['ah_header_code', 'ah_footer_code'];
    if (isset($_POST['submit'])) { foreach ($options as $opt) update_option($opt, $_POST[$opt]); }
    $vals = []; foreach ($options as $opt) $vals[$opt] = get_option($opt, '');
    ?>
    <div class="ah-wrap">
        <h1 style="font-weight:900;">Global Settings</h1>
        <form method="post" style="background:#fff; padding:30px; border-radius:15px; border:1px solid #e2e8f0; margin-top:30px;">
            <label style="display:block; font-weight:800; font-size:11px; color:#64748b; text-transform:uppercase; margin-bottom:10px;">Header Scripts</label>
            <textarea name="ah_header_code" style="width:100%; border-radius:8px; border:1px solid #cbd5e1; padding:15px;" rows="5"><?php echo esc_textarea($vals['ah_header_code']); ?></textarea>
            <?php submit_button('Update Site Configuration'); ?>
        </form>
    </div>
    <?php
}
