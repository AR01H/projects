<?php
/**
 * scratch/function-helpers/helpers.php
 * Dynamic Page Builder Rendering Logic.
 */

function ah_render_dynamic_page($json_raw) {
    $data = json_decode($json_raw, true);
    if (!$data || !isset($data['page'])) return;

    $p = $data['page'];
    
    // 1. Hero Section
    if (isset($p['hero'])) {
        ah_render_hero_section($p['hero']);
    }

    // 2. Banner Section
    if (isset($p['banner'])) {
        ah_render_banner_section($p['banner']);
    }

    // 3. Comparison Table
    if (isset($p['table'])) {
        ah_render_comparison_table($p['table']);
    }

    // 4. Process Timeline
    if (isset($p['process'])) {
        ah_render_process_timeline($p['process']);
    }

    // 5. Commitment / Do-Dont Section
    if (isset($p['commitmentHeadline'])) {
        ah_render_commitment_section($p);
    }

    // 6. Testimonials
    if (isset($p['testimonials'])) {
        ah_render_testimonials($p['testimonials']);
    }

    // 7. FAQ Section
    if (isset($p['commonQuestions'])) {
        ah_render_faq($p);
    }

    // 8. CTA Section
    if (isset($p['cta'])) {
        ah_render_cta($p['cta']);
    }
}

