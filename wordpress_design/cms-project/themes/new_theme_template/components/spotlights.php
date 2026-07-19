<?php
$spotlights = NT_Data_Provider::get('spotlights');
if (empty($spotlights)) return;
?>
<section class="nt-spotlights">
    <div class="nt-container nt-grid">
        <?php foreach ($spotlights as $spot): 
            $spot = (object)$spot;
        ?>
        <div class="nt-spotlight-card">
            <div class="nt-spotlight-icon"><?php echo esc_html($spot->icon ?? ''); ?></div>
            <h3 class="nt-spotlight-title"><?php echo esc_html($spot->title ?? ''); ?></h3>
            <p class="nt-spotlight-desc"><?php echo esc_html($spot->description ?? ''); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
