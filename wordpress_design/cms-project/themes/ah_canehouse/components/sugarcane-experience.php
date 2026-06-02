<?php
defined( 'ABSPATH' ) || exit;

$tag     = $args['tag']     ?? 'The Experience';
$title   = $args['title']   ?? 'Why We Miss <span class="accent" style="color:var(--ch-lime);">India\'s Sugarcane</span>';
$subtitle = $args['subtitle'] ?? 'From street vendor to your glass - a taste of tradition';

$steps = $args['steps'] ?? [
    [
        'emoji' => '🥬',
        'title' => 'Fresh Selection',
        'desc' => 'Sweet, golden stalks hand-picked from the fields. That familiar crunch when the vendor selects the finest canes.',
        'feeling' => 'Anticipation'
    ],
    [
        'emoji' => '⚙️',
        'title' => 'The Press',
        'desc' => 'The mechanical press groans to life, crushing the stalks with rhythmic force. Juice flows like liquid gold onto the collection tray.',
        'feeling' => 'Wonder'
    ],
    [
        'emoji' => '🧊',
        'title' => 'Chill & Serve',
        'desc' => 'Ice shards dropped in with a satisfying clink. The vendor hands you the glass with a smile - it\'s an art form.',
        'feeling' => 'Joy'
    ],
    [
        'emoji' => '😋',
        'title' => 'First Sip',
        'desc' => 'That first sip hits different. Pure, natural, no chemicals. The sweetness, the freshness, the memories it brings.',
        'feeling' => 'Bliss'
    ],
];

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
    <div class="ch-exp-senses fade-up">
        <h3 class="ch-exp-senses-title">The Senses</h3>
        <div class="ch-exp-senses-grid">
            <div class="ch-sense-item">
                <div class="ch-sense-emoji">👁️</div>
                <div class="ch-sense-label">See</div>
                <p>Golden liquid flowing into glass containers</p>
            </div>
            <div class="ch-sense-item">
                <div class="ch-sense-emoji">👂</div>
                <div class="ch-sense-label">Hear</div>
                <p>The mechanical crush, the satisfying clink of ice</p>
            </div>
            <div class="ch-sense-item">
                <div class="ch-sense-emoji">👃</div>
                <div class="ch-sense-label">Smell</div>
                <p>Fresh, natural sweetness of pure sugarcane</p>
            </div>
            <div class="ch-sense-item">
                <div class="ch-sense-emoji">👅</div>
                <div class="ch-sense-label">Taste</div>
                <p>Clean, pure, naturally sweet with no additives</p>
            </div>
        </div>
    </div>
</section>
