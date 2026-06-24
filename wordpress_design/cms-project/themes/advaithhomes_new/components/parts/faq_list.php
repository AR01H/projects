<?php
/**
 * components/parts/faq_list.php - Reusable accordion FAQ list.
 *
 * Props:
 *   $faqs   array  — each item: { question, answer, link_url, link_text }
 *                    items can be objects or associative arrays.
 *   $heading string — optional section heading (h2)
 */

defined( 'ABSPATH' ) || exit;

$faqs    = isset( $faqs )    && is_array( $faqs )    ? $faqs    : array();
$heading = isset( $heading ) && '' !== $heading       ? (string) $heading : '';

if ( empty( $faqs ) ) { return; }
?>
<div class="faq-list-wrap">
	<?php if ( '' !== $heading ) : ?>
	<h2 class="faq-section-heading"> <?php echo esc_html( $heading ); ?> <?= adn_icon('❓')?></h2>
	<?php endif; ?>

	<div class="faqs-list">
		<?php foreach ( $faqs as $faq ) :
			$_q  = is_object( $faq ) ? (string) ( $faq->question  ?? '' ) : (string) ( $faq['question']  ?? '' );
			$_a  = is_object( $faq ) ? (string) ( $faq->answer    ?? '' ) : (string) ( $faq['answer']    ?? '' );
			$_lu = is_object( $faq ) ? (string) ( $faq->link_url  ?? '' ) : (string) ( $faq['link_url']  ?? '' );
			$_lt = is_object( $faq ) ? (string) ( $faq->link_text ?? '' ) : (string) ( $faq['link_text'] ?? '' );
			if ( '' === trim( $_q ) ) { continue; }
		?>
		<details class="faq-item">
			<summary class="faq-q">
				<span class="faq-q-text"><?php echo esc_html( $_q ); ?></span>
			</summary>
			<div class="faq-a">
				<?php if ( '' !== trim( $_a ) ) : ?>
					<div class="faq-a-body"><?php echo wp_kses_post( wpautop( wp_trim_words( $_a, 500, '' ) ) ); ?></div>
				<?php endif; ?>
				<?php if ( '' !== trim( $_lu ) ) : ?>
					<p class="faq-link">
						<a href="<?php echo esc_url( adn_link( $_lu ) ); ?>"><?php echo esc_html( $_lt ?: $_lu ); ?></a>
					</p>
				<?php endif; ?>
			</div>
		</details>
		<?php endforeach; ?>
	</div>
</div>
