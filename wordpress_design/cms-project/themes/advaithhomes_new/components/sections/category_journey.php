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

	<?php if ( ! empty( $steps ) ) : ?>
	<script>
	(function(){
		var wrap = document.currentScript.parentElement.closest('.jny2-wrap');
		if (!wrap) { return; }
		if (!('IntersectionObserver' in window)) { wrap.classList.add('jny2--animate'); return; }
		var io = new IntersectionObserver(function(entries){
			entries.forEach(function(e){
				if (!e.isIntersecting) { return; }
				e.target.classList.add('jny2--animate');
				io.unobserve(e.target);
			});
		}, { threshold: 0.12 });
		io.observe(wrap);
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