function ah_render_hero_section($h) {
    ?>
    <section class="ah-dynamic-hero" style="padding:100px 0; background:#f9fafb; text-align:center;">
        <div class="container">
            <?php if (isset($h['eyebrow'])): ?><span style="color:#6366f1; font-weight:700; text-transform:uppercase; letter-spacing:2px;"><?php echo esc_html($h['eyebrow']); ?></span><?php endif; ?>
            <h1 style="font-size:64px; font-weight:900; margin:20px 0; color:#1e293b;"><?php echo $h['headline']; ?></h1>
            <p style="font-size:20px; color:#64748b; max-width:800px; margin:0 auto 40px;"><?php echo esc_html($h['subtext']); ?></p>
            <?php if (isset($h['stats'])): ?>
                <div style="display:flex; justify-content:center; gap:60px;">
                    <?php foreach ($h['stats'] as $s): ?>
                        <div>
                            <span style="display:block; font-size:32px; font-weight:800; color:#1e293b;"><?php echo esc_html($s['num']); ?></span>
                            <span style="color:#94a3b8; font-weight:600; text-transform:uppercase; font-size:12px;"><?php echo esc_html($s['label']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

function ah_render_comparison_table($t) {
    ?>
    <section style="padding:100px 0; background:#fff;">
        <div class="container">
            <div style="text-align:center; margin-bottom:60px;">
                <span style="color:#6366f1; font-weight:700;"><?php echo esc_html($t['tag']); ?></span>
                <h2 style="font-size:40px; font-weight:900; margin-top:10px;"><?php echo esc_html($t['headline']); ?></h2>
            </div>
            <table style="width:100%; border-collapse:collapse; border-radius:16px; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.05);">
                <thead>
                    <tr style="background:#1e293b; color:#fff;">
                        <?php foreach ($t['columns'] as $col): ?>
                            <th style="padding:25px; text-align:left;"><?php echo esc_html($col); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($t['rows'] as $cat): ?>
                        <tr style="background:#f8fafc;"><td colspan="<?php echo count($t['columns']); ?>" style="padding:15px 25px; font-weight:800; color:#6366f1; font-size:12px; text-transform:uppercase;"><?php echo esc_html($cat['category']); ?></td></tr>
                        <?php foreach ($cat['items'] as $item): ?>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:25px;">
                                    <div style="font-weight:700; color:#1e293b;"><?php echo esc_html($item['name']); ?></div>
                                    <?php if (isset($item['desc'])): ?><div style="font-size:12px; color:#94a3b8;"><?php echo esc_html($item['desc']); ?></div><?php endif; ?>
                                </td>
                                <?php foreach ($item['values'] as $v): ?>
                                    <td style="padding:25px; text-align:center;">
                                        <?php echo ($v === true) ? '✅' : (($v === false) ? '—' : esc_html($v)); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
}

function ah_render_process_timeline($p) {
    ?>
    <section style="padding:100px 0; background:#f9fafb;">
        <div class="container">
            <div style="text-align:center; margin-bottom:80px;">
                <span style="color:#6366f1; font-weight:700;"><?php echo esc_html($p['tag']); ?></span>
                <h2 style="font-size:40px; font-weight:900; margin-top:10px;"><?php echo esc_html($p['headline']); ?></h2>
            </div>
            <div style="display:flex; justify-content:space-between; position:relative; max-width:1000px; margin:0 auto;">
                <div style="position:absolute; top:24px; left:0; right:0; height:2px; background:#e2e8f0; z-index:0;"></div>
                <?php foreach ($p['steps'] as $s): ?>
                    <div style="text-align:center; width:200px; position:relative; z-index:1;">
                        <div style="width:50px; height:50px; background:#6366f1; border-radius:50%; margin:0 auto 20px; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; border:4px solid #fff;"><?php echo explode(' ', $s['title'])[1]; ?></div>
                        <h4 style="font-weight:800; margin-bottom:10px;"><?php echo esc_html($s['title']); ?></h4>
                        <p style="font-size:14px; color:#64748b;"><?php echo esc_html($s['desc']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function ah_render_commitment_section($p) {
    ?>
    <section style="padding:100px 0; background:#fff;">
        <div class="container">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center;">
                <div>
                    <span style="color:#6366f1; font-weight:700;"><?php echo esc_html($p['commitmentTitle']); ?></span>
                    <h2 style="font-size:48px; font-weight:900; margin-top:10px; color:#1e293b;"><?php echo esc_html($p['commitmentHeadline']); ?></h2>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:40px;">
                    <div style="background:#f0fdf4; padding:30px; border-radius:20px; border:1px solid #dcfce7;">
                        <h4 style="color:#166534; font-weight:800; margin-bottom:15px;">What We Do</h4>
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach ($p['whatWeDo']['do'] as $item): ?>
                                <li style="margin-bottom:10px; font-size:14px; color:#166534;">✅ <?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div style="background:#fef2f2; padding:30px; border-radius:20px; border:1px solid #fee2e2;">
                        <h4 style="color:#991b1b; font-weight:800; margin-bottom:15px;">What We Don't</h4>
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach ($p['whatWeDo']['dont'] as $item): ?>
                                <li style="margin-bottom:10px; font-size:14px; color:#991b1b;">❌ <?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

function ah_render_banner_section($b) {
    ?>
    <section style="padding:80px 0; background:#fff;">
        <div class="container">
            <div style="background:linear-gradient(rgba(15,23,42,0.8), rgba(15,23,42,0.8)), url('<?php echo esc_url($b['image']); ?>'); background-size:cover; background-position:center; padding:100px; border-radius:40px; text-align:center; color:#fff;">
                <h2 style="font-size:48px; font-weight:900; margin-bottom:20px;"><?php echo esc_html($b['title']); ?></h2>
                <p style="font-size:18px; opacity:0.9; max-width:600px; margin:0 auto;"><?php echo esc_html($b['text']); ?></p>
            </div>
        </div>
    </section>
    <?php
}

function ah_render_testimonials($ts) {
    ?>
    <section style="padding:100px 0; background:#f8fafc; overflow:hidden;">
        <div class="container" style="display:flex; gap:30px; overflow-x:auto; padding-bottom:40px;">
            <?php foreach ($ts as $t): ?>
                <div style="min-width:400px; background:#fff; padding:40px; border-radius:24px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);">
                    <p style="font-size:24px; font-family:serif; font-style:italic; color:#1e293b; margin-bottom:30px;">"<?php echo esc_html($t['text']); ?>"</p>
                    <div style="font-weight:800; color:#1e293b;"><?php echo esc_html($t['author']); ?></div>
                    <div style="font-size:12px; color:#94a3b8;"><?php echo esc_html($t['location']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function ah_render_faq($p) {
    ?>
    <section style="padding:100px 0; background:#fff;">
        <div class="container" style="max-width:800px; margin:0 auto;">
            <div style="text-align:center; margin-bottom:60px;">
                <span style="color:#6366f1; font-weight:700;"><?php echo esc_html($p['faqTitle']); ?></span>
                <h2 style="font-size:40px; font-weight:900; margin-top:10px;"><?php echo esc_html($p['faqHeadline']); ?></h2>
            </div>
            <?php foreach ($p['commonQuestions'] as $f): ?>
                <div style="margin-bottom:20px; border:1px solid #e2e8f0; border-radius:12px; padding:25px;">
                    <h4 style="font-weight:800; margin-bottom:10px; color:#1e293b;"><?php echo esc_html($f['q']); ?></h4>
                    <p style="color:#64748b; margin:0;"><?php echo esc_html($f['a']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function ah_render_cta($cta) {
    ?>
    <section style="padding:100px 0; background:#6366f1; text-align:center; color:#fff;">
        <div class="container">
            <h2 style="font-size:48px; font-weight:900; margin-bottom:20px;"><?php echo esc_html($cta['headline']); ?></h2>
            <p style="font-size:20px; opacity:0.9; max-width:600px; margin:0 auto 40px;"><?php echo esc_html($cta['subtext']); ?></p>
            <a href="<?php echo esc_url($cta['buttonUrl']); ?>" style="display:inline-block; background:#fff; color:#6366f1; padding:18px 40px; border-radius:12px; font-weight:800; text-decoration:none;"><?php echo esc_html($cta['buttonText']); ?></a>
        </div>
    </section>
    <?php
}
