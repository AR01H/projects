<?php
/**
 * components/cards/page-sample.php — Component: Post Card
 *
 * PURPOSE: Self-contained, re-usable UI partial.
 *          Receives data via $context (extracted by npt_component()).
 *          NO queries, NO hooks.
 *
 * Usage (from any template):
 *   npt_component( 'cards/page-sample', [ 'post' => $post_data ] );
 *
 * Where $post_data is the array returned by npt_model_post().
 *
 * RULE: Components only render. They never fetch data.
 */

defined( 'ABSPATH' ) || exit;

// $post is injected by npt_component() via extract()
// Provide safe defaults so the component never crashes if called incorrectly.
$post      = $post ?? [];
$title     = $post['title']     ?? 'Untitled';
$excerpt   = $post['excerpt']   ?? '';
$url       = $post['url']       ?? '#';
$thumbnail = $post['thumbnail'] ?? null;
$date      = $post['date']      ?? '';
$author    = $post['author']    ?? '';
$categories = $post['categories'] ?? [];
?>

<article class="npt-card">

    <?php if ( $thumbnail ) : ?>
    <a href="<?php echo esc_url( $url ); ?>" class="npt-card__thumbnail" aria-hidden="true" tabindex="-1">
        <img src="<?php echo esc_url( $thumbnail ); ?>"
             alt="<?php echo esc_attr( $title ); ?>"
             loading="lazy">
    </a>
    <?php endif; ?>

    <div class="npt-card__body">

        <?php if ( $categories ) : ?>
        <div class="npt-card__cats">
            <?php foreach ( $categories as $cat ) : ?>
            <a href="<?php echo esc_url( $cat['url'] ); ?>" class="npt-card__cat">
                <?php echo esc_html( $cat['name'] ); ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <h3 class="npt-card__title">
            <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
        </h3>

        <?php if ( $excerpt ) : ?>
        <p class="npt-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
        <?php endif; ?>

        <footer class="npt-card__meta">
            <?php if ( $author ) : ?>
            <span class="npt-card__author"><?php echo esc_html( $author ); ?></span>
            <?php endif; ?>
            <?php if ( $date ) : ?>
            <time class="npt-card__date" datetime="<?php echo esc_attr( $date ); ?>">
                <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ); ?>
            </time>
            <?php endif; ?>
        </footer>

    </div><!-- .npt-card__body -->

</article><!-- .npt-card -->
