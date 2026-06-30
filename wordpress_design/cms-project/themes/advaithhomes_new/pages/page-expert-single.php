<?php
/**
 * pages/page-expert-single.php - Individual expert profile page.
 *
 * Served by template_redirect when ?ah_expert=SLUG is present.
 * Renders the full WP page (header + footer) with the expert profile.
 *
 * RULE: No hardcoded content - only structure.
 */

defined( 'ABSPATH' ) || exit;

$_expert_slug = sanitize_key( wp_unslash( isset( $_GET['ah_expert'] ) ? $_GET['ah_expert'] : '' ) );

require_once ADN_THEME_DIR . '/intermediate/page_expert_single_logical.php';
$ctx = adn_expert_single_get_context( $_expert_slug );

if ( ! $ctx ) {
	status_header( 404 );
	$_404 = get_404_template();
	if ( $_404 ) {
		include $_404;
	} else {
		wp_die( esc_html__( 'Expert not found.', ADN_TEXT_DOMAIN ), '', array( 'response' => 404 ) );
	}
	return;
}

// ── SEO ─────────────────────────────────────────────────────────────────────
$_exp_img = '';
if ( ! empty( $ctx['avatar_url'] ) ) {
	$_exp_img = (string) $ctx['avatar_url'];
} elseif ( ! empty( $ctx['banner_image_url'] ) ) {
	$_exp_img = (string) $ctx['banner_image_url'];
}
$_exp_url = isset( $_GET['ah_expert'] ) ? home_url( add_query_arg( 'ah_expert', sanitize_key( $_GET['ah_expert'] ), defined( 'SITE_EXPERT_URL' ) ? SITE_EXPERT_URL : '/' ) ) : '';
adn_seo_register( array(
	'title'         => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
	'description'   => ! empty( $ctx['bio'] )               ? wp_strip_all_tags( (string) $ctx['bio'] ) : '',
	'canonical'     => $_exp_url,
	'image'         => $_exp_img,
	'breadcrumb'    => isset( $ctx['breadcrumb'] )          ? $ctx['breadcrumb']                   : array(),
	'schema_person' => array(
		'name'      => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
		'job_title' => isset( $ctx['hero']['description'] ) ? (string) $ctx['hero']['description'] : '',
		'bio'       => isset( $ctx['bio'] )                 ? (string) $ctx['bio']                 : '',
		'image'     => $_exp_img,
		'url'       => $_exp_url,
	),
) );

// Breadcrumb renders inside the hero - suppress from adn_page_open.
$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );

$_rating  = isset( $ctx['rating'] )        ? (float) $ctx['rating']        : 0.0;
$_reviews = isset( $ctx['reviews_count'] ) ? (int)   $ctx['reviews_count'] : 0;
$_stars   = min( 5, max( 0, (int) round( $_rating ) ) );

// Pass unlockNonce to JS before any early return so it's available for both
// the locked-screen unlock form and the unlocked profile's contact form.
wp_localize_script( 'adn-ask-expert-script', 'adnExpert', array(
	'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
	'nonce'       => isset( $ctx['contact_nonce'] ) ? $ctx['contact_nonce'] : '',
	'unlockNonce' => isset( $ctx['unlock_nonce'] )  ? $ctx['unlock_nonce']  : '',
	'hasLocked'   => ! empty( $ctx['is_locked'] ) ? 1 : 0,
) );

