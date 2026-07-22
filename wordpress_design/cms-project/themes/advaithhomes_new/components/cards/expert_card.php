<?php
/**
 * components/cards/expert_card.php
 *
 * Props: $item {
 *   slug?, photo_url?, avatar?, name, title, category,
 *   rating, reviews_count|reviews, description, location,
 *   phone?, email?, tags[], bullets[], url
 * }
 */
defined( 'ABSPATH' ) || exit;

$_i           = isset( $item ) ? (array) $item : array();
$_locked      = ! empty( $_i['is_locked'] );
$_unlockable  = ! empty( $_i['unlockable'] ); // pre-rendered but hidden until JS unlock
$_slug     = isset( $_i['slug'] )      ? sanitize_key( (string) $_i['slug'] )     : '';
$_photo    = isset( $_i['photo_url'] ) ? (string) $_i['photo_url']                : '';
$_av       = esc_html( isset( $_i['avatar'] ) ? (string) $_i['avatar'] : '👤' );
$_name     = esc_html( isset( $_i['name'] )   ? (string) $_i['name']   : '' );
$_ttl_raw  = isset( $_i['title'] )  ? (string) $_i['title']  : '';
$_ttl_array = array_filter( array_map( 'trim', explode( ',', $_ttl_raw ) ) );
$_ttl      = esc_html( implode( ' • ', $_ttl_array ) );
$_cat_raw = ( isset( $_i['category'] ) ? (string) $_i['category'] : '' );
$_cat_array = array_filter( array_map( 'trim', explode( ',', $_cat_raw ) ) );
$_cat_attr_array = array();
$_cat_disp_array = array();
foreach ( $_cat_array as $c ) {
	if ( strtolower($c) !== 'all' ) {
		$_cat_attr_array[] = sanitize_key( $c );
		$_cat_disp_array[] = esc_html( ucwords( $c ) );
	}
}
$_cat_attr = empty( $_cat_attr_array ) ? 'all' : esc_attr( implode( ',', $_cat_attr_array ) );
$_rat      = isset( $_i['rating'] )        ? floatval( $_i['rating'] )      : 0;
$_rev      = isset( $_i['reviews_count'] ) ? intval( $_i['reviews_count'] ) : ( isset( $_i['reviews'] ) ? intval( $_i['reviews'] ) : 0 );
$_dsc      = wp_trim_words( isset( $_i['description'] ) ? (string) $_i['description'] : '', 25, '…' );
$_loc      = isset( $_i['location'] ) ? (string) $_i['location'] : '';
$_phone    = isset( $_i['phone'] )    ? (string) $_i['phone']    : '';
$_email    = isset( $_i['email'] )    ? (string) $_i['email']    : '';
$_tags     = isset( $_i['tags'] )    ? (array) $_i['tags']    : array();
$_bullets  = isset( $_i['bullets'] ) ? (array) $_i['bullets'] : array();

// Badge: category display name (admin sets it to credential like "RICS Qualified").
// Pills: first 2 bullets (fallback to tags).
$_pill_src = ! empty( $_bullets ) ? array_slice( $_bullets, 0, 2 ) : array_slice( $_tags, 0, 2 );

$_stars = min( 5, max( 0, (int) round( $_rat ) ) );

// Profile URL.
$_url = '' !== $_slug
	? esc_url( adn_expert_profile_url( $_slug ) )
	: esc_url( adn_link( isset( $_i['url'] ) ? (string) $_i['url'] : '#' ) );

