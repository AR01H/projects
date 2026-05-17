<?php
/**
 * Template Name: Content Atlas
 */
defined( 'ABSPATH' ) || exit;

get_header();

$settings       = ah_get_settings();
$home           = ah_get_home_settings();
$news_items     = ah_get_news_bar_items();
$trust_signals  = ah_get_trust_signals();
$process_steps  = ah_get_process_steps();
$site_stats     = ah_get_site_stats();
$services       = ah_get_services( 8 );
$team           = ah_get_team( 6 );
$reviews        = ah_get_reviews( 6 );
$faqs           = ah_get_faqs( '', 8 );
$properties     = ah_get_properties( 6 );
$static_pages   = ah_get_static_pages();
$file_links     = ah_get_file_links( 10 );
$builder_pages  = ah_get_builder_pages( 10 );
$forms          = ah_get_forms_summary();
$recent_posts   = get_posts( array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
$recent_pages   = get_posts( array(
	'post_type'      => 'page',
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'orderby'        => 'modified',
	'order'          => 'DESC',
) );

$atlas_service_url = static function ( $service ): string {
	$slug = sanitize_title( $service->slug ?? '' );
	if ( $slug !== '' ) {
		return home_url( '/services/' . $slug . '/' );
	}
	return home_url( '/services/' );
};

$atlas_builder_url = static function ( $builder_page ): string {
	$slug = sanitize_title( $builder_page->slug ?? '' );
	return $slug !== '' ? home_url( '/' . $slug . '/' ) : '';
};

$atlas_form_admin_url = static function ( $form ): string {
	$form_id = (int) ( $form->id ?? 0 );
	if ( $form_id <= 0 ) {
		return '';
	}
	return admin_url( 'admin.php?page=ah-form-builder&form_id=' . $form_id . '&tab=build' );
};

$summary_cards = array(
	array( 'label' => 'Published Posts', 'value' => (int) wp_count_posts( 'post' )->publish, 'note' => 'Blog, news, guides' ),
	array( 'label' => 'Services', 'value' => count( $services ), 'note' => 'Active service records' ),
	array( 'label' => 'FAQs', 'value' => count( $faqs ), 'note' => 'Questions ready to show' ),
	array( 'label' => 'Team Members', 'value' => count( $team ), 'note' => 'People content' ),
	array( 'label' => 'Reviews', 'value' => count( $reviews ), 'note' => 'Proof and testimonials' ),
	array( 'label' => 'Static Pages', 'value' => count( $static_pages ), 'note' => 'Theme HTML pages' ),
	array( 'label' => 'File Links', 'value' => count( $file_links ), 'note' => 'Downloads and resources' ),
	array( 'label' => 'Builder Pages', 'value' => count( $builder_pages ), 'note' => 'Custom drag-drop pages' ),
	array( 'label' => 'Forms', 'value' => count( $forms ), 'note' => 'Reusable form builder items' ),
);
?>

<style>
.atlas-hero {
	background:
		linear-gradient(135deg, rgba(255,255,255,.95), rgba(255,249,230,.9)),
		repeating-linear-gradient(90deg, rgba(183,121,31,.04) 0, rgba(183,121,31,.04) 1px, transparent 1px, transparent 56px);
	border-bottom: 1px solid var(--border);
}
.atlas-grid {
	display: grid;
	grid-template-columns: repeat(12, minmax(0, 1fr));
	gap: 24px;
}
.atlas-card {
	background: var(--surface);
	border: 1px solid var(--border);
	border-radius: 18px;
	box-shadow: var(--shadow-sm);
	padding: 22px;
}
.atlas-card h3,
.atlas-card h4 {
	margin: 0 0 12px;
}
.atlas-summary {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 16px;
}
.atlas-summary-card {
	background: linear-gradient(180deg, #fff, #fff8e8);
	border: 1px solid rgba(183,121,31,.18);
	border-radius: 16px;
	padding: 18px;
}
.atlas-summary-card strong {
	display: block;
	font-size: 2rem;
	font-family: var(--font-display);
	line-height: 1;
	margin-bottom: 8px;
	color: var(--slate-900);
}
.atlas-list {
	list-style: none;
	padding: 0;
	margin: 0;
	display: grid;
	gap: 12px;
}
.atlas-list li {
	padding: 12px 14px;
	border: 1px solid var(--border);
	border-radius: 12px;
	background: #fff;
}
.atlas-label {
	display: inline-block;
	padding: 5px 10px;
	border-radius: 999px;
	background: var(--client-color-50);
	color: var(--client-color-800);
	font-size: .74rem;
	font-weight: 600;
	letter-spacing: .04em;
	text-transform: uppercase;
}
.atlas-muted {
	color: var(--text-secondary);
	font-size: .92rem;
}
.atlas-link,
.atlas-muted a {
	color: var(--client-color-800);
	text-decoration: underline;
	text-underline-offset: 2px;
	word-break: break-word;
}
.atlas-link:hover,
.atlas-muted a:hover {
	color: var(--client-color-900);
}
.atlas-kv {
	display: grid;
	grid-template-columns: 160px 1fr;
	gap: 10px 16px;
	margin: 0;
}
.atlas-kv dt {
	color: var(--text-muted);
	font-weight: 600;
}
.atlas-kv dd {
	margin: 0;
	color: var(--text-primary);
}
.atlas-rich {
	line-height: 1.7;
	color: var(--text-secondary);
}
.atlas-two-col {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 24px;
}
.atlas-three-col {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 18px;
}
.atlas-mini-card {
	padding: 16px;
	border: 1px solid var(--border);
	border-radius: 14px;
	background: linear-gradient(180deg, #fff, #fffcf4);
}
.atlas-mini-card p:last-child,
.atlas-card p:last-child {
	margin-bottom: 0;
}
.atlas-table {
	width: 100%;
	border-collapse: collapse;
	font-size: .92rem;
}
.atlas-table th,
.atlas-table td {
	text-align: left;
	padding: 10px 12px;
	border-bottom: 1px solid var(--border);
	vertical-align: top;
}
.atlas-table th {
	color: var(--text-muted);
	font-size: .78rem;
	text-transform: uppercase;
	letter-spacing: .05em;
}
@media (max-width: 980px) {
	.atlas-two-col,
	.atlas-three-col {
		grid-template-columns: 1fr;
	}
	.atlas-kv {
		grid-template-columns: 1fr;
		gap: 6px;
	}
}
</style>

<section class="page-hero atlas-hero" aria-label="Content overview">
	<div class="container">
		<div class="page-hero__copy" style="max-width:820px" data-aos="fade-up">
			<span class="section__eyebrow">Content Atlas</span>
			<h1 class="page-hero__title">Everything Managed in Admin,<br><em>Readable on One Page</em></h1>
			<p class="page-hero__desc">
				This page brings together the important content controlled from the CMS admin: posts, services,
				static pages, downloads, forms, trust signals, and the core site settings that shape the live site.
			</p>
		</div>
	</div>
</section>

<section class="section section--sm" aria-label="Content summary">
	<div class="container">
		<div class="atlas-summary">
			<?php foreach ( $summary_cards as $card ) : ?>
				<div class="atlas-summary-card" data-aos="fade-up">
					<strong><?php echo esc_html( (string) $card['value'] ); ?></strong>
					<div><?php echo esc_html( $card['label'] ); ?></div>
					<div class="atlas-muted"><?php echo esc_html( $card['note'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-label="Core settings">
	<div class="container">
		<div class="section__header">
			<span class="section__eyebrow">Site Basics</span>
			<h2 class="section__title">The Core Information Behind the Site</h2>
		</div>
		<div class="atlas-two-col">
			<div class="atlas-card" data-aos="fade-up">
				<h3>Contact and Brand Settings</h3>
				<dl class="atlas-kv">
					<dt>Business Name</dt>
					<dd><?php echo esc_html( $settings['business_name'] ?? get_bloginfo( 'name' ) ); ?></dd>
					<dt>Phone</dt>
					<dd><?php echo esc_html( $settings['phone'] ?? 'Not set' ); ?></dd>
					<dt>WhatsApp</dt>
					<dd><?php echo esc_html( $settings['whatsapp'] ?? 'Not set' ); ?></dd>
					<dt>Email</dt>
					<dd><?php echo esc_html( $settings['email'] ?? get_option( 'admin_email' ) ); ?></dd>
					<dt>Address</dt>
					<dd><?php echo nl2br( esc_html( $settings['address'] ?? 'Not set' ) ); ?></dd>
				</dl>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>Hero and CTA Direction</h3>
				<dl class="atlas-kv">
					<dt>Hero Heading</dt>
					<dd><?php echo esc_html( $home['heading'] ?? 'Not set' ); ?></dd>
					<dt>Hero Subheading</dt>
					<dd><?php echo esc_html( $home['subheading'] ?? 'Not set' ); ?></dd>
					<dt>Primary CTA</dt>
					<dd><?php echo esc_html( $home['cta_primary_text'] ?? 'Not set' ); ?></dd>
					<dt>Primary URL</dt>
					<dd><?php echo esc_html( $home['cta_primary_url'] ?? 'Not set' ); ?></dd>
					<dt>Secondary CTA</dt>
					<dd><?php echo esc_html( $home['cta_secondary_text'] ?? 'Not set' ); ?></dd>
				</dl>
			</div>
		</div>
	</div>
</section>

<section class="section section--alt" aria-label="Live signals">
	<div class="container">
		<div class="section__header">
			<span class="section__eyebrow">Live Signals</span>
			<h2 class="section__title">What the Site Is Saying to Visitors Right Now</h2>
		</div>
		<div class="atlas-two-col">
			<div class="atlas-card" data-aos="fade-up">
				<h3>News Bar Items</h3>
				<ul class="atlas-list">
					<?php if ( $news_items ) : foreach ( $news_items as $item ) : ?>
						<li><?php echo esc_html( is_object( $item ) ? ( $item->text ?? '' ) : (string) $item ); ?></li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No news bar items found.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>Trust Signals</h3>
				<div class="atlas-three-col">
					<?php if ( $trust_signals ) : foreach ( $trust_signals as $signal ) :
						$signal = is_object( $signal ) ? (array) $signal : (array) $signal;
					?>
						<div class="atlas-mini-card">
							<div class="atlas-label"><?php echo esc_html( $signal['icon'] ?? 'Item' ); ?></div>
							<p><?php echo esc_html( $signal['text'] ?? '' ); ?></p>
						</div>
					<?php endforeach; else : ?>
						<p class="atlas-muted">No trust signals found.</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="section" aria-label="Published content">
	<div class="container">
		<div class="section__header">
			<span class="section__eyebrow">Posts and Pages</span>
			<h2 class="section__title">The Main Reading Content on the Site</h2>
		</div>
		<div class="atlas-two-col">
			<div class="atlas-card" data-aos="fade-up">
				<h3>Recent Blog Posts</h3>
				<ul class="atlas-list">
					<?php if ( $recent_posts ) : foreach ( $recent_posts as $post_item ) : ?>
						<li>
							<strong><a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>"><?php echo esc_html( get_the_title( $post_item ) ); ?></a></strong>
							<div class="atlas-muted"><?php echo esc_html( get_the_date( 'j M Y', $post_item ) ); ?></div>
							<div class="atlas-muted"><?php echo esc_html( wp_trim_words( $post_item->post_excerpt ?: wp_strip_all_tags( $post_item->post_content ), 18 ) ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No published posts yet.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>Recent WordPress Pages</h3>
				<ul class="atlas-list">
					<?php if ( $recent_pages ) : foreach ( $recent_pages as $page_item ) : ?>
						<li>
							<strong><a href="<?php echo esc_url( get_permalink( $page_item ) ); ?>"><?php echo esc_html( get_the_title( $page_item ) ); ?></a></strong>
							<div class="atlas-muted">Modified <?php echo esc_html( get_the_modified_date( 'j M Y', $page_item ) ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No published pages yet.</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<section class="section section--alt" aria-label="Structured content">
	<div class="container">
		<div class="section__header">
			<span class="section__eyebrow">Structured Content</span>
			<h2 class="section__title">Services, FAQs, People, and Social Proof</h2>
		</div>
		<div class="atlas-two-col">
			<div class="atlas-card" data-aos="fade-up">
				<h3>Services</h3>
				<ul class="atlas-list">
					<?php if ( $services ) : foreach ( $services as $service ) : ?>
						<li>
							<?php $service_url = $atlas_service_url( $service ); ?>
							<strong><a href="<?php echo esc_url( $service_url ); ?>"><?php echo esc_html( $service->title ?? '' ); ?></a></strong>
							<?php if ( ! empty( $service->slug ) ) : ?>
								<div class="atlas-muted"><a href="<?php echo esc_url( $service_url ); ?>">/<?php echo esc_html( trim( (string) $service->slug, '/' ) ); ?>/</a></div>
							<?php endif; ?>
							<div class="atlas-muted"><?php echo esc_html( $service->short_desc ?? $service->summary ?? '' ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No services found.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>FAQs</h3>
				<ul class="atlas-list">
					<?php if ( $faqs ) : foreach ( $faqs as $faq ) : ?>
						<li>
							<strong><?php echo esc_html( $faq->question ?? '' ); ?></strong>
							<div class="atlas-muted"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $faq->answer ?? '' ), 20 ) ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No FAQs found.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up">
				<h3>Team</h3>
				<ul class="atlas-list">
					<?php if ( $team ) : foreach ( $team as $member ) : ?>
						<li>
							<strong><?php echo esc_html( $member->name ?? '' ); ?></strong>
							<div class="atlas-muted"><?php echo esc_html( $member->role ?? '' ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No team members found.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>Reviews</h3>
				<ul class="atlas-list">
					<?php if ( $reviews ) : foreach ( $reviews as $review ) : ?>
						<li>
							<strong><?php echo esc_html( $review->reviewer_name ?? '' ); ?></strong>
							<div class="atlas-muted"><?php echo esc_html( $review->reviewer_title ?? '' ); ?></div>
							<div class="atlas-muted"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $review->review_text ?? '' ), 18 ) ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No reviews found.</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<section class="section" aria-label="Tools and resources">
	<div class="container">
		<div class="section__header">
			<span class="section__eyebrow">Tools and Resources</span>
			<h2 class="section__title">Static Pages, Downloads, Builder Pages, and Forms</h2>
		</div>
		<div class="atlas-two-col">
			<div class="atlas-card" data-aos="fade-up">
				<h3>Static Pages</h3>
				<table class="atlas-table">
					<thead><tr><th>Page</th><th>Slug</th><th>Status</th></tr></thead>
					<tbody>
						<?php if ( $static_pages ) : foreach ( $static_pages as $static_page ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $static_page['url'] ); ?>"><?php echo esc_html( $static_page['label'] ); ?></a></td>
								<td><a href="<?php echo esc_url( $static_page['url'] ); ?>"><code><?php echo esc_html( $static_page['slug'] ); ?></code></a></td>
								<td><?php echo ! empty( $static_page['has_wp_page'] ) ? 'Connected' : 'HTML only'; ?></td>
							</tr>
						<?php endforeach; else : ?>
							<tr><td colspan="3" class="atlas-muted">No static pages found.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>File Links</h3>
				<ul class="atlas-list">
					<?php if ( $file_links ) : foreach ( $file_links as $file ) : ?>
						<li>
							<strong><a href="<?php echo esc_url( $file->file_url ?? '#' ); ?>" target="_blank"><?php echo esc_html( $file->original_name ?? '' ); ?></a></strong>
							<div class="atlas-muted"><?php echo esc_html( $file->mime_type ?? 'file' ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No file links found.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up">
				<h3>Page Builder Pages</h3>
				<ul class="atlas-list">
					<?php if ( $builder_pages ) : foreach ( $builder_pages as $builder_page ) :
						$block_count = is_string( $builder_page->blocks ?? null ) ? count( json_decode( $builder_page->blocks, true ) ?: array() ) : 0;
						$builder_url = $atlas_builder_url( $builder_page );
					?>
						<li>
							<strong>
								<?php if ( $builder_url ) : ?>
									<a href="<?php echo esc_url( $builder_url ); ?>"><?php echo esc_html( $builder_page->title ?? '' ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $builder_page->title ?? '' ); ?>
								<?php endif; ?>
							</strong>
							<div class="atlas-muted">
								<?php if ( $builder_url ) : ?>
									<a href="<?php echo esc_url( $builder_url ); ?>">/<?php echo esc_html( $builder_page->slug ?? '' ); ?>/</a>
								<?php else : ?>
									/<?php echo esc_html( $builder_page->slug ?? '' ); ?>/
								<?php endif; ?>
							</div>
							<div class="atlas-muted"><?php echo esc_html( $block_count ); ?> blocks, <?php echo esc_html( $builder_page->status ?? 'draft' ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No builder pages found.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>Forms</h3>
				<ul class="atlas-list">
					<?php if ( $forms ) : foreach ( $forms as $form ) : ?>
						<li>
							<?php $form_admin_url = $atlas_form_admin_url( $form ); ?>
							<strong>
								<?php if ( $form_admin_url ) : ?>
									<a href="<?php echo esc_url( $form_admin_url ); ?>"><?php echo esc_html( $form->name ?? '' ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $form->name ?? '' ); ?>
								<?php endif; ?>
							</strong>
							<div class="atlas-muted">
								<?php if ( $form_admin_url ) : ?>
									<a href="<?php echo esc_url( $form_admin_url ); ?>">[ah_form id="<?php echo esc_html( (string) ( $form->id ?? 0 ) ); ?>"]</a>
								<?php else : ?>
									[ah_form id="<?php echo esc_html( (string) ( $form->id ?? 0 ) ); ?>"]
								<?php endif; ?>
							</div>
							<div class="atlas-muted"><?php echo esc_html( $form->status ?? 'active' ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No forms found.</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<section class="section section--alt" aria-label="Process and numbers">
	<div class="container">
		<div class="section__header">
			<span class="section__eyebrow">Operational Snapshot</span>
			<h2 class="section__title">Process Steps, Stats, and Property Showcase</h2>
		</div>
		<div class="atlas-two-col">
			<div class="atlas-card" data-aos="fade-up">
				<h3>Process Steps</h3>
				<ul class="atlas-list">
					<?php if ( $process_steps ) : foreach ( $process_steps as $step ) :
						$step = is_object( $step ) ? (array) $step : (array) $step;
					?>
						<li>
							<strong><?php echo esc_html( $step['num'] ?? '' ); ?> <?php echo esc_html( $step['title'] ?? '' ); ?></strong>
							<div class="atlas-muted"><?php echo esc_html( $step['desc'] ?? $step['description'] ?? '' ); ?></div>
						</li>
					<?php endforeach; else : ?>
						<li class="atlas-muted">No process steps found.</li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="atlas-card" data-aos="fade-up" data-delay="100">
				<h3>Showcase Data</h3>
				<div class="atlas-three-col">
					<?php if ( $site_stats ) : foreach ( $site_stats as $stat ) :
						$stat = is_object( $stat ) ? (array) $stat : (array) $stat;
					?>
						<div class="atlas-mini-card">
							<strong style="display:block;font-size:1.5rem;font-family:var(--font-display);margin-bottom:6px;"><?php echo esc_html( $stat['num'] ?? '' ); ?></strong>
							<div class="atlas-muted"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
						</div>
					<?php endforeach; endif; ?>
				</div>
				<?php if ( $properties ) : ?>
					<div style="margin-top:18px;">
						<h4>Featured Properties</h4>
						<ul class="atlas-list">
							<?php foreach ( array_slice( $properties, 0, 4 ) as $property ) :
								$property = is_object( $property ) ? (array) $property : (array) $property;
							?>
								<li>
									<strong><?php echo esc_html( trim( ( $property['emoji'] ?? '' ) . ' ' . ( $property['location'] ?? '' ) ) ); ?></strong>
									<div class="atlas-muted"><?php echo esc_html( trim( ( $property['price'] ?? '' ) . ' ' . ( $property['saved'] ?? '' ) ) ); ?></div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
