<?php
/**
 * Sidebar for topic-parent and topic-category pages.
 *
 * @var array $args {
 *   @type object     $pt             Parent term object.
 *   @type string     $pt_slug        Parent term slug.
 *   @type WP_Term[]  $pt_child_cats  Child WP_Term objects.
 *   @type string     $active_cat     Currently active category slug (empty on parent page).
 *   @type array      $site_stats     Market pulse stats.
 *   @type WP_Post[]  $popular_posts  Popular posts.
 * }
 */
defined( 'ABSPATH' ) || exit;

$pt            = $args['pt']            ?? null;
$pt_slug       = $args['pt_slug']       ?? '';
$pt_child_cats = $args['pt_child_cats'] ?? [];
$active_cat    = $args['active_cat']    ?? '';
$site_stats    = $args['site_stats']    ?? [];
$popular_posts = $args['popular_posts'] ?? [];
$pt_color      = ( $pt && ! empty( $pt->color ) ) ? $pt->color : 'var(--accent)';
?>

<?php if ( $pt && ! empty( $pt_child_cats ) ) : ?>
<!-- ── In this topic ──────────────────────────────────────────────────────── -->
<div class="nif-sb-card" aria-label="<?php echo esc_attr( TXT_CATEGORIES_IN_THIS_TOPIC ); ?>">
	<div class="nif-sb-card__header">
		<span class="nif-section-label--primary">
			<?php echo esc_html( sprintf( TXT_IN_S, $pt->name ) ); ?>
		</span>
	</div>

	<a href="<?php echo esc_url( home_url( '/' . $pt_slug . '/' ) ); ?>"
	   class="nif-sb-pt-row nif-sb-pt-row--all<?php echo ! $active_cat ? ' nif-sb-pt-row--all-active' : ''; ?>">
		<span class="nif-sb-pt-dot" style="background:<?php echo esc_attr( $pt_color ); ?>"></span>
		<span class="nif-sb-pt-name"><?php echo esc_html( TXT_ALL ); ?></span>
		<span class="nif-sb-pt-arrow"><?php echo ! $active_cat ? '▾' : '›'; ?></span>
	</a>

	<div class="nif-sb-pt-children nif-sb-pt-children--always" style="--ptc:<?php echo esc_attr( $pt_color ); ?>">
		<?php foreach ( $pt_child_cats as $fc ) :
			$is_active = ( $active_cat === $fc->slug );
		?>
		<a href="<?php echo esc_url( home_url( '/' . $pt_slug . '/' . $fc->slug . '/' ) ); ?>"
		   class="nif-sb-pt-child<?php echo $is_active ? ' nif-sb-pt-child--active' : ''; ?>">
			<?php echo esc_html( $fc->name ); ?>
		</a>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>

<!-- ── All topics link ───────────────────────────────────────────────────── -->
<div class="nif-sb-card nif-sb-card--topics-link">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nif-sb-all-topics-link">
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
		<?php echo esc_html( TXT_BROWSE_ALL_TOPICS ); ?>
	</a>
</div>

<!-- ── Popular Now ───────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/aside-items/nif-sb', 'popular-now', [
	'popular_posts' => $popular_posts,
] ); ?>

<!-- ── Market Pulse ──────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/aside-items/nif-sb', 'market-pulse', [
	'site_stats' => $site_stats,
] ); ?>

<!-- ── CTA card ──────────────────────────────────────────────────────────── -->
<div class="nif-sb-card nif-sb-card--cta">
	<div class="nif-sb-card__header">
		<span class="nif-section-label--primary"><?php echo esc_html( TXT_NEED_HELP ); ?></span>
	</div>
	<p class="nif-sb-cta-text"><?php echo esc_html( TXT_SPEAK_WITH_ONE_OF_OUR_PROPERTY_EXPERTS_FOR_PERSONA ); ?></p>
	<a href="<?php echo esc_url( home_url( defined( 'AH_LINK_CONTACT' ) ? AH_LINK_CONTACT : '/contact/' ) ); ?>"
	   class="btn btn-primary btn-sm" style="width:100%;justify-content:center">
		<?php echo esc_html( TXT_GET_IN_TOUCH ); ?>
	</a>
</div>
