<?php
/**
 * Template Name: Testing Page
 */

get_header();
?>

<main id="page-testing" style="padding: 2rem; max-width: 1200px; margin: 0 auto;">

    <h2 style="margin-bottom: 2rem;">1. Image Cards — 3 visible, autoplay</h2>
    <?php get_template_part( 'components/carousels/generic-carousel', null, [
        'type'       => 'image',
        'visible'    => 3,
        'visible_md' => 2,
        'visible_sm' => 1,
        'autoplay'   => 4500,
        'items'      => [
            [ 'image' => get_template_directory_uri() . '/assets/images/commercial-press.jpg', 'title' => 'Commercial Press', 'subtitle' => 'Stainless steel' ],
            [ 'image' => get_template_directory_uri() . '/assets/images/event-stall.jpg',      'title' => 'Event Stall',       'subtitle' => 'Mobile setup'    ],
            [ 'image' => get_template_directory_uri() . '/assets/images/live-press.jpg',       'title' => 'Live Press',        'subtitle' => 'Fresh to order'  ],
            [ 'image' => get_template_directory_uri() . '/assets/images/commercial-press.jpg', 'title' => 'Commercial Press', 'subtitle' => 'Stainless steel' ],
            [ 'image' => get_template_directory_uri() . '/assets/images/event-stall.jpg',      'title' => 'Event Stall',       'subtitle' => 'Mobile setup'    ],
            [ 'image' => get_template_directory_uri() . '/assets/images/live-press.jpg',       'title' => 'Live Press',        'subtitle' => 'Fresh to order'  ],
            [ 'image' => get_template_directory_uri() . '/assets/images/commercial-press.jpg', 'title' => 'Commercial Press', 'subtitle' => 'Stainless steel' ],
            [ 'image' => get_template_directory_uri() . '/assets/images/event-stall.jpg',      'title' => 'Event Stall',       'subtitle' => 'Mobile setup'    ],
            [ 'image' => get_template_directory_uri() . '/assets/images/live-press.jpg',       'title' => 'Live Press',        'subtitle' => 'Fresh to order'  ],
        ],
        'css_vars' => [
            '--cc-img-height'  => '260px',
            '--cc-title-color' => '#fff',
        ],
    ] ); ?>

    <h2 style="margin: 3rem 0 2rem;">2. Feature Cards — 2 visible, top border</h2>
    <?php get_template_part( 'components/carousels/generic-carousel', null, [
        'type'       => 'feature',
        'visible'    => 2,
        'visible_sm' => 1,
        'items'      => [
            [ 'icon' => '💒', 'title' => 'Weddings & Asian Celebrations', 'text' => 'Add a traditional and healthy touch to your special day. We serve fresh juice live during your reception, Mehndi night, Sangeet, or Baraat.', 'border_top_color' => '#4a8c2a' ],
            [ 'icon' => '🏛️', 'title' => 'Corporate Events',             'text' => 'Perfect for office parties, wellness days, and conferences. A healthy natural alternative to sugary sodas.',                                    'border_top_color' => '#c8a02a' ],
            [ 'icon' => '🎪', 'title' => 'Festivals & Markets',          'text' => 'High-volume outdoor events with our fully mobile setup. We handle the crowds so you can enjoy the day.',                                       'border_top_color' => '#2a7ac8' ],
        ],
        'css_vars' => [
            '--cc-card-bg'     => '#fff',
            '--cc-card-border' => '1px solid #e0e0e0',
            '--cc-icon-size'   => '3rem',
        ],
    ] ); ?>

    <h2 style="margin: 3rem 0 2rem;">3. Feature Cards + Checklist — left-aligned</h2>
    <?php get_template_part( 'components/carousels/generic-carousel', null, [
        'type'       => 'feature',
        'visible'    => 2,
        'visible_sm' => 1,
        'items'      => [
            [ 'icon' => '💒', 'title' => 'Weddings & Asian Celebrations', 'text' => 'Live juice at your reception, Mehndi night, Sangeet, or Baraat.', 'left' => true, 'border_top_color' => '#4a8c2a', 'checklist' => [ 'Reception Drinks', 'Mehndi & Sangeet Night', 'Post-Ceremony Refreshment', 'Baraat Welcome Drinks' ] ],
            [ 'icon' => '🏛️', 'title' => 'Corporate Events',             'text' => 'A healthy natural alternative to sugary sodas.',                  'left' => true, 'border_top_color' => '#c8a02a', 'checklist' => [ 'Office Wellness Days', 'Product Launches', 'Exhibitions & Trade Fairs', 'Team Away Days' ] ],
        ],
        'css_vars' => [
            '--cc-card-bg'     => '#f6faf2',
            '--cc-card-border' => '1px solid #d4e8c2',
            '--cc-check-color' => '#4a8c2a',
        ],
    ] ); ?>

    <h2 style="margin: 3rem 0 2rem;">4. Step Cards — How It Works</h2>
    <?php get_template_part( 'components/carousels/generic-carousel', null, [
        'type'       => 'step',
        'visible'    => 2,
        'visible_sm' => 1,
        'items'      => [
            [ 'step' => '1', 'icon' => '📞', 'title' => 'Enquire',        'text' => "Fill in the form or call us. We'll schedule a call to discuss the opportunity." ],
            [ 'step' => '2', 'icon' => '📋', 'title' => 'Discovery Call', 'text' => 'We walk you through the model, margins, and requirements honestly.'            ],
            [ 'step' => '3', 'icon' => '🤝', 'title' => 'Agreement',      'text' => 'Sign the franchise agreement and receive your full launch pack.'               ],
            [ 'step' => '4', 'icon' => '🚀', 'title' => 'Launch',         'text' => 'Go live with full hands-on support from our team on day one.'                  ],
        ],
        'css_vars' => [
            '--cc-badge-bg'    => '#4a8c2a',
            '--cc-badge-size'  => '3.25rem',
            '--cc-card-bg'     => '#f6faf2',
            '--cc-card-border' => '1px solid #d4e8c2',
        ],
    ] ); ?>

    <h2 style="margin: 3rem 0 2rem;">5. Selector / Blend Picker</h2>
    <?php get_template_part( 'components/carousels/generic-carousel', null, [
        'type'       => 'selector',
        'visible'    => 3,
        'visible_sm' => 2,
        'selector'   => true,
        'items'      => [
            [ 'icon' => '🌿', 'label' => 'Pure Cane', 'value' => 'pure-cane' ],
            [ 'icon' => '🍋', 'label' => 'Lemon',     'value' => 'lemon'     ],
            [ 'icon' => '🫚', 'label' => 'Ginger',    'value' => 'ginger'    ],
            [ 'icon' => '🍊', 'label' => 'Orange',    'value' => 'orange'    ],
        ],
        'css_vars' => [
            '--cc-card-bg'            => '#fff',
            '--cc-card-border'        => '1px solid #d4e8c2',
            '--cc-card-border-active' => '2px solid #4a8c2a',
            '--cc-card-bg-active'     => '#f6faf2',
            '--cc-card-cursor'        => 'pointer',
            '--cc-icon-size'          => '2rem',
        ],
    ] ); ?>

    <h2 style="margin: 3rem 0 2rem;">6. Ticker — Continuous Scroll</h2>
    <?php get_template_part( 'components/carousels/generic-carousel', null, [
        'type'         => 'feature',
        'ticker'       => true,
        'ticker_speed' => 500,
        'items'        => [
            [ 'icon' => '🌿', 'title' => 'Pure Cane',  'text' => 'Fresh pressed sugarcane juice.'   ],
            [ 'icon' => '🍋', 'title' => 'Lemon Blend', 'text' => 'Zesty and refreshing.'           ],
            [ 'icon' => '🫚', 'title' => 'Ginger Shot', 'text' => 'A warming kick of ginger.'       ],
            [ 'icon' => '🍊', 'title' => 'Orange Zest', 'text' => 'Citrus burst in every sip.'      ],
            [ 'icon' => '🌱', 'title' => 'Mint Fresh',  'text' => 'Cool and invigorating finish.'   ],
        ],
        'css_vars' => [
            '--cc-card-bg'     => '#fff',
            '--cc-card-border' => '1px solid #d4e8c2',
            '--cc-icon-size'   => '5rem',
        ],
    ] ); ?>

    <h2 style="margin: 3rem 0 2rem;">7. Vertical — 1 visible, autoplay</h2>
    <div style="max-width: 480px;">
        <?php get_template_part( 'components/carousels/generic-carousel', null, [
            'type'      => 'feature',
            'direction' => 'vertical',
            'visible'   => 1,
            'autoplay'  => 3000,
            'items'     => [
                [ 'icon' => '💒', 'title' => 'Weddings',  'text' => 'Live juice at your reception.',        'border_top_color' => '#4a8c2a' ],
                [ 'icon' => '🏛️', 'title' => 'Corporate', 'text' => 'Wellness days and conferences.',       'border_top_color' => '#c8a02a' ],
                [ 'icon' => '🎪', 'title' => 'Festivals',  'text' => 'High-volume outdoor event catering.', 'border_top_color' => '#2a7ac8' ],
            ],
            'css_vars' => [
                '--cc-card-bg'     => '#fff',
                '--cc-card-border' => '1px solid #e0e0e0',
                '--cc-icon-size'   => '2.5rem',
            ],
        ] ); ?>
    </div>

    <h2 style="margin: 3rem 0 2rem;">8. Vertical Ticker — Continuous Scroll Up</h2>
    <div style="max-width: 480px; height: 220px; overflow: hidden;">
        <?php get_template_part( 'components/carousels/generic-carousel', null, [
            'type'         => 'feature',
            'direction'    => 'vertical',
            'ticker'       => true,
            'ticker_speed' => 40,
            'items'        => [
                [ 'icon' => '💒', 'title' => 'Weddings',  'text' => 'Live juice at your reception.'        ],
                [ 'icon' => '🏛️', 'title' => 'Corporate', 'text' => 'Wellness days and conferences.'       ],
                [ 'icon' => '🎪', 'title' => 'Festivals',  'text' => 'High-volume outdoor event catering.' ],
                [ 'icon' => '🎓', 'title' => 'Graduation', 'text' => 'Celebrate with fresh juice.'         ],
            ],
            'css_vars' => [
                '--cc-card-bg'     => '#fff',
                '--cc-card-border' => '1px solid #e0e0e0',
                '--cc-icon-size'   => '2rem',
            ],
        ] ); ?>
    </div>

    <h2 style="margin: 3rem 0 2rem;">9. Floating Nav — arrows beside track</h2>
    <?php get_template_part( 'components/carousels/generic-carousel', null, [
        'type'         => 'feature',
        'visible'      => 3,
        'visible_sm'   => 1,
        'floating_nav' => true,
        'items'        => [
            [ 'icon' => '💒', 'title' => 'Weddings',  'text' => 'Live juice at your reception.',        'border_top_color' => '#4a8c2a' ],
            [ 'icon' => '🏛️', 'title' => 'Corporate', 'text' => 'Wellness days and conferences.',       'border_top_color' => '#c8a02a' ],
            [ 'icon' => '🎪', 'title' => 'Festivals',  'text' => 'High-volume outdoor event catering.', 'border_top_color' => '#2a7ac8' ],
            [ 'icon' => '🎓', 'title' => 'Graduation', 'text' => 'Celebrate with fresh juice.',         'border_top_color' => '#8a2ac8' ],
        ],
        'css_vars' => [
            '--cc-card-bg'      => '#fff',
            '--cc-card-border'  => '1px solid #e0e0e0',
            '--cc-arrow-offset' => '-1.5rem',
        ],
    ] ); ?>

    <h2 style="margin: 3rem 0 2rem;">10. Three-Column Grid — each column independent</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">

        <div>
            <h3 style="margin-bottom: 1rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: #666;">Image</h3>
            <?php get_template_part( 'components/carousels/generic-carousel', null, [
                'type'       => 'image',
                'visible'    => 1,
                'visible_md' => 1,
                'visible_sm' => 1,
                'autoplay'   => 3000,
                'items'      => [
                    [ 'image' => get_template_directory_uri() . '/assets/images/commercial-press.jpg', 'title' => 'Commercial Press', 'subtitle' => 'Stainless steel' ],
                    [ 'image' => get_template_directory_uri() . '/assets/images/event-stall.jpg',      'title' => 'Event Stall',       'subtitle' => 'Mobile setup'    ],
                ],
            ] ); ?>
        </div>

        <div>
            <h3 style="margin-bottom: 1rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: #666;">Feature</h3>
            <?php get_template_part( 'components/carousels/generic-carousel', null, [
                'type'       => 'feature',
                'visible'    => 1,
                'visible_md' => 1,
                'visible_sm' => 1,
                'items'      => [
                    [ 'icon' => '💒', 'title' => 'Weddings',  'text' => 'Live juice at your reception.'  ],
                    [ 'icon' => '🏛️', 'title' => 'Corporate', 'text' => 'Wellness days and conferences.' ],
                ],
                'css_vars' => [
                    '--cc-card-bg'     => '#fff',
                    '--cc-card-border' => '1px solid #e0e0e0',
                ],
            ] ); ?>
        </div>

        <div>
            <h3 style="margin-bottom: 1rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: #666;">Step</h3>
            <?php get_template_part( 'components/carousels/generic-carousel', null, [
                'type'       => 'step',
                'visible'    => 1,
                'visible_md' => 1,
                'visible_sm' => 1,
                'items'      => [
                    [ 'step' => '1', 'icon' => '📞', 'title' => 'Enquire',   'text' => 'Fill in the form or call us.'           ],
                    [ 'step' => '2', 'icon' => '📋', 'title' => 'Discovery', 'text' => 'We walk you through the model.'         ],
                    [ 'step' => '3', 'icon' => '🚀', 'title' => 'Launch',    'text' => 'Go live with full support on day one.'  ],
                ],
                'css_vars' => [
                    '--cc-badge-bg' => '#4a8c2a',
                    '--cc-card-bg'  => '#f6faf2',
                ],
            ] ); ?>
        </div>

    </div>

</main>

<?php get_footer(); ?>