/* When the profile is locked, skip hero + stats entirely — show nothing identifying. */
if ( ! empty( $ctx['is_locked'] ) ) :
?>
<div class="container" style="padding-top:48px;padding-bottom:64px;">
	<div class="expert-profile-layout">

		<div class="expert-profile-locked-screen" id="expertProfileLockedScreen">
			<div class="epls-icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></div>
			<h2 class="epls-heading"><?php esc_html_e( 'This profile is restricted', ADN_TEXT_DOMAIN ); ?></h2>
			<p class="epls-desc"><?php esc_html_e( 'Enter the unlock password to view this expert\'s full profile.', ADN_TEXT_DOMAIN ); ?></p>
			<div class="epls-form">
				<input type="password" id="expertUnlockPw" class="eub-input epls-input"
					placeholder="<?php esc_attr_e( 'Enter password…', ADN_TEXT_DOMAIN ); ?>"
					autocomplete="current-password"
					aria-label="<?php esc_attr_e( 'Unlock password', ADN_TEXT_DOMAIN ); ?>">
				<button type="button" id="expertUnlockBtn" class="btn btn-primary eub-btn">
					<i class="fa-solid fa-unlock" aria-hidden="true"></i>
					<?php esc_html_e( 'Unlock Profile', ADN_TEXT_DOMAIN ); ?>
				</button>
			</div>
			<p class="eub-error" id="expertUnlockError" hidden></p>
			<a href="<?php echo esc_url( home_url( SITE_EXPERT_URL ) ); ?>" class="epls-back">
				<i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
				<?php esc_html_e( 'Back to all experts', ADN_TEXT_DOMAIN ); ?>
			</a>
		</div>

	</div>
</div>
<?php adn_page_close( $ctx ); ?>
<?php return; ?>
<?php endif; ?>

<?php /* ============================== HERO ============================== */ ?>
<?php $_has_cover = ! empty( $ctx['banner_image_url'] ); ?>
<section class="expert-profile-hero">
	<div class="container">
		<div class="expert-profile-hero-inner<?php echo $_has_cover ? ' has-cover-bg' : ''; ?>"<?php if ( $_has_cover ) : ?> style="background-image:url('<?php echo esc_url( $ctx['banner_image_url'] ); ?>')"<?php endif; ?>>

			<?php /* Photo */ ?>
			<div class="expert-profile-photo-wrap">
				<?php if ( ! empty( $ctx['photo_url'] ) ) : ?>
					<img class="expert-profile-photo"
						src="<?php echo esc_url( $ctx['photo_url'] ); ?>"
						alt="<?php echo esc_attr( $ctx['name'] ); ?>">
				<?php else : ?>
					<div class="expert-profile-initials" aria-hidden="true">
						<?php
						$_initials = '';
						$_parts    = explode( ' ', $ctx['name'] );
						foreach ( $_parts as $_p ) {
							if ( '' !== $_p ) { $_initials .= strtoupper( $_p[0] ); }
							if ( strlen( $_initials ) >= 2 ) { break; }
						}
						echo esc_html( $_initials ? $_initials : '👤' );
						?>
					</div>
				<?php endif; ?>
			</div>

			<?php /* Info */ ?>
			<div class="expert-profile-hero-info">

				<?php /* Breadcrumb */ ?>
				<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
					<nav class="expert-profile-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', ADN_TEXT_DOMAIN ); ?>">
						<?php foreach ( $ctx['breadcrumb'] as $_i => $_bc ) :
							$_bc = (array) $_bc;
						?>
							<?php if ( $_i > 0 ) : ?><span class="epbc-sep" aria-hidden="true">›</span><?php endif; ?>
							<?php if ( ! empty( $_bc['url'] ) ) : ?>
								<a href="<?php echo esc_url( $_bc['url'] ); ?>" class="epbc-link"><?php echo esc_html( $_bc['label'] ); ?></a>
							<?php else : ?>
								<span class="epbc-current"><?php echo esc_html( $_bc['label'] ); ?></span>
							<?php endif; ?>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

				<h1 class="expert-profile-name"><?php echo esc_html( $ctx['name'] ); ?></h1>
				<?php if ( ! empty( $ctx['title'] ) ) : ?>
					<p class="expert-profile-specialisation"><?php echo esc_html( $ctx['title'] ); ?></p>
				<?php endif; ?>

				<?php /* Rating */ ?>
				<?php if ( $_rating > 0 ) : ?>
					<div class="expert-profile-rating">
						<span class="rating-stars" aria-hidden="true">
							<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
								<span class="<?php echo $s <= $_stars ? 'star-filled' : 'star-empty'; ?>">★</span>
							<?php endfor; ?>
						</span>
						<span class="rating-value"><?php echo esc_html( number_format( $_rating, 1 ) ); ?></span>
						<?php if ( $_reviews > 0 ) : ?>
							<span class="rating-count">(<?php echo esc_html( (string) $_reviews ); ?> <?php esc_html_e( 'reviews', ADN_TEXT_DOMAIN ); ?>)</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php /* Category pill */ ?>
				<?php if ( ! empty( $ctx['category'] ) ) : ?>
					<span class="expert-profile-cat-pill"><?php echo esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $ctx['category'] ) ) ); ?></span>
				<?php endif; ?>

			</div>
		</div>
	</div>
