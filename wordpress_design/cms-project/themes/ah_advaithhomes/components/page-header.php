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
<section class="ph" aria-label="<?php echo esc_attr( strip_tags( $title . ' ' . $title_em ) ); ?>">
  <div class="ph__bg" aria-hidden="true">
    <div class="ph__grid-lines"></div>
    <div class="ph__blob"></div>
  </div>

  <div class="container ph__inner">

    <?php if ( $crumbs ) : ?>
    <nav class="ph__breadcrumb" aria-label="Breadcrumb">
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

<style>
/* ── Page Header Component ────────────────────────────────────────────── */
.ph {
  position: relative;
  overflow: hidden;
  padding-top:    calc(var(--nav-h, 66px) + var(--ticker-h, 36px));
  padding-bottom: clamp(46px);
  background: var(--client-color-400, #f7c62f);
}

/* Decorative background */
.ph__bg { position:absolute; inset:0; pointer-events:none; }
.ph__grid-lines {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(to right, rgba(0,0,0,.07) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0,0,0,.07) 1px, transparent 1px);
  background-size: 48px 48px;
  mask-image: radial-gradient(ellipse 90% 80% at 50% 100%, transparent 30%, black 100%);
}
.ph__blob {
  position: absolute;
  top: -80px; right: -100px;
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(255,255,255,.25) 0%, transparent 65%);
  border-radius: 50%;
}

/* Inner layout */
.ph__inner {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

/* Breadcrumb */
.ph__breadcrumb {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(0,0,0,.12);
  border-radius: 999px;
  padding: 5px 14px;
  font-size: .78rem;
  font-weight: 500;
  color: rgba(0,0,0,.65);
  margin-bottom: 22px;
  text-decoration: none;
}
.ph__breadcrumb a {
  color: rgba(0,0,0,.65);
  text-decoration: none;
  transition: color .15s;
}
.ph__breadcrumb a:hover { color: rgba(0,0,0,.9); }
.ph__breadcrumb span[aria-current] { color: rgba(0,0,0,.9); font-weight: 600; }
.ph__breadcrumb span[aria-hidden] { opacity: .5; }

/* Eyebrow */
.ph__eyebrow {
  color: rgba(0,0,0,.6) !important;
  margin-bottom: 10px;
}

/* Title */
.ph__title {
  font-family: var(--font-display);
  font-size: clamp(2rem, 4.5vw, 3.25rem);
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: -.03em;
  color: #1a1a1a;
  margin: 0 0 14px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
}

/* Badge */
.ph__badge {
  display: inline-flex;
  align-items: center;
  font-family: var(--font-body, sans-serif);
  font-size: .73rem;
  font-weight: 600;
  letter-spacing: .04em;
  color: #1a1a1a;
  background: rgba(0,0,0,.12);
  border: 1px solid rgba(0,0,0,.18);
  border-radius: 20px;
  padding: 3px 12px;
  translate: 0 -4px;
}

/* Desc */
.ph__desc {
  font-size: clamp(.9rem, 1.5vw, 1.05rem);
  color: rgba(0,0,0,.65);
  line-height: 1.75;
  margin: 0;
}

/* Accent line */
.ph__accent {
  width: 40px;
  height: 3px;
  background: rgba(0,0,0,.3);
  border-radius: 2px;
  margin-top: 24px;
}
</style>
