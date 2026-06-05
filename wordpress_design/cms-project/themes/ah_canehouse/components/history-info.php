<?php
/**
 * Sugarcane History Section
 * Drop into your theme or include via shortcode / template part
 * Requires: The Cane House CSS variables already loaded
 */
defined( 'ABSPATH' ) || exit;

function ch_get_history_pages() {
    return class_exists( 'CH_About_Data' ) ? CH_About_Data::history_pages() : [];
}
?>

<section id="ch-history" class="ch-history-section">
    <div class="container">

        <div class="ch-history__header fade-up">
            <div class="ch-section-tag">Story of Sugarcane</div>
            <h2 class="ch-section-title">10,000 Years of <span class="accent">Sweet History</span></h2>
            <p class="ch-section-body">From a wild grass in New Guinea to the world's most traded commodity - the extraordinary journey of sugarcane.</p>
        </div>

        
    </div>
    <!-- Book / Page-turn carousel -->
    <div class="ch-book" id="ch-book" aria-label="Sugarcane history book" role="region">

        <!-- Shadow beneath book -->
        <div class="ch-book-shadow" aria-hidden="true"></div>

        <!-- Pages stack -->
        <div class="ch-book-pages" id="ch-book-pages">
            <?php
            $pages = ch_get_history_pages();
            $total = count( $pages );
            foreach ( $pages as $i => $page ) :
                $page   = (array) $page;
                $is_first = $i === 0;
                $z      = $total - $i;
            ?>
            <article
                class="ch-book-page<?php echo $is_first ? ' is-active' : ''; ?>"
                data-index="<?php echo esc_attr( $i ); ?>"
                style="z-index:<?php echo esc_attr( $z ); ?>;"
                aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
            >
                <!-- Left leaf (image side) -->
                <div class="ch-page-left">
                    <div class="ch-page-img-wrap">
                        <img
                            src="<?php echo esc_url( $page['image'] ); ?>"
                            alt="<?php echo esc_attr( $page['image_alt'] ?? '' ); ?>"
                            class="ch-page-img"
                            loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
                        >
                        <div class="ch-page-era-badge"><?php echo esc_html( $page['era'] ); ?></div>
                        <!-- decorative cane watermark -->
                        <div class="ch-page-watermark" aria-hidden="true">🌿</div>
                    </div>
                    <!-- page number -->
                    <div class="ch-page-num-left"><?php echo sprintf( '%02d', $i + 1 ); ?></div>
                </div>

                <!-- Spine fold line -->
                <div class="ch-page-spine" aria-hidden="true"></div>

                <!-- Right leaf (content side) -->
                <div class="ch-page-right">
                    <div class="ch-page-content">
                        <div class="ch-section-tag ch-page-tag"><?php echo esc_html( $page['tag'] ); ?></div>

                        <h3 class="ch-page-title">
                            <?php
                            $title  = esc_html( $page['title'] ?? '' );
                            $accent = esc_html( $page['accent'] ?? '' );
                            if ( $accent ) {
                                echo str_replace( $accent, '<span class="accent">' . $accent . '</span>', $title );
                            } else {
                                echo $title;
                            }
                            ?>
                        </h3>

                        <p class="ch-page-body"><?php echo wp_kses_post( $page['body'] ?? '' ); ?></p>

                        <?php if ( ! empty( $page['countries'] ) ) : ?>
                        <div class="ch-page-countries">
                            <?php foreach ( (array) $page['countries'] as $country ) : ?>
                                <span class="ch-page-country"><?php echo esc_html( $country ); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $page['facts'] ) ) : ?>
                        <div class="ch-page-facts">
                            <?php foreach ( (array) $page['facts'] as $fact ) :
                                $fact = (array) $fact;
                            ?>
                                <div class="ch-page-fact">
                                    <span class="ch-pf-icon" aria-hidden="true"><?php echo esc_html( $fact['icon'] ?? '' ); ?></span>
                                    <span><?php echo esc_html( $fact['text'] ?? '' ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $page['impacts'] ) ) : ?>
                        <ul class="ch-page-impacts">
                            <?php foreach ( (array) $page['impacts'] as $impact ) : ?>
                                <li><?php echo wp_kses_post( $impact ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <div class="ch-page-num-right"><?php echo sprintf( '%02d', $i + 1 ); ?> / <?php echo esc_html( $total ); ?></div>
                </div>

                <!-- Page-turn clicker (right half triggers next) -->
                <button class="ch-page-turn-trigger ch-page-turn-next" aria-label="Next page" tabindex="<?php echo $is_first ? '0' : '-1'; ?>"></button>
            </article>
            <?php endforeach; ?>
        </div><!-- .ch-book-pages -->

        <!-- Overlay turn animation element (the "peeling" leaf) -->
        <div class="ch-turn-leaf" id="ch-turn-leaf" aria-hidden="true">
            <div class="ch-turn-leaf-front"></div>
            <div class="ch-turn-leaf-back"></div>
        </div>

    </div><!-- .ch-book -->

    <!-- Controls outside book -->
    <div class="ch-history-nav fade-up">
        <button class="ch-v-btn" id="ch-hist-prev" aria-label="Previous page">←</button>

        <div class="ch-hist-dots" id="ch-hist-dots" role="tablist" aria-label="History pages">
            <?php foreach ( $pages as $i => $_ ) : ?>
                <button
                    class="ch-hdot<?php echo $i === 0 ? ' active' : ''; ?>"
                    role="tab"
                    aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                    aria-label="Chapter <?php echo $i + 1; ?>"
                    data-goto="<?php echo esc_attr( $i ); ?>"
                ></button>
            <?php endforeach; ?>
        </div>

        <button class="ch-v-btn" id="ch-hist-next" aria-label="Next page">→</button>
    </div>

    <!-- Progress line (the "paper edge" line) -->
    <div class="ch-hist-progress-wrap" aria-hidden="true">
        <div class="ch-hist-progress-track">
            <div class="ch-hist-progress-fill" id="ch-hist-pfill" style="width:<?php echo round( 1 / $total * 100 ); ?>%"></div>
            <?php foreach ( $pages as $i => $page ) : ?>
                <div class="ch-hist-tick" style="left:<?php echo round( ($i / ($total-1)) * 100 ); ?>%" data-label="<?php echo esc_attr( $page['era'] ?? '' ); ?>"></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    const TOTAL_HISTORY_INFO   = <?php echo (int) $total; ?>;

</script>