</section>

<?php /* ============================== STATS MARQUEE ============================== */ ?>
<?php if ( ! empty( $ctx['banner_items'] ) ) : ?>
<div class="expert-stats-marquee">
	<?php get_template_part( 'components/marque_scroll/point_marque', null, array(
		'trust'     => $ctx['banner_items'],
		'is_string' => false,
		'is_icon'   => true,
	) ); ?>
</div>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT: CONTENT + SIDEBAR ============================== */ ?>
<div class="container">
	<div class="expert-profile-layout">

		<main class="expert-profile-main">

			<?php /* ── Bio ── */ ?>
			<?php if ( ! empty( $ctx['bio'] ) ) : ?>
				<section class="expert-profile-section expert-bio-section">
					<h2><?php esc_html_e( 'About', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php echo esc_html( $ctx['bio'] ); ?></p>
				</section>
			<?php endif; ?>

			<?php /* ── Bullets ── */ ?>
			<?php if ( ! empty( $ctx['bullets'] ) ) : ?>
				<section class="expert-profile-section">
					<h2><?php esc_html_e( 'Specialises in', ADN_TEXT_DOMAIN ); ?></h2>
					<ul class="expert-bullets-list">
						<?php foreach ( $ctx['bullets'] as $_bullet ) : ?>
							<li><?php echo esc_html( (string) $_bullet ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php /* ── Client work images ── */ ?>
			<?php if ( ! empty( $ctx['client_images'] ) ) : ?>
				<section class="expert-profile-section">
					<h2><?php esc_html_e( 'Client Work', ADN_TEXT_DOMAIN ); ?></h2>
					<div class="expert-clients-grid">
						<?php foreach ( $ctx['client_images'] as $_ci ) :
							$_ci = (array) $_ci;
							if ( empty( $_ci['url'] ) ) { continue; }
						?>
							<div class="client-img-wrap">
								<img src="<?php echo esc_url( $_ci['url'] ); ?>" alt="<?php echo esc_attr( isset( $_ci['caption'] ) ? (string) $_ci['caption'] : '' ); ?>" loading="lazy">
								<?php if ( ! empty( $_ci['caption'] ) ) : ?>
									<span class="client-img-caption"><?php echo esc_html( $_ci['caption'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php /* ── Mega HTML ── */ ?>
			<?php if ( ! empty( $ctx['mega_html'] ) ) : ?>
				<section class="expert-profile-section expert-mega-section">
					<?php echo do_shortcode( $ctx['mega_html'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- admin-written trusted HTML ?>
				</section>
			<?php endif; ?>

		</main>

		<?php /* ── Sidebar ── */ ?>
		<aside class="expert-profile-sidebar">

			<?php /* Specialisation pill */ ?>
			<?php if ( ! empty( $ctx['title'] ) ) : ?>
				<div class="expert-sb-box">
					<h3><?php esc_html_e( 'Specialisation', ADN_TEXT_DOMAIN ); ?></h3>
					<span class="expert-profile-cat-pill"><?php echo esc_html( $ctx['title'] ); ?></span>
				</div>
			<?php endif; ?>

			<?php /* CTA */ ?>
			<div class="expert-sb-box expert-need-help">
				<h3><?php echo esc_html( SITE_EXPERT_LABEL ); ?></h3>
				<p><?php esc_html_e( 'Browse all our vetted professionals and find the right specialist for your situation.', ADN_TEXT_DOMAIN ); ?></p>
				<a href="<?php echo esc_url( home_url( SITE_EXPERT_URL ) ); ?>" class="btn btn-primary expert-nh-btn">
					<?php esc_html_e( 'View All Experts', ADN_TEXT_DOMAIN ); ?>
				</a>
			</div>

		</aside>

	</div>
</div>

<?php adn_page_close( $ctx ); ?>

