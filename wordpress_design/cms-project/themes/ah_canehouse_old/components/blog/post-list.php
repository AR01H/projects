<?php
defined( 'ABSPATH' ) || exit;
$journal_query = $args['journal_query'] ?? null;
$active_cat    = $args['active_cat']    ?? '';
$paged         = $args['paged']         ?? 1;
if ( ! $journal_query ) return;
?>
<div style="padding:1rem 0;background:#fff;">
  <div class="container" style="max-width:900px;">

    <?php if ( $journal_query->have_posts() ) : ?>
    <div style="display:grid;gap:2rem;">
      <?php while ( $journal_query->have_posts() ) :
        $journal_query->the_post();
        $cats = get_the_category();
      ?>
      <article style="border-left:4px solid #84cc16;padding-left:1.5rem;padding-bottom:2rem;border-bottom:1px solid #f3f4f6;transition:all 0.3s ease;" class="fade-up">
        <div style="display:flex;gap:0.75rem;align-items:center;margin-bottom:0.75rem;">
          <?php if ( $cats ) : ?>
          <span style="display:inline-block;font-size:11px;font-weight:700;text-transform:uppercase;color:#84cc16;letter-spacing:0.5px;background:#f0fdf4;padding:0.25rem 0.75rem;border-radius:4px;">
            <?php echo esc_html( $cats[0]->name ); ?>
          </span>
          <?php endif; ?>
        </div>
        <h2 style="font-size:1.3rem;margin:0 0 0.75rem;color:#1f2937;font-weight:700;line-height:1.3;">
          <a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;"><?php the_title(); ?></a>
        </h2>
        <p style="font-size:0.95rem;color:#6b7280;margin:0;line-height:1.6;">
          <?php echo esc_html( ch_excerpt( 20 ) ); ?>
        </p>
        <a href="<?php the_permalink(); ?>" style="display:inline-block;margin-top:1rem;font-size:13px;font-weight:600;color:#84cc16;text-decoration:none;transition:all 0.2s;"
           onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
          Read Full Article →
        </a>
      </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <?php if ( $journal_query->max_num_pages > 1 ) :
      $big = 999999999;
    ?>
    <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid #e5e7eb;text-align:center;">
      <?php echo paginate_links( [
        'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format'    => '?paged=%#%',
        'current'   => max( 1, $paged ),
        'total'     => $journal_query->max_num_pages,
        'type'      => 'list',
        'prev_text' => '← Previous',
        'next_text' => 'Next →',
      ] ); ?>
    </div>
    <?php endif; ?>

    <?php else : ?>
    <div style="text-align:center;padding:4rem 2rem;">
      <div style="font-size:3rem;margin-bottom:1rem;">📖</div>
      <h2 style="font-size:1.4rem;margin-bottom:0.75rem;color:#1f2937;">No articles yet</h2>
      <p style="color:#6b7280;margin-bottom:1.5rem;">
        <?php echo $active_cat ? 'Nothing in this category yet - browse all articles.' : "We're brewing something great - check back soon."; ?>
      </p>
      <?php if ( $active_cat ) : ?>
      <a href="<?php echo esc_url( get_permalink() ); ?>" style="display:inline-block;padding:0.75rem 1.5rem;background:#84cc16;color:#fff;text-decoration:none;font-weight:600;border-radius:6px;">
        View All Articles →
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</div>
