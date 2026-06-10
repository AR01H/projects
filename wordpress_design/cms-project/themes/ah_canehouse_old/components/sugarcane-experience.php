<?php
defined( 'ABSPATH' ) || exit;

$_d      = CH_Story_Data::sugarcane_experience_settings();
$tag      = $args['tag']      ?? $_d['tag']      ?? '';
$title    = $args['title']    ?? $_d['title']    ?? '';
$subtitle = $args['subtitle'] ?? $_d['subtitle'] ?? '';
$steps    = $args['steps']    ?? $_d['steps']    ?? [];

$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-experience-section">
    <div class="container">
        <!-- Section Header -->
        <?php get_template_part( 'components/section-header', null, [
            'tag'  => $tag,
            'title' => $title,
            'body'  => $subtitle,
        ] ); ?>

    </div>
    <!-- Experience Journey - Vertical Timeline -->
    <div class="ch-experience-journey fade-up">
        <div class="ch-exp-timeline">
            <?php foreach ( $steps as $i => $step ) : ?>
                <div class="ch-exp-timeline-item">
                    <!-- Timeline Node -->
                    <div class="ch-exp-node">
                        <div class="ch-exp-node-num"><?php echo $i + 1; ?></div>
                    </div>

                    <!-- Timeline Content -->
                    <div class="ch-exp-content">
                        <!-- Emoji Large -->
                        <div class="ch-exp-emoji-large"><?php echo esc_html( $step['emoji'] ); ?></div>

                        <!-- Text Content -->
                        <div class="ch-exp-text">
                            <h3 class="ch-exp-title"><?php echo esc_html( $step['title'] ); ?></h3>
                            <p class="ch-exp-desc"><?php echo esc_html( $step['desc'] ); ?></p>
                            <div class="ch-exp-feeling"><?php echo esc_html( $step['feeling'] ); ?></div>
                        </div>
                    </div>

                    <!-- Timeline Line (connects to next) -->
                    <?php if ( $i < count( $steps ) - 1 ) : ?>
                        <div class="ch-exp-connector" aria-hidden="true"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Quote Section -->
    <div class="ch-exp-quote fade-up">
        <div class="ch-exp-quote-mark">"</div>
        <p class="ch-exp-quote-text">
            In India, sugarcane juice isn't just a drink-it's a moment of pause.
            A connection to the earth, to tradition, to the simplicity of nature's sweetness.
            We bring that moment to you, wherever you are.
        </p>
        <div class="ch-exp-quote-author">- The Cane House</div>
    </div>

    <!-- Sensory Experience -->
    <?php $sensory = $_d['sensory'] ?? []; ?>
    <div class="ch-exp-senses fade-up">
        <h3 class="ch-exp-senses-title">The Senses</h3>
        <div class="ch-exp-senses-grid">
            <?php foreach ( $sensory as $sense ) : ?>
                <div class="ch-sense-item">
                    <div class="ch-sense-emoji"><?php echo esc_html( $sense['icon'] ?? '' ); ?></div>
                    <div class="ch-sense-label"><?php echo esc_html( $sense['label'] ?? '' ); ?></div>
                    <p><?php echo esc_html( $sense['desc'] ?? '' ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
