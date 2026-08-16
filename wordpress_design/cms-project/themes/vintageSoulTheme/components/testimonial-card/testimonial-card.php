<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\Formatter;

$name   = isset( $name ) ? trim( (string) $name ) : '';
$role   = isset( $role ) ? (string) $role : '';
$quote  = isset( $quote ) ? trim( (string) $quote ) : '';
$rating = isset( $rating ) ? max( 0, min( 5, (int) $rating ) ) : 5;
$avatar = isset( $avatar ) ? (string) $avatar : '';

if ( '' === $name || '' === $quote ) {
	return;
}
?>
<figure class="testimonial-card">
	<blockquote class="testimonial-card__quote">
		<p><?php echo esc_html( $quote ); ?></p>
	</blockquote>
	<span class="testimonial-card__rating" aria-label="<?php echo esc_attr( sprintf(  __( '%d out of 5 stars', 'vintagesoul' ), $rating ) ); ?>">
		<?php echo esc_html( Formatter::star_rating( $rating ) ); ?>
	</span>
	<figcaption class="testimonial-card__author">
		<span class="testimonial-card__name">&mdash; <?php echo esc_html( $name ); ?></span>
		<?php if ( '' !== $role ) : ?>
			<span class="testimonial-card__role"><?php echo esc_html( $role ); ?></span>
		<?php endif; ?>
	</figcaption>
	<?php if ( '' !== $avatar ) : ?>
		<img class="testimonial-card__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="" loading="lazy">
	<?php else : ?>
		<span class="testimonial-card__avatar testimonial-card__avatar--fallback" aria-hidden="true"><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></span>
	<?php endif; ?>
</figure>
