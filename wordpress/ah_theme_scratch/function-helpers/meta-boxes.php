<?php
/**
 * scratch/function-helpers/meta-boxes.php
 * The Infinite Portal: Comprehensive Visual Builder for ALL JSON Components.
 */

function ah_add_custom_meta_boxes() {
    $all_cpts = ['ah_review', 'ah_post', 'ah_project', 'ah_guide', 'page'];
    add_meta_box('ah_page_builder', '🧩 Elite Page Portal (Infinite Builder)', 'ah_page_builder_cb', $all_cpts, 'normal', 'high');
}
add_action('add_meta_boxes', 'ah_add_custom_meta_boxes');

// Enqueue WP Media Library
function ah_enqueue_admin_assets($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'ah_enqueue_admin_assets');

function ah_page_builder_cb($post) {
    $json = get_post_meta($post->ID, '_ah_page_builder_json', true) ?: '{"page":{}}';
    ?>
    <style>
        .ah-portal { background:#f1f5f9; padding:20px; border-radius:15px; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; }
        .ah-sec { background:#fff; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:20px; overflow:hidden; }
        .ah-sec-head { padding:18px 25px; background:#f8fafc; border-bottom:1px solid #e2e8f0; font-weight:900; color:#1e293b; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
        .ah-sec-body { padding:30px; display:none; border-top:1px solid #f1f5f9; }
        .ah-sec.active .ah-sec-body { display:block; }
        .ah-field { margin-bottom:20px; }
        .ah-label { display:block; font-weight:800; font-size:11px; color:#64748b; text-transform:uppercase; margin-bottom:10px; }
        .ah-input { width:100%; padding:14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; }
        .ah-repeater { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:25px; margin-top:15px; }
        .ah-item { background:#fff; border:1px solid #e2e8f0; padding:20px; border-radius:12px; margin-bottom:15px; position:relative; }
        .ah-remove { position:absolute; top:12px; right:15px; color:#ef4444; cursor:pointer; font-size:11px; font-weight:900; }
        .ah-btn-add { background:#6366f1; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:800; font-size:11px; margin-top:10px; }
        .ah-img-flex { display:flex; gap:10px; align-items:center; }
        .ah-btn-upload { background:#1e293b; color:#fff; border:none; padding:12px 20px; border-radius:10px; cursor:pointer; font-weight:800; font-size:12px; }
        .ah-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .ah-checkbox-grid { display:flex; gap:20px; background:#f1f5f9; padding:15px; border-radius:10px; border:1px solid #e2e8f0; margin-top:10px; }
        .ah-check-item { display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#475569; }
    </style>

    <div class="ah-portal">
        <!-- 1. HERO -->
        <div class="ah-sec" data-section="hero">
            <div class="ah-sec-head">1. Hero Section <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-field"><label class="ah-label">Eyebrow</label><input type="text" class="ah-input" data-key="eyebrow"></div>
                <div class="ah-field"><label class="ah-label">Headline</label><input type="text" class="ah-input" data-key="headline"></div>
                <div class="ah-field"><label class="ah-label">Subtext</label><textarea class="ah-input" data-key="subtext" rows="3"></textarea></div>
                <div class="ah-repeater" data-repeater="stats"><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="stat">+ Add Stat</button></div>
            </div>
        </div>

        <!-- 2. BANNER -->
        <div class="ah-sec" data-section="banner">
            <div class="ah-sec-head">2. Banner (Image Upload) <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-field">
                    <label class="ah-label">Image URL</label>
                    <div class="ah-img-flex"><input type="text" class="ah-input" data-key="image" id="ah_banner_img"><button type="button" class="ah-btn-upload" data-target="ah_banner_img">Select Image</button></div>
                </div>
                <div class="ah-grid-2">
                    <div class="ah-field"><label class="ah-label">Title</label><input type="text" class="ah-input" data-key="title"></div>
                    <div class="ah-field"><label class="ah-label">Text</label><textarea class="ah-input" data-key="text"></textarea></div>
                </div>
            </div>
        </div>

        <!-- 3. CHALLENGES -->
        <div class="ah-sec" data-section="challenges">
            <div class="ah-sec-head">3. Challenges & Phases <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-grid-2">
                    <div class="ah-field"><label class="ah-label">Title</label><input type="text" class="ah-input" data-parent-key="challengesTitle"></div>
                    <div class="ah-field"><label class="ah-label">Headline</label><input type="text" class="ah-input" data-parent-key="challengesHeadline"></div>
                </div>
                <div class="ah-repeater" data-repeater="phases"><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="phase">+ Add Phase</button></div>
            </div>
        </div>

        <!-- 4. PROCESS -->
        <div class="ah-sec" data-section="process">
            <div class="ah-sec-head">4. Process Timeline <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-grid-2">
                    <div class="ah-field"><label class="ah-label">Tag</label><input type="text" class="ah-input" data-key="tag"></div>
                    <div class="ah-field"><label class="ah-label">Headline</label><input type="text" class="ah-input" data-key="headline"></div>
                </div>
                <div class="ah-repeater" data-repeater="steps"><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="step">+ Add Step</button></div>
            </div>
        </div>

        <!-- 5. COMPARISON TABLE -->
        <div class="ah-sec" data-section="table">
            <div class="ah-sec-head">5. Comparison Table (Checkbox) <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-grid-2">
                    <div class="ah-field"><label class="ah-label">Tag</label><input type="text" class="ah-input" data-key="tag"></div>
                    <div class="ah-field"><label class="ah-label">Headline</label><input type="text" class="ah-input" data-key="headline"></div>
                </div>
                <div class="ah-field"><label class="ah-label">Columns (Comma Separated)</label><input type="text" class="ah-input" data-key="columns" id="ah_table_cols" placeholder="Features, Premium, Standard, Basic"></div>
                <div class="ah-repeater" data-repeater="table_rows"><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="table_cat">+ Add Category</button></div>
            </div>
        </div>

        <!-- 6. TESTIMONIALS -->
        <div class="ah-sec" data-section="testimonials">
            <div class="ah-sec-head">6. Testimonials <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-repeater" data-repeater="testimonials"><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="testimonial">+ Add Testimonial</button></div>
            </div>
        </div>

        <!-- 7. COMMITMENT -->
        <div class="ah-sec" data-section="commitment">
            <div class="ah-sec-head">7. Commitment Section <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-grid-2">
                    <div class="ah-field"><label class="ah-label">Title</label><input type="text" class="ah-input" data-parent-key="commitmentTitle"></div>
                    <div class="ah-field"><label class="ah-label">Headline</label><input type="text" class="ah-input" data-parent-key="commitmentHeadline"></div>
                </div>
                <div class="ah-grid-2">
                    <div class="ah-repeater" data-repeater="do"><label class="ah-label">What We Do</label><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="do_item">+ Add Do</button></div>
                    <div class="ah-repeater" data-repeater="dont"><label class="ah-label">What We Don't</label><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="dont_item">+ Add Don't</button></div>
                </div>
            </div>
        </div>

        <!-- 8. FAQ -->
        <div class="ah-sec" data-section="faq">
            <div class="ah-sec-head">8. FAQ Section <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-grid-2">
                    <div class="ah-field"><label class="ah-label">Title</label><input type="text" class="ah-input" data-parent-key="faqTitle"></div>
                    <div class="ah-field"><label class="ah-label">Headline</label><input type="text" class="ah-input" data-parent-key="faqHeadline"></div>
                </div>
                <div class="ah-repeater" data-repeater="faqs"><div class="ah-items"></div><button type="button" class="ah-btn-add" data-add="faq_item">+ Add FAQ</button></div>
            </div>
        </div>

        <!-- 9. CTA -->
        <div class="ah-sec" data-section="cta">
            <div class="ah-sec-head">9. CTA Section <span>▼</span></div>
            <div class="ah-sec-body">
                <div class="ah-field"><label class="ah-label">Headline</label><input type="text" class="ah-input" data-key="headline"></div>
                <div class="ah-field"><label class="ah-label">Subtext</label><textarea class="ah-input" data-key="subtext"></textarea></div>
                <div class="ah-grid-2"><div class="ah-field"><label class="ah-label">Button Text</label><input type="text" class="ah-input" data-key="buttonText"></div><div class="ah-field"><label class="ah-label">Button URL</label><input type="text" class="ah-input" data-key="buttonUrl"></div></div>
            </div>
        </div>

        <input type="hidden" id="ah_page_builder_json" name="ah_page_builder_json" value="<?php echo esc_attr($json); ?>">
        <div style="background:#1e293b; color:#94a3b8; padding:20px; border-radius:12px; margin-top:20px; font-family:monospace; font-size:11px;"><pre id="ah_json_out"></pre></div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const jsonInput = $('#ah_page_builder_json'), preview = $('#ah_json_out'), colInput = $('#ah_table_cols');
        $('.ah-sec-head').click(function() { $(this).parent().toggleClass('active').siblings().removeClass('active'); });

        $(document).on('click', '.ah-btn-upload', function(e) {
            e.preventDefault(); let target = $(this).data('target');
            let frame = wp.media({ title: 'Select Image', button: { text: 'Use image' }, multiple: false });
            frame.on('select', function() { $('#' + target).val(frame.state().get('selection').first().toJSON().url).trigger('input'); });
            frame.open();
        });

        function getCols() { return colInput.val().split(',').map(s => s.trim()).filter(Boolean).slice(1); }

        function sync() {
            let data = { page: {} };
            $('.ah-sec').each(function() {
                let sec = $(this).data('section');
                if (!data.page[sec]) data.page[sec] = {};
                $(this).find('.ah-input[data-key]').each(function() {
                    let v = $(this).val();
                    if (sec === 'table' && $(this).data('key') === 'columns') v = v.split(',').map(s => s.trim()).filter(Boolean);
                    data.page[sec][$(this).data('key')] = v;
                });
                $(this).find('.ah-input[data-parent-key]').each(function() { data.page[$(this).data('parent-key')] = $(this).val(); });

                if (sec === 'hero') {
                    data.page.hero.stats = [];
                    $(this).find('.ah-item').each(function() { data.page.hero.stats.push({ num: $(this).find('[data-sub="num"]').val(), label: $(this).find('[data-sub="label"]').val() }); });
                }
                if (sec === 'challenges') {
                    data.page.challenges = [];
                    $(this).find('.ah-item').each(function() { data.page.challenges.push({ phase: $(this).find('[data-sub="phase"]').val(), icon: $(this).find('[data-sub="icon"]').val(), items: $(this).find('[data-sub="items"]').val().split('\n').filter(Boolean) }); });
                }
                if (sec === 'process') {
                    data.page.process.steps = [];
                    $(this).find('.ah-item').each(function() { data.page.process.steps.push({ title: $(this).find('[data-sub="title"]').val(), desc: $(this).find('[data-sub="desc"]').val() }); });
                }
                if (sec === 'table') {
                    data.page.table.rows = [];
                    $(this).find('.ah-table-cat').each(function() {
                        let cat = { category: $(this).find('[data-cat-key]').val(), items: [] };
                        $(this).find('.ah-item').each(function() {
                            let vals = []; $(this).find('.ah-col-check').each(function() { vals.push($(this).is(':checked')); });
                            cat.items.push({ name: $(this).find('[data-sub="name"]').val(), values: vals });
                        });
                        data.page.table.rows.push(cat);
                    });
                }
                if (sec === 'testimonials') {
                    data.page.testimonials = [];
                    $(this).find('.ah-item').each(function() { data.page.testimonials.push({ text: $(this).find('[data-sub="text"]').val(), author: $(this).find('[data-sub="author"]').val(), location: $(this).find('[data-sub="location"]').val() }); });
                }
                if (sec === 'commitment') {
                    data.page.whatWeDo = { do: [], dont: [] };
                    $('.ah-repeater[data-repeater="do"] .ah-input').each(function() { if($(this).val()) data.page.whatWeDo.do.push($(this).val()); });
                    $('.ah-repeater[data-repeater="dont"] .ah-input').each(function() { if($(this).val()) data.page.whatWeDo.dont.push($(this).val()); });
                }
                if (sec === 'faq') {
                    data.page.commonQuestions = [];
                    $(this).find('.ah-item').each(function() { data.page.commonQuestions.push({ q: $(this).find('[data-sub="q"]').val(), a: $(this).find('[data-sub="a"]').val() }); });
                }
            });
            jsonInput.val(JSON.stringify(data, null, 2));
            preview.text(jsonInput.val());
        }

        $(document).on('click', '[data-add]', function() {
            let type = $(this).data('add'), html = `<div class="ah-item"><span class="ah-remove">×</span>`;
            if (type === 'stat') html += `<div class="ah-grid-2"><input type="text" placeholder="Value" class="ah-input" data-sub="num"><input type="text" placeholder="Label" class="ah-input" data-sub="label"></div>`;
            if (type === 'phase') html += `<input type="text" placeholder="Phase" class="ah-input" data-sub="phase" style="margin-bottom:10px;"><input type="text" placeholder="Icon" class="ah-input" data-sub="icon" style="margin-bottom:10px;"><textarea placeholder="Items (one per line)" class="ah-input" data-sub="items"></textarea>`;
            if (type === 'step') html += `<input type="text" placeholder="Step" class="ah-input" data-sub="title" style="margin-bottom:10px;"><textarea placeholder="Desc" class="ah-input" data-sub="desc"></textarea>`;
            if (type === 'testimonial') html += `<textarea placeholder="Text" class="ah-input" data-sub="text" style="margin-bottom:10px;"></textarea><div class="ah-grid-2"><input type="text" placeholder="Author" class="ah-input" data-sub="author"><input type="text" placeholder="Location" class="ah-input" data-sub="location"></div>`;
            if (type === 'do_item' || type === 'dont_item') html += `<input type="text" placeholder="Item" class="ah-input">`;
            if (type === 'faq_item') html += `<input type="text" placeholder="Q" class="ah-input" data-sub="q" style="margin-bottom:10px;"><textarea placeholder="A" class="ah-input" data-sub="a"></textarea>`;
            if (type === 'table_cat') { $(this).siblings('.ah-items').append(`<div class="ah-table-cat"><span class="ah-remove">×</span><input type="text" class="ah-input" data-cat-key="name" placeholder="Category" style="margin-bottom:15px;"><div class="ah-table-items"></div><button type="button" class="ah-btn-add" data-add="table_item">+ Add Item</button></div>`); return sync(); }
            if (type === 'table_item') {
                let c = getCols(), h = `<div class="ah-item"><span class="ah-remove">×</span><input type="text" placeholder="Name" class="ah-input" data-sub="name" style="margin-bottom:10px;"><div class="ah-checkbox-grid">`;
                c.forEach(col => { h += `<label class="ah-check-item"><input type="checkbox" class="ah-col-check"> ${col}</label>`; });
                h += `</div></div>`; $(this).siblings('.ah-table-items').append(h); return sync();
            }
            html += `</div>`; $(this).siblings('.ah-items').append(html); sync();
        });

        $(document).on('click', '.ah-remove', function() { $(this).parent().remove(); sync(); });
        $(document).on('input change', '.ah-input, .ah-col-check', sync);
        sync();
    });
    </script>
    <?php
}

function ah_save_scratch_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['ah_page_builder_json'])) update_post_meta($post_id, '_ah_page_builder_json', $_POST['ah_page_builder_json']);
}
add_action('save_post', 'ah_save_scratch_meta');
