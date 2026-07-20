<?php
$faqs    = NT_Data_Provider::get('faqs');
$content = nt_data( 'content' )['faqs'] ?? [];
if (empty($faqs)) return;
?>
<section class="nt-faqs">
    <div class="nt-container">
        <h2 class="nt-section-title"><?php echo wp_kses_post( $content['heading'] ?? 'Frequently Asked Questions' ); ?></h2>
        <div class="nt-faq-list">
            <?php foreach ($faqs as $faq): 
                $faq = (object)$faq;
            ?>
            <details class="nt-faq-item">
                <summary class="nt-faq-q"><?php echo esc_html($faq->question ?? ''); ?></summary>
                <div class="nt-faq-a"><?php echo wp_kses_post($faq->answer ?? ''); ?></div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
