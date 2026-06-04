<?php
/**
 * Sugarcane History Section
 * Drop into your theme or include via shortcode / template part
 * Requires: The Cane House CSS variables already loaded
 */
defined( 'ABSPATH' ) || exit;

function ch_get_history_pages() {
    return [
        [
            'era'      => '8000 BCE · Origins',
            'tag'      => 'Chapter 1 · Origins',
            'title'    => 'Born in New Guinea',
            'accent'   => 'New Guinea',
            'body'     => 'Sugarcane - <em>Saccharum officinarum</em> - was first domesticated in New Guinea around 8000 BCE. Ancient peoples chewed raw stalks for their sweet juice. Ocean-going traders and settlers carried the crop across Pacific islands and into Southeast Asia, making it one of humanity\'s oldest cultivated plants.',
            'image'    => 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?w=600&q=80',
            'image_alt'=> 'Sugarcane field at sunrise',
            'countries'=> ['🇵🇬 New Guinea', '🌏 Pacific Islands', '🇮🇩 Indonesia'],
            'facts'    => [
                ['icon' => '🌱', 'text' => 'World\'s oldest cultivated crop'],
                ['icon' => '📍', 'text' => 'Spread via ocean trade routes'],
            ],
            'impacts'  => [],
        ],
        [
            'era'      => '500 BCE · India',
            'tag'      => 'Chapter 2 · India & the East',
            'title'    => 'Crystallised into Sugar',
            'accent'   => 'Sugar',
            'body'     => 'Around 500 BCE, India became the first civilisation to extract and crystallise sugar from cane. Sanskrit texts describe <em>khanda</em> - the ancestor of the word "candy." Indian traders exported this white crystal gold along the Silk Road. Persia encountered it around 600 CE and famously called it "a reed that gives honey without bees."',
            'image'    => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80',
            'image_alt'=> 'Traditional sugar processing in India',
            'countries'=> ['🇮🇳 India', '🇮🇷 Persia', '🇨🇳 China'],
            'facts'    => [],
            'impacts'  => [
                'First solid sugar produced - <em>khanda</em> → candy',
                'Ayurvedic medicine used cane juice for healing',
                'Exported via Silk Road to the Mediterranean',
            ],
        ],
        [
            'era'      => '700 CE · Arab World',
            'tag'      => 'Chapter 3 · Arab Expansion',
            'title'    => 'The Arab Sugar Revolution',
            'accent'   => 'Arab Sugar',
            'body'     => 'Arab expansion in the 7th–9th centuries spread sugarcane from Persia across North Africa, Sicily, and Spain. Arab scientists perfected refining techniques - producing clear white sugar for the first time. Medieval Europe encountered it as a rare, costly spice available only in apothecaries and prized above gold.',
            'image'    => 'https://images.unsplash.com/photo-1567306226416-28f0efdc88ce?w=600&q=80',
            'image_alt'=> 'Ancient spice market',
            'countries'=> ['🇪🇬 Egypt', '🇲🇦 Morocco', '🇪🇸 Spain', '🇮🇹 Sicily'],
            'facts'    => [
                ['icon' => '⚗️', 'text' => 'First white refined sugar'],
                ['icon' => '💊', 'text' => 'Sold as medicine in Europe'],
            ],
            'impacts'  => [],
        ],
        [
            'era'      => '1400s · Colonialism',
            'tag'      => 'Chapter 4 · Colonial Plantations',
            'title'    => 'Sugar & the Slave Trade',
            'accent'   => 'Slave Trade',
            'body'     => 'Portugal planted cane in Madeira (1420) then Brazil (1530). Spain brought it to the Caribbean. Europe\'s insatiable demand for sugar fuelled the transatlantic slave trade - millions were enslaved to work brutal plantation conditions. Sugar became the most profitable colonial commodity, reshaping entire continents.',
            'image'    => 'https://images.unsplash.com/photo-1548247416-ec66f4900b2e?w=600&q=80',
            'image_alt'=> 'Historic sugarcane plantation ruins',
            'countries'=> ['🇧🇷 Brazil', '🇯🇲 Jamaica', '🇵🇹 Portugal', '🇬🇧 Britain'],
            'facts'    => [],
            'impacts'  => [
                'Triangular trade: Europe → Africa → Americas',
                'Over 12 million Africans enslaved for plantations',
                'Caribbean islands reshaped entirely for cane',
            ],
        ],
        [
            'era'      => '1800s · Industrialisation',
            'tag'      => 'Chapter 5 · Industrial Age',
            'title'    => 'Steam, Mills & Mass Production',
            'accent'   => 'Mills & Mass',
            'body'     => 'Steam-powered mills and centrifugal refining transformed sugar into a mass-market commodity within decades. Cuba and Brazil dominated global supply. The abolition of slavery forced a shift to indentured labour from India and China. By 1900 sugar was no longer a luxury - it was in every kitchen on Earth.',
            'image'    => 'https://images.unsplash.com/photo-1530836369250-ef72a3f5cda8?w=600&q=80',
            'image_alt'=> 'Historic sugar mill machinery',
            'countries'=> ['🇨🇺 Cuba', '🇧🇷 Brazil', '🇺🇸 USA', '🇿🇦 South Africa'],
            'facts'    => [
                ['icon' => '⚙️', 'text' => 'Steam mills → mass refining'],
                ['icon' => '🚢', 'text' => 'Indentured labour from Asia'],
            ],
            'impacts'  => [],
        ],
        [
            'era'      => '1900s–Now · Modern',
            'tag'      => 'Chapter 6 · Modern Era',
            'title'    => 'Global Crop & Biofuel',
            'accent'   => 'Biofuel',
            'body'     => 'Today sugarcane is grown in over 100 countries, producing roughly 2 billion tonnes annually. Brazil leads global production, followed by India, China, and Thailand. Beyond sugar, cane produces ethanol biofuel, rum, cachaça, bagasse biomass energy, and molasses - covering more farmland than any other crop on Earth.',
            'image'    => 'https://images.unsplash.com/photo-1473973266408-ed4e27abdd47?w=600&q=80',
            'image_alt'=> 'Modern sugarcane harvest aerial view',
            'countries'=> ['🇧🇷 Brazil #1', '🇮🇳 India #2', '🇨🇳 China #3', '🇹🇭 Thailand #4'],
            'facts'    => [
                ['icon' => '⛽', 'text' => '40% of world\'s bioethanol'],
                ['icon' => '🌐', 'text' => '100+ countries cultivate cane'],
            ],
            'impacts'  => [],
        ],
        [
            'era'      => 'Legacy · Impact',
            'tag'      => 'Chapter 7 · Impact & Legacy',
            'title'    => 'A Bittersweet History',
            'accent'   => 'Bittersweet',
            'body'     => 'No crop has shaped human civilisation more dramatically - or more violently. Sugarcane drove colonialism, slavery, migration, and the globalisation of trade. Today it fuels economies and bioenergy, yet also contributes to deforestation, water stress, and the global crisis of excess sugar consumption.',
            'image'    => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?w=600&q=80',
            'image_alt'=> 'Sugarcane juice being pressed fresh',
            'countries'=> [],
            'facts'    => [],
            'impacts'  => [
                'Shaped the Americas, Caribbean & Indian Ocean identity',
                'Drove global migration - enslaved Africans, indentured Indians',
                'Sugar linked to obesity, diabetes, and processed food crisis',
                'Deforestation of Amazon & savanna for cane fields',
                'Cane ethanol = 90% cleaner than fossil fuel (Brazil data)',
            ],
        ],
        [
            'era'      => 'Products · Uses',
            'tag'      => 'Chapter 8 · Products',
            'title'    => 'More than Just Sugar',
            'accent'   => 'Just Sugar',
            'body'     => 'Every part of the cane is used. Juice yields white sugar, raw sugar, jaggery, molasses, and rum. The fibrous bagasse powers sugar mills and generates electricity. Cane wax is used in polishes. Ethanol goes into fuel tanks. Even the leaves become animal fodder - zero waste, total utility.',
            'image'    => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=600&q=80',
            'image_alt'=> 'Fresh sugarcane juice glasses',
            'countries'=> [],
            'facts'    => [
                ['icon' => '🍫', 'text' => 'White & raw sugar, jaggery'],
                ['icon' => '🍹', 'text' => 'Rum, cachaça, fresh juice'],
                ['icon' => '⚡', 'text' => 'Bagasse electricity generation'],
                ['icon' => '⛽', 'text' => 'Ethanol biofuel'],
                ['icon' => '💊', 'text' => 'Molasses, animal feed, wax'],
            ],
            'impacts'  => [],
        ],
    ];
}
?>

