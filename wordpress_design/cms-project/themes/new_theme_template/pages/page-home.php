<?php
/**
 * Template Name: Home
 *
 * Registered as 'home' in config/pages.php (front => true). Content comes
 * from admin/data/home.json via nt_data() - swap in admin options or DB data
 * later without touching the markup (the array shape stays the same).
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nt_home     = nt_data( 'home' );
$nt_hero     = (array) ( $nt_home['hero'] ?? array() );
$nt_features = (array) ( $nt_home['features'] ?? array() );
$nt_terms    = nt_terms_tree();

$nt_latest = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 3,
	'ignore_sticky_posts' => true,
) );
?>

<section class="nt-hero">
	<div class="nt-container">
		<?php if ( ! empty( $nt_hero['kicker'] ) ) : ?>
			<p class="nt-hero-kicker"><?php echo esc_html( $nt_hero['kicker'] ); ?></p>
		<?php endif; ?>
		<h1 class="nt-hero-title"><?php echo esc_html( $nt_hero['title'] ?? get_bloginfo( 'name' ) ); ?></h1>
		<?php if ( ! empty( $nt_hero['subtitle'] ) ) : ?>
			<p class="nt-hero-subtitle"><?php echo esc_html( $nt_hero['subtitle'] ); ?></p>
		<?php endif; ?>
		<div class="nt-hero-actions">
			<?php if ( ! empty( $nt_hero['cta_label'] ) ) : ?>
				<a class="nt-btn" href="<?php echo esc_url( nt_link( $nt_hero['cta_url'] ?? '#' ) ); ?>"><?php echo esc_html( $nt_hero['cta_label'] ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $nt_hero['secondary_label'] ) ) : ?>
				<a class="nt-btn nt-btn-ghost" href="<?php echo esc_url( nt_link( $nt_hero['secondary_url'] ?? '#' ) ); ?>"><?php echo esc_html( $nt_hero['secondary_label'] ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php if ( $nt_features ) : ?>
<section class="nt-section">
	<div class="nt-container">
		<div class="nt-grid nt-grid-3">
			<?php foreach ( $nt_features as $nt_feature ) : ?>
				<div class="nt-feature">
					<h3><?php echo esc_html( $nt_feature['title'] ?? '' ); ?></h3>
					<p><?php echo esc_html( $nt_feature['text'] ?? '' ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $nt_terms ) : ?>
<section class="nt-section nt-section-alt">
	<div class="nt-container">
		<h2 class="nt-section-title"><?php echo esc_html( nt_term_label( 'parent', true ) ); ?></h2>
		<div class="nt-grid nt-grid-3">
			<?php foreach ( $nt_terms as $nt_term ) : ?>
				<a class="nt-term-card" href="<?php echo esc_url( home_url( '/' . ( $nt_term['slug'] ?? '' ) . '/' ) ); ?>">
					<h3><?php echo esc_html( $nt_term['name'] ?? '' ); ?></h3>
					<?php if ( ! empty( $nt_term['description'] ) ) : ?>
						<p><?php echo esc_html( $nt_term['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $nt_term['children'] ) ) : ?>
						<span class="nt-term-count"><?php echo esc_html( count( (array) $nt_term['children'] ) . ' ' . nt_term_label( 'section', true ) ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $nt_latest->have_posts() ) : ?>
<section class="nt-section">
	<div class="nt-container">
		<h2 class="nt-section-title"><?php esc_html_e( 'Latest News', NT_TEXT_DOMAIN ); ?></h2>
		<div class="nt-grid nt-grid-3">
			<?php
			while ( $nt_latest->have_posts() ) {
				$nt_latest->the_post();
				nt_component( 'cards/post_card', array( 'post_id' => get_the_ID() ) );
			}
			wp_reset_postdata();
			?>
		</div>
		<p class="nt-center"><a class="nt-btn nt-btn-ghost" href="<?php echo esc_url( nt_page_url( 'news' ) ); ?>"><?php esc_html_e( 'View all news', NT_TEXT_DOMAIN ); ?></a></p>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
