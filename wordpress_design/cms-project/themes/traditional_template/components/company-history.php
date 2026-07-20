<?php
/**
 * Company History Section
 * Drop into your theme or include via shortcode / template part
 */
defined( 'ABSPATH' ) || exit;

$_d     = nt_data( 'history' ) ?: [];
$tag    = $args['tag']   ?? $_d['tag']   ?? 'OUR STORY';
$title  = $args['title'] ?? $_d['title'] ?? 'The Journey';
$body   = $args['body']  ?? $_d['body']  ?? '';
$pages  = $args['pages'] ?? $_d['pages'] ?? [];

if ( empty( $pages ) ) return;
$total = count( $pages );
?>

<section id="nt-history" class="nt-history-section section">
    <div class="container wrapper">

        <?php
        get_template_part( 'components/parts/section-header', null, [
            'tag'           => $tag,
            'title'         => $title,
            'body'          => $body,
            'wrapper_class' => 'nt-history__header',
        ] ); ?>

    </div>
    <!-- Book / Page-turn carousel -->
    <div class="nt-book" id="nt-book" aria-label="Company history book" role="region">

        <!-- Shadow beneath book -->
        <div class="nt-book-shadow" aria-hidden="true"></div>

        <!-- Pages stack -->
        <div class="nt-book-pages" id="nt-book-pages">
            <?php
            foreach ( $pages as $i => $page ) :
                $page   = (array) $page;
                $is_first = $i === 0;
                $z      = $total - $i;
            ?>
            <article
                class="nt-book-page<?php echo $is_first ? ' is-active' : ''; ?> card"
                data-index="<?php echo esc_attr( $i ); ?>"
                style="z-index:<?php echo esc_attr( $z ); ?>;"
                aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
            >
                <!-- Left leaf (image side) -->
                <div class="nt-page-left">
                    <div class="nt-page-img-wrap">
                        <img
                            src="<?php echo esc_url( $page['image'] ?? '' ); ?>"
                            alt="<?php echo esc_attr( $page['image_alt'] ?? '' ); ?>"
                            class="nt-page-img"
                            loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
                        >
                        <?php if ( ! empty( $page['era'] ) ) : ?>
                        <div class="nt-page-era-badge"><?php echo esc_html( $page['era'] ); ?></div>
                        <?php endif; ?>
                    </div>
                    <!-- page number -->
                    <div class="nt-page-num-left"><?php echo sprintf( '%02d', $i + 1 ); ?></div>
                </div>

                <!-- Spine fold line -->
                <div class="nt-page-spine" aria-hidden="true"></div>

                <!-- Right leaf (content side) -->
                <div class="nt-page-right content">
                    <div class="nt-page-content">
                        <?php if ( ! empty( $page['tag'] ) ) : ?>
                        <div class="nt-section-tag nt-page-tag"><?php echo esc_html( $page['tag'] ); ?></div>
                        <?php endif; ?>

                        <h3 class="nt-page-title">
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

                        <p class="nt-page-body"><?php echo wp_kses_post( $page['body'] ?? '' ); ?></p>

                        <?php if ( ! empty( $page['countries'] ) ) : ?>
                        <div class="nt-page-countries">
                            <?php foreach ( (array) $page['countries'] as $country ) : ?>
                                <span class="nt-page-country"><?php echo esc_html( $country ); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $page['facts'] ) ) : ?>
                        <div class="nt-page-facts collection">
                            <?php foreach ( (array) $page['facts'] as $fact ) :
                                $fact = (array) $fact;
                            ?>
                                <div class="nt-page-fact item">
                                    <span class="nt-pf-icon" aria-hidden="true"><?php echo esc_html( $fact['icon'] ?? '' ); ?></span>
                                    <span><?php echo esc_html( $fact['text'] ?? '' ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="nt-page-num-right"><?php echo sprintf( '%02d', $i + 1 ); ?> / <?php echo esc_html( $total ); ?></div>
                </div>

                <!-- Page-turn clicker (right half triggers next) -->
                <button class="nt-page-turn-trigger nt-page-turn-next" aria-label="Next page" tabindex="<?php echo $is_first ? '0' : '-1'; ?>"></button>
            </article>
            <?php endforeach; ?>
        </div><!-- .nt-book-pages -->
    </div><!-- .nt-book -->

    <!-- Controls outside book -->
    <div class="nt-history-nav fade-up">
        <button class="nt-v-btn button" id="nt-hist-prev" aria-label="Previous page">←</button>

        <div class="nt-hist-dots" id="nt-hist-dots" role="tablist" aria-label="History pages">
            <?php foreach ( $pages as $i => $_ ) : ?>
                <button
                    class="nt-hdot<?php echo $i === 0 ? ' active' : ''; ?>"
                    role="tab"
                    aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                    aria-label="Chapter <?php echo $i + 1; ?>"
                    data-goto="<?php echo esc_attr( $i ); ?>"
                ></button>
            <?php endforeach; ?>
        </div>

        <button class="nt-v-btn button" id="nt-hist-next" aria-label="Next page">→</button>
    </div>

    <!-- Progress line (the "paper edge" line) -->
    <div class="nt-hist-progress-wrap" aria-hidden="true">
        <div class="nt-hist-progress-track">
            <div class="nt-hist-progress-fill" id="nt-hist-pfill" style="width:<?php echo round( 1 / $total * 100 ); ?>%"></div>
            <?php foreach ( $pages as $i => $page ) : ?>
                <div class="nt-hist-tick" style="left:<?php echo round( ($i / ($total-1)) * 100 ); ?>%" data-label="<?php echo esc_attr( $page['era'] ?? '' ); ?>"></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    const TOTAL_HISTORY_INFO   = <?php echo (int) $total; ?>;
</script>
