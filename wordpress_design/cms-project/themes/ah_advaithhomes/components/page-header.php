<?php
/**
 * Reusable page header / hero component.
 *
 * Usage:
 *   get_template_part( 'components/page-header', null, [
 *     'eyebrow'   => 'Latest Updates',      // small label above title
 *     'title'     => 'News &',              // main title (plain part)
 *     'title_em'  => 'Announcements',       // italic/accent part appended to title
 *     'desc'      => 'Paragraph text...',   // optional subtitle
 *     'badge'     => '12 items',            // optional pill badge after title
 *     'breadcrumb'=> [ ['Home','/''], ['News',''] ],  // optional breadcrumb
 *   ] );
 */
defined( 'ABSPATH' ) || exit;

$s        = $args ?? [];
$eyebrow  = $s['eyebrow']    ?? '';
$title    = $s['title']      ?? '';
$title_em = $s['title_em']   ?? '';
$desc     = $s['desc']       ?? '';
$badge    = $s['badge']      ?? '';
$crumbs   = $s['breadcrumb'] ?? [];
?>
<section class="ph" aria-label="<?php echo esc_attr( strip_tags( $title . " " . $title_em ) ); ?>">
  <div class="ph__bg" aria-hidden="true">
    <div class="ph__grid-lines"></div>
    <div class="ph__blob"></div>
  </div>

  <div class="container ph__inner">

    <?php if ( $crumbs ) : ?>
    <nav class="ph__breadcrumb" aria-label="<?php echo esc_attr( TXT_BREADCRUMB ); ?>">
      <?php foreach ( $crumbs as $i => $crumb ) :
        $label = $crumb[0] ?? '';
        $href  = $crumb[1] ?? '';
        $last  = ( $i === array_key_last( $crumbs ) );
      ?>
        <?php if ( ! $last && $href ) : ?>
          <a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $label ); ?></a>
          <span aria-hidden="true">›</span>
        <?php else : ?>
          <span aria-current="page"><?php echo esc_html( $label ); ?></span>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php if ( $eyebrow ) : ?>
    <span class="section__eyebrow ph__eyebrow" data-aos="fade-up"><?php echo esc_html( $eyebrow ); ?></span>
    <?php endif; ?>

    <h1 class="ph__title" data-aos="fade-up" data-aos-delay="60">
      <?php if ( $title )    echo wp_kses_post( $title ) . ( $title_em ? ' ' : '' ); ?>
      <?php if ( $title_em ) : ?><em><?php echo wp_kses_post( $title_em ); ?></em><?php endif; ?>
      <?php if ( $badge ) : ?>
        <span class="ph__badge"><?php echo esc_html( $badge ); ?></span>
      <?php endif; ?>
    </h1>

    <?php if ( $desc ) : ?>
    <p class="ph__desc" data-aos="fade-up" data-aos-delay="120">
      <?php echo wp_kses_post( $desc ); ?>
    </p>
    <?php endif; ?>

    <div class="ph__accent" aria-hidden="true"></div>

  </div>
</section>