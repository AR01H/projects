<?php
/**
 * components/cards/expert_card.php
 * Props: $item { avatar, name, title, category, rating, reviews,
 *                description, location, tags[], url }
 */
defined( 'ABSPATH' ) || exit;

$_i    = isset( $item ) ? (array) $item : array();
$_av   = esc_html( isset( $_i['avatar'] )      ? (string) $_i['avatar']      : '👤' );
$_name = esc_html( isset( $_i['name'] )        ? (string) $_i['name']        : '' );
$_ttl  = esc_html( isset( $_i['title'] )       ? (string) $_i['title']       : '' );
$_cat  = esc_attr( sanitize_key( isset( $_i['category'] ) ? (string) $_i['category'] : 'all' ) );
$_rat  = isset( $_i['rating'] )  ? floatval( $_i['rating'] )  : 0;
$_rev  = isset( $_i['reviews'] ) ? intval( $_i['reviews'] )   : 0;
$_dsc  = esc_html( isset( $_i['description'] ) ? (string) $_i['description'] : '' );
$_loc  = esc_html( isset( $_i['location'] )    ? (string) $_i['location']    : '' );
$_tags = isset( $_i['tags'] ) ? (array) $_i['tags'] : array();
$_url  = esc_url( adn_link( isset( $_i['url'] ) ? (string) $_i['url'] : '#' ) );

$_stars = min( 5, max( 0, (int) round( $_rat ) ) );
?>
<div class="expert-card" data-cat="<?php echo $_cat; ?>">
	<div class="expert-card-header">
		<div class="expert-avatar" aria-hidden="true"><?php echo $_av; ?></div>
		<div class="expert-card-meta">
			<h3 class="expert-name"><?php echo $_name; ?></h3>
			<p class="expert-title"><?php echo $_ttl; ?></p>
			<div class="expert-rating">
				<span class="rating-stars" aria-hidden="true">
					<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
						<span class="<?php echo $s <= $_stars ? 'star-filled' : 'star-empty'; ?>">★</span>
					<?php endfor; ?>
				</span>
				<span class="rating-value"><?php echo esc_html( number_format( $_rat, 1 ) ); ?></span>
				<?php if ( $_rev > 0 ) : ?>
					<span class="rating-count">(<?php echo esc_html( $_rev ); ?> <?php esc_html_e( 'reviews', ADN_TEXT_DOMAIN ); ?>)</span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if ( '' !== $_dsc ) : ?>
		<p class="expert-desc"><?php echo $_dsc; ?></p>
	<?php endif; ?>

	<?php if ( '' !== $_loc ) : ?>
		<p class="expert-location">📍 <?php echo $_loc; ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $_tags ) ) : ?>
		<div class="expert-tags">
			<?php foreach ( $_tags as $_t ) : ?>
				<span class="expert-tag"><?php echo esc_html( (string) $_t ); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<a href="<?php echo $_url; ?>" class="btn btn-secondary expert-profile-btn">
		<?php esc_html_e( 'View Profile', ADN_TEXT_DOMAIN ); ?>
	</a>
</div>
