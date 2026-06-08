<?php
defined( 'ABSPATH' ) || exit;
$static_pages  = $args['static_pages']  ?? [];
$file_links    = $args['file_links']    ?? [];
$builder_pages = $args['builder_pages'] ?? [];
$forms         = $args['forms']         ?? [];

$atlas_builder_url = static function ( $builder_page ): string {
	$slug = sanitize_title( $builder_page->slug ?? '' );
	return $slug !== '' ? home_url( '/' . $slug . '/' ) : '';
};
$atlas_form_admin_url = static function ( $form ): string {
	$form_id = (int) ( $form->id ?? 0 );
	return $form_id > 0 ? admin_url( 'admin.php?page=ah-form-builder&form_id=' . $form_id . '&tab=build' ) : '';
};
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_TOOLS_AND_RESOURCES ); ?>">
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
            $block_count = is_string( $builder_page->blocks ?? null ) ? count( json_decode( $builder_page->blocks, true ) ?: [] ) : 0;
            $builder_url = $atlas_builder_url( $builder_page );
          ?>
            <li>
              <strong>
                <?php if ( $builder_url ) : ?>
                  <a href="<?php echo esc_url( $builder_url ); ?>"><?php echo esc_html( $builder_page->title ?? '' ); ?></a>
                <?php else : echo esc_html( $builder_page->title ?? '' ); endif; ?>
              </strong>
              <div class="atlas-muted">
                <?php if ( $builder_url ) : ?>
                  <a href="<?php echo esc_url( $builder_url ); ?>">/<?php echo esc_html( $builder_page->slug ?? '' ); ?>/</a>
                <?php else : ?>/<?php echo esc_html( $builder_page->slug ?? '' ); ?>/<?php endif; ?>
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
          <?php if ( $forms ) : foreach ( $forms as $form ) :
            $form_admin_url = $atlas_form_admin_url( $form );
          ?>
            <li>
              <strong>
                <?php if ( $form_admin_url ) : ?>
                  <a href="<?php echo esc_url( $form_admin_url ); ?>"><?php echo esc_html( $form->name ?? '' ); ?></a>
                <?php else : echo esc_html( $form->name ?? '' ); endif; ?>
              </strong>
              <div class="atlas-muted">
                <?php if ( $form_admin_url ) : ?>
                  <a href="<?php echo esc_url( $form_admin_url ); ?>">[ah_form id="<?php echo esc_html( (string) ( $form->id ?? 0 ) ); ?>"]</a>
                <?php else : ?>[ah_form id="<?php echo esc_html( (string) ( $form->id ?? 0 ) ); ?>"]<?php endif; ?>
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
