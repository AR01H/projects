<?php
/**
 * Company History Section
 * Drop into your theme or include via shortcode / template part
 */
defined( 'ABSPATH' ) || exit;

$_d     = app_data( 'history' ) ?: [];
$tag    = $args['tag']   ?? $_d['tag']   ?? 'OUR STORY';
$title  = $args['title'] ?? $_d['title'] ?? 'The Journey';
$body   = $args['body']  ?? $_d['body']  ?? '';
$pages  = $args['pages'] ?? $_d['pages'] ?? [];

if ( empty( $pages ) ) return;
$total = count( $pages );
?>

<section id="app-history" class="app-history-section section">
    <div class="container wrapper">

        <?php
        get_template_part( 'components/parts/section-header', null, [
            'tag'           => $tag,
            'title'         => $title,
            'body'          => $body,
            'wrapper_class' => 'app-history__header',
        ] ); ?>

    </div>
    <!-- Book / Page-turn carousel -->
    <div class="app-book" id="app-book" aria-label="Company history book" role="region">

        <!-- Shadow beneath book -->
        <div class="app-book-shadow" aria-hidden="true"></div>

        <!-- Pages stack -->
        <div class="app-book-pages" id="app-book-pages">
            <?php
            foreach ( $pages as $i => $page ) :
                $page   = (array) $page;
                $is_first = $i === 0;
                $z      = $total - $i;
            ?>
            <article
                class="app-book-page<?php echo $is_first ? ' is-active' : ''; ?> card"
                data-index="<?php echo esc_attr( $i ); ?>"
                style="z-index:<?php echo esc_attr( $z ); ?>;"
                aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
            >
                <!-- Left leaf (image side) -->
                <div class="app-page-left">
                    <div class="app-page-img-wrap">
                        <img
                            src="<?php echo esc_url( $page['image'] ?? '' ); ?>"
                            alt="<?php echo esc_attr( $page['image_alt'] ?? '' ); ?>"
                            class="app-page-img"
                            loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
                        >
                        <?php if ( ! empty( $page['era'] ) ) : ?>
                        <div class="app-page-era-badge"><?php echo esc_html( $page['era'] ); ?></div>
                        <?php endif; ?>
                    </div>
                    <!-- page number -->
                    <div class="app-page-num-left"><?php echo sprintf( '%02d', $i + 1 ); ?></div>
                </div>

                <!-- Spine fold line -->
                <div class="app-page-spine" aria-hidden="true"></div>

                <!-- Right leaf (content side) -->
                <div class="app-page-right content">
                    <div class="app-page-content">
                        <?php if ( ! empty( $page['tag'] ) ) : ?>
                        <div class="app-section-tag app-page-tag"><?php echo esc_html( $page['tag'] ); ?></div>
                        <?php endif; ?>

                        <h3 class="app-page-title">
                            <?php
                            $title_str  = esc_html( $page['title'] ?? '' );
                            $accent = esc_html( $page['accent'] ?? '' );
                            if ( $accent ) {
                                echo str_replace( $accent, '<span class="accent">' . $accent . '</span>', $title_str );
                            } else {
                                echo $title_str;
                            }
                            ?>
                        </h3>

                        <p class="app-page-body"><?php echo wp_kses_post( $page['body'] ?? '' ); ?></p>

                        <?php if ( ! empty( $page['countries'] ) ) : ?>
                        <div class="app-page-countries">
                            <?php foreach ( (array) $page['countries'] as $country ) : ?>
                                <span class="app-page-country"><?php echo esc_html( $country ); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $page['facts'] ) ) : ?>
                        <div class="app-page-facts collection">
                            <?php foreach ( (array) $page['facts'] as $fact ) :
                                $fact = (array) $fact;
                            ?>
                                <div class="app-page-fact item">
                                    <span class="app-pf-icon" aria-hidden="true"><?php echo esc_html( $fact['icon'] ?? '' ); ?></span>
                                    <span><?php echo esc_html( $fact['text'] ?? '' ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="app-page-num-right"><?php echo sprintf( '%02d', $i + 1 ); ?> / <?php echo esc_html( $total ); ?></div>
                </div>

                <!-- Page-turn clicker (right half triggers next) -->
                <button class="app-page-turn-trigger app-page-turn-next" aria-label="Next page" tabindex="<?php echo $is_first ? '0' : '-1'; ?>"></button>
            </article>
            <?php endforeach; ?>
        </div><!-- .app-book-pages -->
    </div><!-- .app-book -->

    <!-- Controls outside book -->
    <div class="app-history-nav fade-up">
        <button class="app-v-btn button" id="app-hist-prev" aria-label="Previous page">←</button>

        <div class="app-hist-dots" id="app-hist-dots" role="tablist" aria-label="History pages">
            <?php foreach ( $pages as $i => $_ ) : ?>
                <button
                    class="app-hdot<?php echo $i === 0 ? ' active' : ''; ?>"
                    role="tab"
                    aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                    aria-label="Chapter <?php echo $i + 1; ?>"
                    data-goto="<?php echo esc_attr( $i ); ?>"
                ></button>
            <?php endforeach; ?>
        </div>

        <button class="app-v-btn button" id="app-hist-next" aria-label="Next page">→</button>
    </div>

    <!-- Progress line (the "paper edge" line) -->
    <div class="app-hist-progress-wrap" aria-hidden="true">
        <div class="app-hist-progress-track">
            <div class="app-hist-progress-fill" id="app-hist-pfill" style="width:<?php echo round( 1 / $total * 100 ); ?>%"></div>
            <?php foreach ( $pages as $i => $page ) : ?>
                <div class="app-hist-tick" style="left:<?php echo round( ($i / ($total-1)) * 100 ); ?>%" data-label="<?php echo esc_attr( $page['era'] ?? '' ); ?>"></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    const TOTAL_HISTORY_INFO   = <?php echo (int) $total; ?>;
</script>