<section id="ch-history" class="ch-history-section">
    <div class="container">

        <div class="ch-history__header fade-up">
            <div class="ch-section-tag">Story of Sugarcane</div>
            <h2 class="ch-section-title">10,000 Years of <span class="accent">Sweet History</span></h2>
            <p class="ch-section-body">From a wild grass in New Guinea to the world's most traded commodity - the extraordinary journey of sugarcane.</p>
        </div>

        
    </div>
    <!-- Book / Page-turn carousel -->
    <div class="ch-book" id="ch-book" aria-label="Sugarcane history book" role="region">

        <!-- Shadow beneath book -->
        <div class="ch-book-shadow" aria-hidden="true"></div>

        <!-- Pages stack -->
        <div class="ch-book-pages" id="ch-book-pages">
            <?php
            $pages = ch_get_history_pages();
            $total = count( $pages );
            foreach ( $pages as $i => $page ) :
                $page   = (array) $page;
                $is_first = $i === 0;
                $z      = $total - $i;
            ?>
            <article
                class="ch-book-page<?php echo $is_first ? ' is-active' : ''; ?>"
                data-index="<?php echo esc_attr( $i ); ?>"
                style="z-index:<?php echo esc_attr( $z ); ?>;"
                aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
            >
                <!-- Left leaf (image side) -->
                <div class="ch-page-left">
                    <div class="ch-page-img-wrap">
                        <img
                            src="<?php echo esc_url( $page['image'] ); ?>"
                            alt="<?php echo esc_attr( $page['image_alt'] ?? '' ); ?>"
                            class="ch-page-img"
                            loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
                        >
                        <div class="ch-page-era-badge"><?php echo esc_html( $page['era'] ); ?></div>
                        <!-- decorative cane watermark -->
                        <div class="ch-page-watermark" aria-hidden="true">🌿</div>
                    </div>
                    <!-- page number -->
                    <div class="ch-page-num-left"><?php echo sprintf( '%02d', $i + 1 ); ?></div>
                </div>

                <!-- Spine fold line -->
                <div class="ch-page-spine" aria-hidden="true"></div>

                <!-- Right leaf (content side) -->
                <div class="ch-page-right">
                    <div class="ch-page-content">
                        <div class="ch-section-tag ch-page-tag"><?php echo esc_html( $page['tag'] ); ?></div>

                        <h3 class="ch-page-title">
                            <?php
                            $title  = esc_html( $page['title'] ?? '' );
                            $accent = esc_html( $page['accent'] ?? '' );
                            if ( $accent ) {
                                echo str_replace( $accent, '<span class="accent">' . $accent . '</span>', $title );
                            } else {
                                echo $title;
                            }
                            ?>
                        </h3>

                        <p class="ch-page-body"><?php echo wp_kses_post( $page['body'] ?? '' ); ?></p>

                        <?php if ( ! empty( $page['countries'] ) ) : ?>
                        <div class="ch-page-countries">
                            <?php foreach ( (array) $page['countries'] as $country ) : ?>
                                <span class="ch-page-country"><?php echo esc_html( $country ); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $page['facts'] ) ) : ?>
                        <div class="ch-page-facts">
                            <?php foreach ( (array) $page['facts'] as $fact ) :
                                $fact = (array) $fact;
                            ?>
                                <div class="ch-page-fact">
                                    <span class="ch-pf-icon" aria-hidden="true"><?php echo esc_html( $fact['icon'] ?? '' ); ?></span>
                                    <span><?php echo esc_html( $fact['text'] ?? '' ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ( ! empty( $page['impacts'] ) ) : ?>
                        <ul class="ch-page-impacts">
                            <?php foreach ( (array) $page['impacts'] as $impact ) : ?>
                                <li><?php echo wp_kses_post( $impact ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>

                    <div class="ch-page-num-right"><?php echo sprintf( '%02d', $i + 1 ); ?> / <?php echo esc_html( $total ); ?></div>
                </div>

                <!-- Page-turn clicker (right half triggers next) -->
                <button class="ch-page-turn-trigger ch-page-turn-next" aria-label="Next page" tabindex="<?php echo $is_first ? '0' : '-1'; ?>"></button>
            </article>
            <?php endforeach; ?>
        </div><!-- .ch-book-pages -->

        <!-- Overlay turn animation element (the "peeling" leaf) -->
        <div class="ch-turn-leaf" id="ch-turn-leaf" aria-hidden="true">
            <div class="ch-turn-leaf-front"></div>
            <div class="ch-turn-leaf-back"></div>
        </div>

    </div><!-- .ch-book -->

    <!-- Controls outside book -->
    <div class="ch-history-nav fade-up">
        <button class="ch-v-btn" id="ch-hist-prev" aria-label="Previous page">←</button>

        <div class="ch-hist-dots" id="ch-hist-dots" role="tablist" aria-label="History pages">
            <?php foreach ( $pages as $i => $_ ) : ?>
                <button
                    class="ch-hdot<?php echo $i === 0 ? ' active' : ''; ?>"
                    role="tab"
                    aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                    aria-label="Chapter <?php echo $i + 1; ?>"
                    data-goto="<?php echo esc_attr( $i ); ?>"
                ></button>
            <?php endforeach; ?>
        </div>

        <button class="ch-v-btn" id="ch-hist-next" aria-label="Next page">→</button>
    </div>

    <!-- Progress line (the "paper edge" line) -->
    <div class="ch-hist-progress-wrap" aria-hidden="true">
        <div class="ch-hist-progress-track">
            <div class="ch-hist-progress-fill" id="ch-hist-pfill" style="width:<?php echo round( 1 / $total * 100 ); ?>%"></div>
            <?php foreach ( $pages as $i => $page ) : ?>
                <div class="ch-hist-tick" style="left:<?php echo round( ($i / ($total-1)) * 100 ); ?>%" data-label="<?php echo esc_attr( $page['era'] ?? '' ); ?>"></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    const TOTAL_HISTORY_INFO   = <?php echo (int) $total; ?>;

</script>