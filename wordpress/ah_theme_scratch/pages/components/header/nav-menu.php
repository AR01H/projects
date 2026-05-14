<?php 

$asset_logo = mytheme_image('logo.png');
$company_name = 'Advaith ';
$company_name_part2 = 'Homes';
$mobileno = 'tel:+447747223762';

// Main Navigation Array (Prepared for database/WP Walker integration)
$nav_items = [
    [
        'title'     => 'Home',
        'url'       => home_url('/'),
        'data_page' => 'home',
        'class'     => 'nav__item-p1',
    ],
    [
        'title'     => 'Services',
        'url'       => home_url('/services'),
        'data_page' => 'services',
        'class'     => 'nav__item-p2',
    ],
    [
        'title' => 'Buying Guides',
        'class' => 'nav__item-p3',
        'dropdown' => [
            [
                'title'       => 'Property Research Report',
                'url'         => home_url('/property-research'),
                'icon'        => '🔍',
                'desc'        => 'Deep analysis before you buy',
                'title_style' => 'width: max-content;',
            ],
            [
                'title'       => 'Legal Search Packs',
                'url'         => home_url('/legal-search'),
                'icon'        => '⚖️',
                'desc'        => 'What\'s hidden in the paperwork',
                'title_style' => 'width: max-content;',
            ],
            [
                'title' => 'Buyer\'s Guide',
                'url'   => home_url('/buyers-guide'),
                'icon'  => '📋',
                'desc'  => 'Complete buying process',
            ],
            [
                'title' => 'Deposit Guide',
                'url'   => home_url('/deposit-guide'),
                'icon'  => '💰',
                'desc'  => 'How much you really need',
            ],
            [
                'title' => 'Mortgage Guide',
                'url'   => home_url('/mortgage-guide'),
                'icon'  => '🏦',
                'desc'  => 'Navigate rates & lenders',
            ],
            [
                'title' => 'Moving Guide',
                'url'   => home_url('/moving-guide'),
                'icon'  => '🚛',
                'desc'  => 'Stress-free moving day',
            ],
            [
                'title'       => 'Price Calculator',
                'url'         => home_url('/price-calculator'),
                'icon'        => '🧮',
                'desc'        => 'Dynamic cost estimations',
                'item_style'  => 'background: var(--bg-alt); border-radius: 8px;',
                'icon_style'  => 'background: var(--accent); color: white;',
                'title_style' => 'color:var(--accent);',
                'font_weight' => '700',
            ],
        ]
    ],
    [
        'title'   => 'Blog',
        'class'   => 'nav__item-p4',
        'is_mega' => true,
        'dropdown' => [
            ['title' => 'Latest Market News', 'url' => home_url('/news'), 'icon' => '🚨', 'desc' => 'Breaking updates & trends'],
            ['title' => 'London Hotspots', 'url' => home_url('/blog/london-hotspots-2026'), 'icon' => '📍', 'desc' => 'Top 5 growth areas'],
            ['title' => 'Negotiation Secrets', 'url' => home_url('/blog/negotiation-secrets'), 'icon' => '💰', 'desc' => 'Save £20k+ easily'],
            ['title' => 'Property Rules', 'url' => home_url('/blog/property-rules-2026'), 'icon' => '⚖️', 'desc' => 'Law changes for 2026'],
            ['title' => 'Mortgage Secrets', 'url' => home_url('/blog/mortgage-secrets-2026'), 'icon' => '🏦', 'desc' => 'Unlock the best rates'],
            ['title' => 'The Midlands Boom', 'url' => home_url('/blog/midlands-boom-2026'), 'icon' => '🏗️', 'desc' => 'Manchester is smart buy'],
            ['title' => 'First-Time Buyer', 'url' => home_url('/blog/first-time-buyer-2026'), 'icon' => '🔑', 'desc' => 'Your step-by-step key'],
            ['title' => 'Hidden Costs', 'url' => home_url('/blog/hidden-costs-buying'), 'icon' => '💸', 'desc' => 'The extra £10k you need'],
            ['title' => 'New Build vs Period', 'url' => home_url('/blog/new-build-vs-period'), 'icon' => '🏘️', 'desc' => 'Which wins in 2026?'],
            ['title' => 'Shared Ownership', 'url' => home_url('/blog/shared-ownership-reality'), 'icon' => '🤝', 'desc' => 'Trap or ticket?'],
            ['title' => 'Digital Legals', 'url' => home_url('/blog/digital-legals-2026'), 'icon' => '📱', 'desc' => 'Paperwork-free buying'],
        ]
    ],
    [
        'title'     => 'About Us',
        'url'       => home_url('/about'),
        'data_page' => 'aboutus',
        'class'     => 'nav__item-p5',
    ],
    [
        'title'     => 'Client Stories',
        'url'       => home_url('/previous-clients'),
        'data_page' => 'clientstories',
        'class'     => 'nav__item-p6',
    ],
    [
        'title'     => 'Contact',
        'url'       => home_url('/contact'),
        'data_page' => 'contact',
        'class'     => 'nav__item-p7',
    ],
];

