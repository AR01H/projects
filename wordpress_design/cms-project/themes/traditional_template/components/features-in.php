<?php
$features = NT_Data_Provider::get('features_in');
$content  = app_data( 'content' )['features_in'] ?? [];
if (empty($features)) return;
?>
<section class="app-features-in">
    <div class="app-container">
        <h3 class="app-features-title"><?php echo esc_html( $content['heading'] ?? 'Featured In' ); ?></h3>
        <div class="app-features-logos">
            <?php foreach ($features as $f): 
                $f = (object)$f;
            ?>
            <div class="app-feature-logo">
                <?php if (!empty($f->image_id)): ?>
                    <?php echo wp_get_attachment_image($f->image_id, 'medium'); ?>
                <?php else: ?>
                    <span class="app-feature-name"><?php echo esc_html($f->name ?? ''); ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
