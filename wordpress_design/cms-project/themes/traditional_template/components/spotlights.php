<?php
$spotlights = App_Data_Provider::get('spotlights');
if (empty($spotlights)) return;
?>
<section class="app-spotlights">
    <div class="app-container app-grid">
        <?php foreach ($spotlights as $spot): 
            $spot = (object)$spot;
        ?>
        <div class="app-spotlight-card">
            <div class="app-spotlight-icon"><?php echo esc_html($spot->icon ?? ''); ?></div>
            <h3 class="app-spotlight-title"><?php echo esc_html($spot->title ?? ''); ?></h3>
            <p class="app-spotlight-desc"><?php echo esc_html($spot->description ?? ''); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
