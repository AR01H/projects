<?php
// Query Testimonials CPT
$testimonials_query = new WP_Query([
    'post_type' => 'testimonial',
    'posts_per_page' => -1,
    'order' => 'ASC',
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'display_status',
            'value' => 'Active',
            'compare' => '='
        ],
        [
            'key' => 'display_status',
            'compare' => 'NOT EXISTS'
        ]
    ]
]);

// If no testimonials in DB, provide some fallbacks just like the static site
$testimonials = [];
if ($testimonials_query->have_posts()) {
    while ($testimonials_query->have_posts()) {
        $testimonials_query->the_post();
        $testimonials[] = [
            'quote' => wp_strip_all_tags(get_the_content()),
            'author' => get_the_title(),
            'subtitle' => get_post_meta(get_the_ID(), 'subtitle', true) ?: 'Happy Buyer',
            'image' => get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&h=800&fit=crop'
        ];
    }
    wp_reset_postdata();
} else {
    // Fallback static data
    $testimonials = [
        [
            'quote' => "Advaith Homes saved us £27,500 on our Richmond home and six months of stress. The team spotted issues we never would have seen and negotiated brilliantly.",
            'author' => "Sarah & Raj Mehta",
            'subtitle' => "First-Time Buyers in Richmond",
            'image' => "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&h=800&fit=crop"
        ],
        [
            'quote' => "The negotiation was flawless. We saved £40,000 off the asking price thanks to Advaith Homes' expert intervention.",
            'author' => "Emma & Tom Wright",
            'subtitle' => "Dream Home in Surrey",
            'image' => "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&h=800&fit=crop"
        ],
        [
            'quote' => "As an investor, I need ROI. Advaith Homes sourced three off-market properties for me this year, saving me over £80k in total.",
            'author' => "James Wilson",
            'subtitle' => "Property Investor",
            'image' => "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=600&h=800&fit=crop"
        ]
    ];
}
?>
<section class="section testimonial-section">
    <div class="container">
      <div style="text-align:center;max-width:640px;margin:0 auto 60px">
        <div class="eyebrow reveal">Success Stories</div>
        <h2 class="reveal reveal-delay-1">Hear From Our Happy Buyers</h2>
      </div>

      <div class="testimonial-container reveal">
        <div class="testimonial-slider" id="testimonialSlider">
            <?php foreach ($testimonials as $index => $t): ?>
              <div class="testimonial-slide <?php echo $index === 0 ? 'active' : ''; ?> <?php echo $index % 2 !== 0 ? 'testimonial-slide--alt' : ''; ?>">
                <div class="testimonial-slide__image">
                  <img src="<?php echo esc_url($t['image']); ?>" alt="<?php echo esc_attr($t['author']); ?>">
                </div>
                <div class="testimonial-slide__content">
                  <div class="stars" style="margin-bottom:20px">
                    <?php for($i=0; $i<5; $i++): ?>
                    <svg viewBox="0 0 24 24">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                    <?php endfor; ?>
                  </div>
                  <blockquote class="testimonial-quote">
                    "<?php echo esc_html($t['quote']); ?>"
                  </blockquote>
                  <div class="testimonial-author">
                    <div class="testimonial-author__info">
                      <h4><?php echo esc_html($t['author']); ?></h4>
                      <p><?php echo esc_html($t['subtitle']); ?></p>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
        </div>

        <div class="testimonial-nav-wrap">
          <div class="testimonial-nav" id="testimonialNav">
            <?php foreach ($testimonials as $index => $t): ?>
                <div class="testimonial-dot <?php echo $index === 0 ? 'active' : ''; ?>"></div>
            <?php endforeach; ?>
          </div>

          <div class="testimonial-controls">
            <button class="testimonial-arrow prev" id="testimonialPrev">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <button class="testimonial-arrow next" id="testimonialNext">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
