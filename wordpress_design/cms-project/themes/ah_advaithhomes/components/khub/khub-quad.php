<?php
/**
 * Knowledge Hub four-column band:
 *   Popular Guides (links) · Journey Timeline (steps) · Calculators (links) · Red Flags (links)
 *
 * Args: [ 'guides' => {...}, 'timeline' => {...}, 'calculators' => {...}, 'red_flags' => {...} ]
 * Each links-block: [ 'title','sub','links'=>[ ['icon','label','url'] ], 'foot'=>['label','url'] ].
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$guides = $args['guides']      ?? array();
$tl     = $args['timeline']    ?? array();
$calc   = $args['calculators'] ?? array();
$flags  = $args['red_flags']   ?? array();

/* Reusable list-card renderer. */
$render_list = static function ( array $block, string $modifier = '' ): void {
	if ( empty( $block['title'] ) ) {
		return;
	}
	?>
	<div class="khub-card khub-list-card <?php echo esc_attr( $modifier ); ?>">
		<h3 class="khub-card__title"><?php echo esc_html( $block['title'] ); ?></h3>
		<?php if ( ! empty( $block['sub'] ) ) : ?>
			<p class="khub-card__sub"><?php echo esc_html( $block['sub'] ); ?></p>
		<?php endif; ?>
		<ul class="khub-linklist" role="list">
			<?php foreach ( (array) ( $block['links'] ?? array() ) as $l ) : ?>
				<li>
					<a class="khub-linklist__item" href="<?php echo esc_url( $l['url'] ?? '#' ); ?>">
						<span class="khub-linklist__ico"><?php echo ah_khub_icon( $l['icon'] ?? 'doc', 16 ); ?></span>
						<span class="khub-linklist__label"><?php echo esc_html( $l['label'] ?? '' ); ?></span>
						<span class="khub-linklist__chev"><?php echo ah_khub_icon( 'arrow', 14 ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( ! empty( $block['foot']['label'] ) ) : ?>
			<a class="khub-card__foot" href="<?php echo esc_url( $block['foot']['url'] ?? '#' ); ?>">
				<?php echo esc_html( $block['foot']['label'] ); ?> <?php echo ah_khub_icon( 'arrow', 14 ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
};
?>
<section class="khub-quad" aria-label="Guides, timeline, calculators and red flags">
  <div class="container khub-quad__grid">

    <?php $render_list( $guides ); ?>

    <!-- Timeline card -->
    <?php if ( ! empty( $tl['title'] ) ) : ?>
    <div class="khub-card khub-tl-card">
      <h3 class="khub-card__title"><?php echo esc_html( $tl['title'] ); ?></h3>
      <?php if ( ! empty( $tl['sub'] ) ) : ?>
        <p class="khub-card__sub"><?php echo esc_html( $tl['sub'] ); ?></p>
      <?php endif; ?>
      <ol class="khub-tl" role="list">
        <?php foreach ( (array) ( $tl['steps'] ?? array() ) as $s ) : ?>
          <li class="khub-tl__step">
            <span class="khub-tl__dot"><?php echo ah_khub_icon( $s['icon'] ?? 'home', 16 ); ?></span>
            <strong class="khub-tl__title"><?php echo esc_html( $s['title'] ?? '' ); ?></strong>
            <small class="khub-tl__desc"><?php echo esc_html( $s['desc'] ?? '' ); ?></small>
          </li>
        <?php endforeach; ?>
      </ol>
      <?php if ( ! empty( $tl['foot']['label'] ) ) : ?>
        <a class="khub-card__foot" href="<?php echo esc_url( $tl['foot']['url'] ?? '#' ); ?>">
          <?php echo esc_html( $tl['foot']['label'] ); ?> <?php echo ah_khub_icon( 'arrow', 14 ); ?>
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php $render_list( $calc ); ?>
    <?php $render_list( $flags, 'khub-list-card--flags' ); ?>

  </div>
</section>