// "More" Dropdown Items
$more_items = [
    ['title' => 'Home', 'url' => home_url('/'), 'icon' => '🏠'],
    ['title' => 'Services', 'url' => home_url('/services'), 'icon' => '✨'],
    ['title' => 'Buying Guides', 'url' => '#', 'icon' => '📋'],
    ['title' => 'Blog', 'url' => '#', 'icon' => '✍️'],
    ['title' => 'About Us', 'url' => home_url('/about'), 'icon' => '👥'],
    ['title' => 'Client Stories', 'url' => home_url('/previous-clients'), 'icon' => '⭐'],
    ['title' => 'Contact', 'url' => home_url('/contact'), 'icon' => '📬'],
];

?>
<nav class="nav" id="mainNav">
  <div class="container">
    <div class="nav__inner">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="nav__logo">
        <img src="<?php echo esc_url($asset_logo); ?>" style="height:40px;">
        <span><?php echo esc_html($company_name); ?> <em
            style="font-style:italic;font-family:var(--font-accent)"><?php echo esc_html($company_name_part2); ?></em></span>
      </a>

      <ul class="nav__menu">
        <?php foreach ($nav_items as $item): ?>
          <?php if (empty($item['dropdown'])): ?>
            <li class="<?php echo esc_attr($item['class']); ?>">
              <a href="<?php echo esc_url($item['url']); ?>" class="nav__link" data-page="<?php echo esc_attr($item['data_page']); ?>">
                <?php echo esc_html($item['title']); ?>
              </a>
            </li>
          <?php else: ?>
            <li class="nav__dropdown <?php echo esc_attr($item['class']); ?>">
              <button class="nav__link nav__dropdown-toggle">
                <?php echo esc_html($item['title']); ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M6 9l6 6 6-6"></path>
                </svg>
              </button>

              <?php if (!empty($item['is_mega'])): ?>
                <div class="nav__dropdown-menu nav__dropdown-menu--mega"
                  style="width: 90vw; left: 50%; padding: 10px; overflow-x: auto; overflow-y: hidden;">
                  <div style="display: grid; grid-template-rows: repeat(2, 1fr); grid-auto-flow: column; gap: 20px; min-width: max-content; padding-bottom: 5px;">
                    <?php foreach ($item['dropdown'] as $subitem): ?>
                      <a href="<?php echo esc_url($subitem['url']); ?>" class="nav__dropdown-item"
                        style="width: 240px; flex-shrink: 0; display: flex; align-items: flex-start; gap: 12px; border-right: 1px solid var(--slate-100); padding-right: 15px;">
                        <div class="nav__dropdown-item-icon" style="margin-bottom: 0; flex-shrink: 0;"><?php echo $subitem['icon']; ?></div>
                        <div>
                          <div style="font-weight:700;color:var(--slate-800);font-size:.82rem;margin-bottom:2px">
                            <?php echo esc_html($subitem['title']); ?>
                          </div>
                          <div style="font-size:.75rem;color:var(--text-muted);line-height:1.4">
                            <?php echo esc_html($subitem['desc']); ?>
                          </div>
                        </div>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php else: ?>
                <div class="nav__dropdown-menu">
                  <?php foreach ($item['dropdown'] as $subitem): ?>
                    <?php 
                      $fw = !empty($subitem['font_weight']) ? $subitem['font_weight'] : '600'; 
                      $default_color = (isset($subitem['title_style']) && strpos($subitem['title_style'], 'color') !== false) ? '' : 'color:var(--slate-800);';
                      $ts = !empty($subitem['title_style']) ? $subitem['title_style'] : '';
                    ?>
                    <a href="<?php echo esc_url($subitem['url']); ?>" class="nav__dropdown-item"
                      <?php echo !empty($subitem['item_style']) ? 'style="' . esc_attr($subitem['item_style']) . '"' : ''; ?>>
                      <div class="nav__dropdown-item-icon" <?php echo !empty($subitem['icon_style']) ? 'style="' . esc_attr($subitem['icon_style']) . '"' : ''; ?>>
                        <?php echo $subitem['icon']; ?>
                      </div>
                      <div>
                        <div style="font-weight:<?php echo esc_attr($fw); ?>;font-size:.85rem;<?php echo esc_attr($default_color . $ts); ?>">
                          <?php echo esc_html($subitem['title']); ?>
                        </div>
                        <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">
                          <?php echo esc_html($subitem['desc']); ?>
                        </div>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>

        <!-- "More" Dropdown (Auto-Handles Overflow) -->
        <li class="nav__dropdown nav__item-more-trigger">
          <button class="nav__link nav__dropdown-toggle" style="color: var(--accent); font-weight: 700;">
            More
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M6 9l6 6 6-6"></path>
            </svg>
          </button>
          <div class="nav__dropdown-menu" style="min-width: 220px;">
            <?php foreach ($more_items as $index => $m_item): ?>
              <a href="<?php echo esc_url($m_item['url']); ?>" class="nav__dropdown-item nav__more-item-<?php echo ($index + 1); ?>" style="padding: 12px 16px;">
                <span style="margin-right: 10px;"><?php echo $m_item['icon']; ?></span> <?php echo esc_html($m_item['title']); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
      </ul>

      <div class="nav__actions">
        <a href="<?php echo esc_url($mobileno); ?>" class="button">📞 Call</a>
        <a href="<?php echo esc_url(home_url('/free-consultation')); ?>" class="button">Free Consultation</a>
        <button class="nav__hamburger" id="hamburger" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>
