<?php
$features = NT_Data_Provider::get('features_in');
if (empty($features)) return;
?>
<section class="nt-features-in">
    <div class="nt-container">
        <h3 class="nt-features-title">Featured In</h3>
        <div class="nt-features-logos">
            <?php foreach ($features as $f): 
                $f = (object)$f;
            ?>
            <div class="nt-feature-logo">
                <?php if (!empty($f->image_id)): ?>
                    <?php echo wp_get_attachment_image($f->image_id, 'medium'); ?>
                <?php else: ?>
                    <span class="nt-feature-name"><?php echo esc_html($f->name ?? ''); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
