<?php
/**
 * Post Gallery Component - Usage Examples
 *
 * The post-gallery component creates responsive image galleries/carousels
 * that automatically adapt between desktop (horizontal scroll) and mobile (carousel).
 *
 * Location: components/post-gallery.php
 *
 * Include in your post content using get_template_part() with $args array.
 */

// Example 1: Simple Product Gallery
get_template_part( 'components/post-gallery', null, [
    'images' => [
        [ 'src' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800' ],
        [ 'src' => 'https://images.unsplash.com/photo-1599599810694-b5ac4dd64fe2?w=800' ],
        [ 'src' => 'https://images.unsplash.com/photo-1624882277720-5c20f644b000?w=800' ],
    ]
] );

// Example 2: Gallery with Labels (for step-by-step processes)
get_template_part( 'components/post-gallery', null, [
    'title'  => 'How We Make Fresh Juice',
    'id'     => 'juice-process',
    'images' => [
        [
            'src'   => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
            'label' => 'Step 1: Selection',
            'desc'  => 'Choose the finest, ripest sugarcane stalks from our farms'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
            'label' => 'Step 2: Washing',
            'desc'  => 'Thoroughly clean each stalk to remove soil and debris'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1599599810694-b5ac4dd64fe2?w=800',
            'label' => 'Step 3: Pressing',
            'desc'  => 'Use traditional hydraulic press to extract pure juice'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1624882277720-5c20f644b000?w=800',
            'label' => 'Step 4: Serve Fresh',
            'desc'  => 'Chill and serve immediately to lock in freshness'
        ],
    ]
] );

// Example 3: Event Gallery
get_template_part( 'components/post-gallery', null, [
    'title'  => 'Our Event Gallery',
    'id'     => 'events-gallery-2024',
    'images' => [
        [
            'src'   => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800',
            'label' => 'Summer Festival 2024',
            'desc'  => 'Serving fresh juice to hundreds of happy guests'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=800',
            'label' => 'Wedding Reception',
            'desc'  => 'Refreshing drinks at a beautiful celebration'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1567521464027-f127ff144326?w=800',
            'label' => 'Corporate Event',
            'desc'  => 'Refreshment station for professional networking'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1519904981063-b0cf448d479e?w=800',
            'label' => 'Community Market',
            'desc'  => 'Local favorite stall at the weekend farmers market'
        ],
    ]
] );

// Example 4: Team/Behind-the-Scenes
get_template_part( 'components/post-gallery', null, [
    'title'  => 'Meet Our Team',
    'images' => [
        [
            'src'   => 'https://i.pravatar.cc/400?img=12',
            'label' => 'Ravi',
            'desc'  => 'Founder & Sugarcane Expert - 15 years experience'
        ],
        [
            'src'   => 'https://i.pravatar.cc/400?img=24',
            'label' => 'Priya',
            'desc'  => 'Operations Manager - Ensures consistency and quality'
        ],
        [
            'src'   => 'https://i.pravatar.cc/400?img=36',
            'label' => 'Anil',
            'desc'  => 'Equipment Specialist - Maintains our pressing machinery'
        ],
    ]
] );

// Example 5: Location/Franchise Showcase
get_template_part( 'components/post-gallery', null, [
    'title'  => 'Our Locations Across the UK',
    'id'     => 'franchise-map',
    'images' => [
        [
            'src'   => 'https://images.unsplash.com/photo-1519904981063-b0cf448d479e?w=800',
            'label' => 'Manchester Hub',
            'desc'  => 'Our flagship location in the city center'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800',
            'label' => 'London Branch',
            'desc'  => 'Serving thousands of customers daily'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800',
            'label' => 'Birmingham Franchise',
            'desc'  => 'Recent expansion - growing fast!'
        ],
    ]
] );

// Example 6: Using in PHP (typical WordPress post content)
// Place this in your post editor or in the_content filter:

// HTML version for Post Editor:
/*
<p>Below is our gallery showing the transformation of sugarcane:</p>

[Add post-gallery component here in WordPress - using get_template_part()
 if in a custom template, or rendering via the_content filter]

<p>As you can see, our process is meticulous and traditional...</p>
*/

// Example 7: Dynamically from post metadata
$gallery_images = get_post_meta( get_the_ID(), '_post_gallery_images', true );
if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
    get_template_part( 'components/post-gallery', null, [
        'title'  => 'Gallery',
        'id'     => 'post-' . get_the_ID() . '-gallery',
        'images' => $gallery_images
    ] );
}

