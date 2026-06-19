<?php
/**
 * components/cards/expert_card.php
 *
 * Props: $item {
 *   slug?, photo_url?, avatar?, name, title, category,
 *   rating, reviews_count|reviews, description, location,
 *   phone?, email?, tags[], bullets[], url
 * }
 *
 * When photo_url is set → shows <img> circular photo.
 * Otherwise → shows initials circle (falls back to emoji avatar).
 * "View Profile" links to ?ah_expert=SLUG when slug is present.
 * "Contact" button opens the contact modal for this expert.
 */
defined( 'ABSPATH' ) || exit;

$_i        = isset( $item ) ? (array) $item : array();
$_slug     = isset( $_i['slug'] )      ? sanitize_key( (string) $_i['slug'] )     : '';
$_photo    = isset( $_i['photo_url'] ) ? (string) $_i['photo_url']                : '';
$_av       = esc_html( isset( $_i['avatar'] ) ? (string) $_i['avatar'] : '👤' );
$_name     = esc_html( isset( $_i['name'] )   ? (string) $_i['name']   : '' );
$_ttl      = esc_html( isset( $_i['title'] )  ? (string) $_i['title']  : '' );
$_cat      = esc_attr( sanitize_key( isset( $_i['category'] ) ? (string) $_i['category'] : 'all' ) );
$_rat      = isset( $_i['rating'] )        ? floatval( $_i['rating'] )        : 0;
$_rev      = isset( $_i['reviews_count'] ) ? intval( $_i['reviews_count'] )   : ( isset( $_i['reviews'] ) ? intval( $_i['reviews'] ) : 0 );
$_dsc      = esc_html( isset( $_i['description'] ) ? (string) $_i['description'] : '' );
$_loc      = esc_html( isset( $_i['location'] )    ? (string) $_i['location']    : '' );
$_phone    = isset( $_i['phone'] ) ? (string) $_i['phone'] : '';
$_email    = isset( $_i['email'] ) ? (string) $_i['email'] : '';
$_tags     = isset( $_i['tags'] )    ? (array) $_i['tags']    : array();
$_bullets  = isset( $_i['bullets'] ) ? (array) $_i['bullets'] : array();
// Use top 2 bullets as pills (falling back to tags if bullets empty).
$_pill_src = ! empty( $_bullets ) ? $_bullets : $_tags;
$_pills    = array_slice( $_pill_src, 0, 2 );

// URL: profile page if slug present, otherwise fallback.
if ( '' !== $_slug ) {
	$_url = esc_url( home_url( '/?ah_expert=' . rawurlencode( $_slug ) ) );
} else {
	$_url = esc_url( adn_link( isset( $_i['url'] ) ? (string) $_i['url'] : '#' ) );
}

// Initials for avatar fallback.
$_initials = '';
if ( '' === $_photo ) {
	$_np = explode( ' ', isset( $_i['name'] ) ? (string) $_i['name'] : '' );
	foreach ( $_np as $_np_part ) {
		if ( '' !== $_np_part ) { $_initials .= strtoupper( $_np_part[0] ); }
		if ( strlen( $_initials ) >= 2 ) { break; }
	}
}

