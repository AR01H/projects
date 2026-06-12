<?php
/**
 * components/sections/article_body.php — Article body: type-dispatched section renderer.
 *
 * Props: $sections  array of sections, each with a "type" key:
 *   paragraph  — { id, heading, text }
 *   steps_list — { id, heading, items[]{icon,title,desc,read_more,url}, tip{...} }
 *   costs_grid — { id, heading, text, items[]{icon,name,value}, cta{label,url} }
 *
 * Adding a new section type: add a new elseif block below; the JSON drives everything.
 *
 * NOTE: tip.text and any field carrying HTML must use wp_kses — not esc_html.
 */

defined( 'ABSPATH' ) || exit;

$sections = isset( $sections ) && is_array( $sections ) ? $sections : array();

foreach ( $sections as $section ) :
	$type = isset( $section['type'] ) ? (string) $section['type'] : '';
	$id   = isset( $section['id'] )   ? sanitize_html_class( $section['id'] ) : '';
	?>

	<?php if ( 'paragraph' === $type ) : ?>

		<section class="article-section"<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?>>
			<?php if ( ! empty( $section['heading'] ) ) : ?>
				<h2><?php echo esc_html( $section['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $section['text'] ) ) : ?>
				<p><?php echo esc_html( $section['text'] ); ?></p>
			<?php endif; ?>
		</section>

	<?php elseif ( 'steps_list' === $type ) : ?>

		<section class="article-section"<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?>>
			<?php if ( ! empty( $section['heading'] ) ) : ?>
				<h2><?php echo esc_html( $section['heading'] ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $section['items'] ) ) : ?>
				<div class="steps-list">
					<?php foreach ( (array) $section['items'] as $step ) : ?>
						<div class="step-item">
							<div class="step-item-icon"><?php echo adn_icon( isset( $step['icon'] ) ? $step['icon'] : '' ); ?></div>
							<div class="step-item-content">
								<div class="step-item-title"><?php echo esc_html( isset( $step['title'] ) ? $step['title'] : '' ); ?></div>
								<?php if ( ! empty( $step['desc'] ) ) : ?>
									<p class="step-item-desc"><?php echo esc_html( $step['desc'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $step['read_more'] ) && ! empty( $step['url'] ) ) : ?>
									<a href="<?php echo esc_url( adn_link( $step['url'] ) ); ?>" class="step-read-more">
										<?php echo esc_html( $step['read_more'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $section['tip'] ) ) : ?>
				<?php $tip = (array) $section['tip']; ?>
				<div class="journey-tip">
					<div class="journey-tip-left">
						<span class="journey-tip-icon"><?php echo adn_icon( isset( $tip['icon'] ) ? $tip['icon'] : '' ); ?></span>
						<span class="journey-tip-text"><?php
							echo wp_kses(
								isset( $tip['text'] ) ? $tip['text'] : '',
								array( 'strong' => array() )
							);
						?></span>
					</div>
					<?php if ( ! empty( $tip['link_label'] ) && ! empty( $tip['link_url'] ) ) : ?>
						<a href="<?php echo esc_url( adn_link( $tip['link_url'] ) ); ?>" class="btn btn-outline btn-sm">
							<?php echo esc_html( $tip['link_label'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</section>

	<?php elseif ( 'costs_grid' === $type ) : ?>

		<section class="article-section"<?php echo $id ? ' id="' . esc_attr( $id ) . '"' : ''; ?>>
			<?php if ( ! empty( $section['heading'] ) ) : ?>
				<h2><?php echo esc_html( $section['heading'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $section['text'] ) ) : ?>
				<p><?php echo esc_html( $section['text'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $section['items'] ) ) : ?>
				<div class="costs-grid">
					<?php foreach ( (array) $section['items'] as $cost ) : ?>
						<div class="cost-item">
							<div class="cost-icon"><?php echo adn_icon( isset( $cost['icon'] ) ? $cost['icon'] : '' ); ?></div>
							<div class="cost-name"><?php echo esc_html( isset( $cost['name'] ) ? $cost['name'] : '' ); ?></div>
							<div class="cost-value"><?php echo esc_html( isset( $cost['value'] ) ? $cost['value'] : '' ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $section['cta']['label'] ) ) : ?>
				<a href="<?php echo esc_url( adn_link( isset( $section['cta']['url'] ) ? $section['cta']['url'] : '' ) ); ?>" class="btn btn-outline btn-sm article-section-cta">
					<?php echo esc_html( $section['cta']['label'] ); ?>
				</a>
			<?php endif; ?>
		</section>

	<?php endif; ?>

<?php endforeach; ?>