// Example 8: Multiple galleries in one post
get_template_part( 'components/post-gallery', null, [
    'title'  => 'Before & After Transformation',
    'id'     => 'before-after-gallery',
    'images' => [
        [
            'src'   => 'https://images.unsplash.com/photo-1579089322326-4a54835487f3?w=800',
            'label' => 'Before: Raw Sugarcane',
            'desc'  => 'Fresh stalks ready for processing'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1599599810694-b5ac4dd64fe2?w=800',
            'label' => 'After: Refreshing Juice',
            'desc'  => 'Golden, fresh-pressed juice in your glass'
        ],
    ]
] );

// Example 9: Seasonal Gallery
get_template_part( 'components/post-gallery', null, [
    'title'  => 'Seasonal Sugarcane Varieties',
    'images' => [
        [
            'src'   => 'https://images.unsplash.com/photo-1624882277720-5c20f644b000?w=800',
            'label' => 'Spring Harvest',
            'desc'  => 'Fresh and light with early spring stalks'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
            'label' => 'Summer Peak',
            'desc'  => 'Peak season - sweetest and most nutrient-rich'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1567521464027-f127ff144326?w=800',
            'label' => 'Autumn Collection',
            'desc'  => 'Late harvest with concentrated sweetness'
        ],
    ]
] );

// Example 10: Full-featured gallery with all options
get_template_part( 'components/post-gallery', null, [
    'title'  => 'Premium Experience Gallery',
    'id'     => 'gallery-premium-showcase',
    'images' => [
        [
            'src'   => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&h=675&fit=crop',
            'label' => 'Premium Service',
            'desc'  => 'White-glove service for your special events'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1519904981063-b0cf448d479e?w=1200&h=675&fit=crop',
            'label' => 'Expert Team',
            'desc'  => 'Our experienced staff ensures excellence'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&h=675&fit=crop',
            'label' => 'Fresh Ingredients',
            'desc'  => 'Only the highest quality sugarcane used'
        ],
        [
            'src'   => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=675&fit=crop',
            'label' => 'Happy Customers',
            'desc'  => 'Hundreds of satisfied event hosts'
        ],
    ]
] );

/**
 * USAGE TIPS:
 *
 * 1. Image URLs
 *    - Use direct URLs to images (JPG, PNG, WebP)
 *    - Recommended size: 1200x675px for 16:9 ratio
 *    - Component will auto-fit to aspect-ratio: 16 / 10
 *
 * 2. Unique IDs
 *    - Always use unique 'id' values if you have multiple galleries
 *    - If not provided, a random ID is generated (fine for single gallery)
 *    - ID format: lowercase with hyphens, e.g., 'gallery-process-steps'
 *
 * 3. Captions
 *    - 'label' is shown in a lime-green color in the caption
 *    - 'desc' is shown in white below the label
 *    - Both are optional
 *
 * 4. Desktop vs Mobile
 *    - Desktop (>767px): Horizontal scroll, all images visible in one row
 *    - Mobile (≤767px): Single image carousel with dots and arrows
 *    - Swipe gestures work on both sizes
 *
 * 5. Styling
 *    - Green header bar optional (only if 'title' provided)
 *    - Dark background for images
 *    - Light green navigation bar below
 *    - Fully responsive
 *
 * 6. Accessibility
 *    - All images need alt text (auto-generated from label)
 *    - Navigation dots labeled as "Image 1", "Image 2", etc.
 *    - Keyboard navigable
 *
 * 7. Performance
 *    - Images use lazy loading
 *    - Use optimized image sizes (not full 4K)
 *    - CDN-served images recommended
 */
?>