$_stars = min( 5, max( 0, (int) round( $_rat ) ) );
?>
<div class="expert-card" data-cat="<?php echo $_cat; ?>">
	<div class="expert-card-header">

		<?php /* Avatar: photo or initials circle */ ?>
		<div class="expert-avatar" aria-hidden="true">
			<?php if ( '' !== $_photo ) : ?>
				<img class="expert-avatar-img"
					src="<?php echo esc_url( $_photo ); ?>"
					alt="<?php echo esc_attr( isset( $_i['name'] ) ? (string) $_i['name'] : '' ); ?>"
					loading="lazy">
			<?php elseif ( '' !== $_initials ) : ?>
				<span class="expert-initials"><?php echo esc_html( $_initials ); ?></span>
			<?php else : ?>
				<?php echo $_av; ?>
			<?php endif; ?>
		</div>

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
					<span class="rating-count">(<?php echo esc_html( (string) $_rev ); ?> <?php esc_html_e( 'reviews', ADN_TEXT_DOMAIN ); ?>)</span>
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

	<?php /* Contact info mini-icons */ ?>
	<?php if ( '' !== $_phone || '' !== $_email ) : ?>
		<div class="expert-contact-info">
			<?php if ( '' !== $_phone ) : ?>
				<span class="expert-contact-detail">📞 <?php echo esc_html( $_phone ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $_email ) : ?>
				<span class="expert-contact-detail">✉️ <?php echo esc_html( $_email ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php /* Bullet pills (top 2) */ ?>
	<?php if ( ! empty( $_pills ) ) : ?>
		<div class="expert-tags">
			<?php foreach ( $_pills as $_t ) : ?>
				<span class="expert-tag"><?php echo esc_html( (string) $_t ); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php /* Actions */ ?>
	<div class="expert-card-actions">
		<a href="<?php echo $_url; ?>" class="btn btn-secondary expert-profile-btn">
			<?php echo esc_html( SITE_BTN_VIEW_ALL ); ?>
		</a>
		<?php if ( '' !== $_slug ) : ?>
			<button type="button"
				class="btn btn-outline expert-contact-btn"
				data-slug="<?php echo esc_attr( $_slug ); ?>"
				data-name="<?php echo esc_attr( isset( $_i['name'] ) ? (string) $_i['name'] : '' ); ?>">
				<?php echo esc_html( SITE_BTN_CONTACT_US ); ?>
			</button>
		<?php endif; ?>
	</div>
</div>

<?php /* ── Inline contact modal (one per card, hidden until triggered) ── */ ?>
<?php if ( '' !== $_slug ) : ?>
<div class="expert-contact-modal" data-slug="<?php echo esc_attr( $_slug ); ?>" role="dialog"
	aria-label="<?php printf( esc_attr__( 'Contact %s', ADN_TEXT_DOMAIN ), esc_attr( isset( $_i['name'] ) ? (string) $_i['name'] : '' ) ); ?>"
	aria-hidden="true" hidden>
	<div class="ecm-backdrop"></div>
	<div class="ecm-panel">
		<button type="button" class="ecm-close" aria-label="<?php esc_attr_e( 'Close', ADN_TEXT_DOMAIN ); ?>">✕</button>
		<h2 class="ecm-title">
			<?php printf(
				/* translators: %s: expert name */
				esc_html__( 'Contact %s', ADN_TEXT_DOMAIN ),
				esc_html( isset( $_i['name'] ) ? (string) $_i['name'] : '' )
			); ?>
		</h2>
		<form class="expert-contact-form ecm-form" data-slug="<?php echo esc_attr( $_slug ); ?>" novalidate>
			<input type="hidden" name="expert_slug" value="<?php echo esc_attr( $_slug ); ?>">
			<div class="ecf-row">
				<label><?php echo esc_html( FORM_NAME_LABEL ); ?> <span class="ecf-req"><?php echo esc_html( FORM_REQUIRED_SUFFIX ); ?></span></label>
				<input type="text" name="sender_name" required
					placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_NAME ); ?>">
			</div>
			<div class="ecf-row">
				<label><?php echo esc_html( FORM_EMAIL_LABEL ); ?> <span class="ecf-req"><?php echo esc_html( FORM_REQUIRED_SUFFIX ); ?></span></label>
				<input type="email" name="sender_email" required
					placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_EMAIL ); ?>">
			</div>
			<div class="ecf-row">
				<label><?php echo esc_html( FORM_WHATSAPP_LABEL ); ?> <span class="ecf-opt"><?php echo esc_html( FORM_OPTIONAL_SUFFIX ); ?></span></label>
				<input type="tel" name="sender_phone"
					placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_WHATSAPP ); ?>">
			</div>
			<div class="ecf-row">
				<label><?php echo esc_html( FORM_MESSAGE_LABEL ); ?> <span class="ecf-req"><?php echo esc_html( FORM_REQUIRED_SUFFIX ); ?></span></label>
				<textarea name="message" rows="4" required
					placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_MESSAGE ); ?>"></textarea>
			</div>
			<div class="ecf-actions">
				<button type="submit" class="btn btn-primary ecf-submit">
					<?php echo esc_html( SITE_BTN_CONTACT_SUBMIT ); ?>
				</button>
			</div>
			<div class="ecf-feedback" aria-live="polite"></div>
		</form>
	</div>
</div>
<?php endif; ?>
