<?php
defined( 'ABSPATH' ) || exit;

$content  = app_data( 'content' )['product_experience'] ?? [];
$tag      = $args['tag']      ?? $content['tag']      ?? 'THE EXPERIENCE';
$title    = $args['title']    ?? $content['heading']  ?? 'A Journey of Taste';
$subtitle = $args['subtitle'] ?? $content['body']     ?? '';

$_d       = app_data( 'experience_data' ) ?: [];
$steps    = $args['steps']    ?? $_d['steps']    ?? [];
$quote    = $args['quote']    ?? $_d['quote']    ?? '';
$author   = $args['author']   ?? $_d['author']   ?? '';
$sensory  = $args['sensory']  ?? $_d['sensory']  ?? [];

$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="app-experience-section section">
    <div class="container wrapper">
        <!-- Section Header -->
        <?php get_template_part( 'components/parts/section-header', null, [
            'tag'   => $tag,
            'title' => $title,
            'body'  => $subtitle,
        ] ); ?>

    </div>
    <!-- Experience Journey - Vertical Timeline -->
    <div class="app-experience-journey fade-up">
        <div class="app-exp-timeline">
            <?php foreach ( $steps as $i => $step ) : ?>
                <div class="app-exp-timeline-item item">
                    <!-- Timeline Node -->
                    <div class="app-exp-node">
                        <div class="app-exp-node-num"><?php echo $i + 1; ?></div>
                    </div>

                    <!-- Timeline Content -->
                    <div class="app-exp-content content">
                        <!-- Emoji Large -->
                        <div class="app-exp-emoji-large"><?php echo esc_html( $step['emoji'] ?? '' ); ?></div>

                        <!-- Text Content -->
                        <div class="app-exp-text">
                            <h3 class="app-exp-title"><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
                            <p class="app-exp-desc"><?php echo esc_html( $step['desc'] ?? '' ); ?></p>
                            <div class="app-exp-feeling"><?php echo esc_html( $step['feeling'] ?? '' ); ?></div>
                        </div>
                    </div>

                    <!-- Timeline Line (connects to next) -->
                    <?php if ( $i < count( $steps ) - 1 ) : ?>
                        <div class="app-exp-connector" aria-hidden="true"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Quote Section -->
    <?php if ( $quote ) : ?>
    <div class="app-exp-quote fade-up card">
        <div class="app-exp-quote-mark">"</div>
        <p class="app-exp-quote-text">
            <?php echo esc_html( $quote ); ?>
        </p>
        <div class="app-exp-quote-author">- <?php echo esc_html( $author ); ?></div>
    </div>
    <?php endif; ?>

    <!-- Sensory Experience -->
    <?php if ( ! empty( $sensory ) ) : ?>
    <div class="app-exp-senses fade-up collection">
        <h3 class="app-exp-senses-title">The Senses</h3>
        <div class="app-exp-senses-grid grid">
            <?php foreach ( $sensory as $sense ) : ?>
                <div class="app-sense-item feature">
                    <div class="app-sense-emoji"><?php echo esc_html( $sense['icon'] ?? '' ); ?></div>
                    <div class="app-sense-label"><?php echo esc_html( $sense['label'] ?? '' ); ?></div>
                    <p><?php echo esc_html( $sense['desc'] ?? '' ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
