<?php
$banners = NT_Data_Provider::get('home_banner');
if (empty($banners)) return;
$banner = (object)$banners[0];
?>
<section class="nt-hero-banner" style="background-image: url('<?php echo esc_url($banner->image ?? ''); ?>'); text-align: <?php echo esc_attr($banner->text_align ?? 'center'); ?>;">
    <div class="nt-hero-overlay"></div>
    <div class="nt-container nt-hero-content">
        <?php if (!empty($banner->subtitle)): ?>
            <p class="nt-hero-subtitle"><?php echo esc_html($banner->subtitle); ?></p>
        <?php endif; ?>
        <h1 class="nt-hero-title"><?php echo esc_html($banner->title ?? ''); ?></h1>
        <p class="nt-hero-desc"><?php echo esc_html($banner->description ?? ''); ?></p>
        <?php if (!empty($banner->btn_url)): ?>
            <a href="<?php echo esc_url($banner->btn_url); ?>" class="nt-btn nt-btn-primary"><?php echo esc_html($banner->btn_text ?? 'Learn More'); ?></a>
        <?php endif; ?>
    </div>
</section>