// Initials fallback for avatar.
$_initials = '';
if ( '' === $_photo ) {
	foreach ( explode( ' ', isset( $_i['name'] ) ? (string) $_i['name'] : '' ) as $_np_part ) {
		if ( '' !== $_np_part ) { $_initials .= strtoupper( $_np_part[0] ); }
		if ( strlen( $_initials ) >= 2 ) { break; }
	}
}
?>
<div class="expert-card<?php echo $_locked ? ' expert-card--locked' : ''; ?>" data-cat="<?php echo $_cat_attr; ?>"<?php if ( $_unlockable ) : ?> data-unlockable="1" hidden<?php endif; ?><?php if ( '' !== $_url && '#' !== $_url ) : ?> data-profile-url="<?php echo $_url; ?>"<?php if ( ! $_locked ) : ?> role="link" tabindex="0"<?php endif; ?><?php endif; ?>>

	<?php if ( $_locked ) : ?>
	<?php /* ── LOCKED: show only the role/category + lock indicator ── */ ?>
	<div class="expert-lock-body">
		<div class="expert-lock-icon-wrap" aria-hidden="true">
			<i class="fa-solid fa-lock expert-lock-icon"></i>
		</div>
		<?php if ( ! empty( $_cat_disp_array ) ) : ?>
			<span class="expert-lock-role"><?php echo implode( ', ', $_cat_disp_array ); ?></span>
		<?php endif; ?>
		<span class="expert-lock-label"><?php esc_html_e( 'Profile Locked', ADN_TEXT_DOMAIN ); ?></span>
	</div>
	<?php else : ?>

	<?php /* ── Top row: avatar (left) + credential badge (right) ── */ ?>
	<div class="expert-card-top">
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
		<?php if ( ! empty( $_cat_disp_array ) ) : ?>
			<div class="expert-card-badges">
				<?php foreach ( $_cat_disp_array as $disp ) : ?>
					<span class="expert-card-badge">
						<?php echo adn_icon( 'shield' ); ?>
						<?php echo $disp; ?>
					</span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php /* ── Identity: name, title, rating ── */ ?>
	<div class="expert-card-identity">
		<h3 class="expert-name"><?php echo $_name; ?></h3>
		<?php if ( '' !== $_ttl ) : ?>
			<p class="expert-title"><?php echo $_ttl; ?></p>
		<?php endif; ?>
		<?php if ( $_rat > 0 ) : ?>
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
		<?php endif; ?>
	</div>

	<?php /* ── Bio with Read more link ── */ ?>
	<?php if ( '' !== $_dsc ) : ?>
		<div class="expert-bio-wrap">
			<p class="expert-desc"><?php echo esc_html( $_dsc ); ?></p>
			<?php if ( '' !== $_slug ) : ?>
				<a href="<?php echo $_url; ?>" class="expert-read-more">
					<?php esc_html_e( 'Read more', ADN_TEXT_DOMAIN ); ?>
					<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php /* ── Contact box ── */ ?>
	<?php if ( '' !== $_loc || '' !== $_phone || '' !== $_email ) : ?>
		<div class="expert-contact-box">
			<?php if ( '' !== $_loc ) : ?>
				<div class="ecb-row"><?php echo adn_icon( 'location' ); ?><span><?php echo esc_html( $_loc ); ?></span></div>
			<?php endif; ?>
			<?php if ( '' !== $_phone ) : ?>
				<div class="ecb-row"><?php echo adn_icon( 'phone' ); ?><span><?php echo esc_html( $_phone ); ?></span></div>
			<?php endif; ?>
			<?php if ( '' !== $_email ) : ?>
				<div class="ecb-row"><?php echo adn_icon( 'email' ); ?><span><?php echo esc_html( $_email ); ?></span></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php /* ── Footer: bullet pills + action buttons ── */ ?>
	<div class="expert-card-footer">

		<?php if ( ! empty( $_pill_src ) ) : ?>
			<div class="expert-tags">
				<?php foreach ( $_pill_src as $_idx => $_t ) : ?>
					<span class="expert-tag">
						<?php if ( 0 === (int) $_idx ) : ?>
							<i class="fa-solid fa-briefcase" aria-hidden="true"></i>
						<?php else : ?>
							<i class="fa-solid fa-award" aria-hidden="true"></i>
						<?php endif; ?>
						<?php echo esc_html( (string) $_t ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="expert-card-actions">
			<a href="<?php echo $_url; ?>" class="btn btn-primary expert-profile-btn">
				<?php esc_html_e( 'View Profile', ADN_TEXT_DOMAIN ); ?>
				<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
			</a>
			<?php if ( '' !== $_phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $_phone ) ); ?>"
					class="expert-call-btn">
					<i class="fa-solid fa-phone" aria-hidden="true"></i>
					<?php esc_html_e( 'Call', ADN_TEXT_DOMAIN ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div><!-- .expert-card-footer -->

	<?php endif; /* end else (not locked) */ ?>

</div>

<?php /* ── Inline contact modal (one per card, hidden until triggered) ── */ ?>
<?php if ( '' !== $_slug && ! $_locked ) : ?>
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
		<form class="expert-contact-form ecm-form" data-slug="<?php echo esc_attr( $_slug ); ?>">
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