</nav>

<div class="nav__mobile" id="mobileNav">
  <?php foreach ($nav_items as $nav): ?>
    <?php if (!empty($nav['dropdown'])): ?>
      <details class="nav__mobile-details">
        <summary class="nav__mobile-summary">
            <?php 
                $icon = '';
                foreach ($more_items as $m) {
                    if ($m['title'] === $nav['title']) $icon = $m['icon'] . ' ';
                }
                echo $icon . esc_html($nav['title']); 
            ?> 
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
        </summary>
        <div class="nav__mobile-sub-menu">
          <?php foreach ($nav['dropdown'] as $item): ?>
            <?php 
              $hl_style = (!empty($item['item_style'])) ? 'style="color: var(--accent); font-weight: 700;"' : '';
            ?>
            <a href="<?php echo esc_url($item['url']); ?>" class="nav__mobile-link" <?php echo $hl_style; ?>><?php echo $item['icon'] . ' ' . esc_html($item['title']); ?></a>
          <?php endforeach; ?>
          <?php if ($nav['title'] === 'Buying Guides'): ?>
             <a href="<?php echo esc_url(home_url('/free-consultation')); ?>" class="nav__mobile-link">☎️ Free Consultation Guide</a>
          <?php endif; ?>
        </div>
      </details>
    <?php else: ?>
      <?php 
          $icon = '';
          foreach ($more_items as $m) {
              if ($m['title'] === $nav['title']) $icon = $m['icon'] . ' ';
          }
      ?>
      <a href="<?php echo esc_url($nav['url']); ?>" class="nav__mobile-link"><?php echo $icon . esc_html($nav['title']); ?></a>
    <?php endif; ?>
  <?php endforeach; ?>
  <div style="padding:16px;">
    <a href="<?php echo esc_url(home_url('/free-consultation')); ?>" class="btn btn-primary" style="width:100%;justify-content:center">Book Free Consultation</a>
  </div>
</div>

<style>
.nav__mobile-details { border-bottom: 1px solid rgba(0,0,0,0.05); }
.nav__mobile-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  font-weight: 600;
  color: var(--slate-700);
  cursor: pointer;
  list-style: none;
}
.nav__mobile-summary::-webkit-details-marker { display: none; }
.nav__mobile-summary svg { transition: transform 0.3s ease; }
.nav__mobile-details[open] .nav__mobile-summary svg { transform: rotate(180deg); }
.nav__mobile-sub-menu { background: rgba(0,0,0,0.02); padding-left: 15px; padding-bottom: 5px; }
</style>