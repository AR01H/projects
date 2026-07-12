<?php
/**
 * components/sections/category_journey.php
 * Numbered-steps timeline - horizontal stepper with connecting rail.
 *
 * Props: $journey { heading, steps[], tip { icon, text, link_label, link_url } }
 */

defined( 'ABSPATH' ) || exit;

$journey = isset( $journey ) && is_array( $journey ) ? $journey : array();
$steps   = isset( $journey['steps'] ) ? (array) $journey['steps'] : array();
$tip     = isset( $journey['tip'] )   ? (array) $journey['tip']   : array();

if ( empty( $steps ) ) { return; }

$_total = count( $steps );
?>

<div class="jny2-wrap">

	<?php if ( ! empty( $journey['heading'] ) ) : ?>
	<div class="jny2-header">
		<h2 class="jny2-heading"><?php echo esc_html( $journey['heading'] ); ?></h2>
	</div>
	<?php endif; ?>

	<div class="jny2-steps" data-steps="<?php echo esc_attr( $_total ); ?>">

		<?php foreach ( $steps as $i => $step ) :
			$step  = (array) $step;
			$num   = isset( $step['num'] )   ? (string) $step['num']   : (string) ( $i + 1 );
			$label = isset( $step['label'] ) ? (string) $step['label'] : '';
			$desc  = isset( $step['desc'] )  ? (string) $step['desc']  : '';
			$icon  = isset( $step['icon'] )  ? trim( (string) $step['icon'] ) : '';

			$_icon_html = ( '' !== $icon ) ? adn_icon( $icon ) : '';
			$_num_pad = str_pad( $num, 2, '0', STR_PAD_LEFT );
			$_is_last = ( $i === $_total - 1 );
		?>
		<div class="jny2-step<?php echo $_is_last ? ' jny2-step--last' : ''; ?>">

			<div class="jny2-step__top">
				<div class="jny2-step__circle" aria-hidden="true">
					<?php if ( $_icon_html ) : ?>
						<?php echo $_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<span class="jny2-step__num"><?php echo esc_html( $_num_pad ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="jny2-step__body">
				<?php if ( $icon ) : ?>
					
					<div class="jny2-step__num-chip" aria-label="<?php echo esc_attr( sprintf( __( 'Step %s', 'adn' ), $num ) ); ?>"><?php print adn_icon( $icon ); ?></div>
				<?php else : ?>
					<div class="jny2-step__num-chip" aria-label="<?php echo esc_attr( sprintf( __( 'Step %s', 'adn' ), $num ) ); ?>"><?php echo adn_icon( $_num_pad ); ?></div>
				<?php endif; ?>
				<?php if ( $label ) : ?>
				<strong class="jny2-step__label"><?php echo esc_html( $label ); ?></strong>
				<?php endif; ?>
				<?php if ( $desc ) : ?>
				<p class="jny2-step__desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
			</div>

		</div>

		<?php if ( ! $_is_last ) : ?>
		<span class="jny2-arrow" aria-hidden="true">&#8250;</span>
		<?php endif; ?>

		<?php endforeach; ?>


	</div>

	<?php if ( count( $steps ) > 2 ) : ?>
	<div class="jny2-nav">
		<button class="jny2-nav__btn jny2-nav__btn--prev" aria-label="<?php esc_attr_e( 'Previous', 'adn' ); ?>" disabled>&#8592;</button>
		<button class="jny2-nav__btn jny2-nav__btn--next" aria-label="<?php esc_attr_e( 'Next', 'adn' ); ?>">&#8594;</button>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $steps ) ) : ?>
	<script>
	(function(){
		var wrap  = document.currentScript.parentElement.closest('.jny2-wrap');
		if (!wrap) { return; }

		/* Scroll-reveal */
		if (!('IntersectionObserver' in window)) { wrap.classList.add('jny2--animate'); }
		else {
			var io = new IntersectionObserver(function(entries){
				entries.forEach(function(e){
					if (!e.isIntersecting) { return; }
					e.target.classList.add('jny2--animate');
					io.unobserve(e.target);
				});
			}, { threshold: 0.12 });
			io.observe(wrap);
		}

		/* Arrow navigation */
		var track = wrap.querySelector('.jny2-steps');
		var prev  = wrap.querySelector('.jny2-nav__btn--prev');
		var next  = wrap.querySelector('.jny2-nav__btn--next');
		if (!track || !prev || !next) { return; }

		function cardWidth() {
			var c = track.querySelector('.jny2-step');
			var g = parseInt(getComputedStyle(track).gap) || 16;
			return c ? c.offsetWidth + g : 220;
		}
		function sync() {
			prev.disabled = track.scrollLeft <= 2;
			next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
		}
		prev.addEventListener('click', function(){ track.scrollBy({ left: -cardWidth(), behavior: 'smooth' }); });
		next.addEventListener('click', function(){ track.scrollBy({ left:  cardWidth(), behavior: 'smooth' }); });
		track.addEventListener('scroll', sync, { passive: true });
		window.addEventListener('resize', sync);
		sync();
	}());
	</script>
	<?php endif; ?>

	<?php if ( $tip ) : ?>
	<div class="jny2-tip">
		<span class="jny2-tip__icon" aria-hidden="true">
			<?php echo adn_icon( isset( $tip['icon'] ) ? $tip['icon'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
		<span class="jny2-tip__text"><?php
			echo wp_kses(
				isset( $tip['text'] ) ? $tip['text'] : '',
				array( 'strong' => array() )
			);
		?></span>
		<?php if ( ! empty( $tip['link_label'] ) && ! empty( $tip['link_url'] ) ) : ?>
			<a href="<?php echo esc_url( adn_link( $tip['link_url'] ) ); ?>" class="jny2-tip__cta">
				<?php echo esc_html( $tip['link_label'] ); ?> &rarr;
			</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>

</div>
