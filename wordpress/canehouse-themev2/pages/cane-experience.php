<?php get_header(); ?>

<?php
// ═══════════════════════════════════════════════════════
//  PAGE DATA
// ═══════════════════════════════════════════════════════

$hero_stalks = [
    [ 'left' => '5%',  'height' => '260px', 'delay' => '0s',   'opacity' => 1   ],
    [ 'left' => '10%', 'height' => '340px', 'delay' => '0.4s', 'opacity' => 0.6 ],
    [ 'left' => '87%', 'height' => '300px', 'delay' => '0.8s', 'opacity' => 1   ],
    [ 'left' => '93%', 'height' => '220px', 'delay' => '1.2s', 'opacity' => 0.5 ],
    [ 'left' => '96%', 'height' => '380px', 'delay' => '0.2s', 'opacity' => 0.7 ],
    [ 'left' => '2%',  'height' => '180px', 'delay' => '1.6s', 'opacity' => 0.4 ],
];

$panels = [
    [
        'class'     => 'panel-crushed',
        'side'      => 'left',
        'num'       => '01',
        'tag'       => 'The Moment',
        'title'     => '<em>Freshly</em><br>Crushed',
        'p1'        => 'There is a moment — brief but extraordinary — when raw sugarcane meets the press and pure golden liquid begins to flow. This is where The Cane House experience begins.',
        'p2'        => 'No pre-made batches. No syrups reconstituted behind closed doors. Every glass is born right in front of you, from the stalk, under the press.',
        'details'   => [
            [ 'icon' => '🌾', 'strong' => 'Live pressing on every order', 'text' => '— never pre-made or pre-bottled. Your juice starts when you order it.' ],
            [ 'icon' => '👁️', 'strong' => 'Full transparency',            'text' => '— you watch every step. Nothing hidden, nothing artificial, nothing to hide.' ],
            [ 'icon' => '⏱️', 'strong' => 'Under 60 seconds',             'text' => 'from press to glass. Speed and freshness, perfectly combined.' ],
        ],
        'circle_class' => 'crushed',
        'circle_icon'  => '⚙️',
        'big_icon'     => '⚙️',
        'has_big_icon' => true,
    ],
    [
        'class'     => 'panel-chilled',
        'side'      => 'right',
        'num'       => '02',
        'tag'       => 'The Temperature',
        'title'     => 'Served<br><em>Chilled</em>',
        'p1'        => 'Temperature is flavour. Served too warm, cane juice loses its crispness. Drowned in ice, it loses its character. We serve at the precise point where freshness sings.',
        'p2'        => 'Naturally cooled — not diluted — so every sip carries the full, rounded sweetness the cane intended.',
        'details'   => [
            [ 'icon' => '🌡️', 'strong' => 'Precision chilling',   'text' => '— served at the ideal temperature without losing flavour to ice dilution.' ],
            [ 'icon' => '💧', 'strong' => 'No excessive ice',      'text' => 'unless requested — the natural sweetness of the cane deserves to reach you intact.' ],
            [ 'icon' => '✨', 'strong' => 'First sip perfection',  'text' => '— the moment the glass touches your lips should be the best moment of the drink.' ],
        ],
        'circle_class' => 'chilled',
        'circle_icon'  => '🧊',
        'has_big_icon' => false,
    ],
    [
        'class'     => 'panel-live',
        'side'      => 'left',
        'num'       => '03',
        'tag'       => 'The Craft',
        'title'     => '<em>Crafted</em><br>Live',
        'p1'        => 'The theatre of live pressing is part of the magic. You don\'t just receive a drink — you witness its creation. The sound of the press, the colour of the juice, the scent of fresh cane.',
        'p2'        => 'Every Cane House server is trained to craft your blend with precision — not just press and pour, but create a moment you remember.',
        'details'   => [
            [ 'icon' => '🎭', 'strong' => 'Live theatre',       'text' => '— the pressing experience is a performance as much as it is production.' ],
            [ 'icon' => '🎓', 'strong' => 'Trained craftspeople','text' => '— every server completes full pressing and service training before serving customers.' ],
            [ 'icon' => '🌿', 'strong' => 'Botanicals added fresh','text' => '— mint, ginger, lemon are added by hand, precisely, with care.' ],
        ],
        'circle_class' => 'live',
        'circle_icon'  => '🎯',
        'has_big_icon' => false,
    ],
    [
        'class'     => 'panel-ambience',
        'side'      => 'right',
        'num'       => '04',
        'tag'       => 'The Atmosphere',
        'title'     => 'Premium<br><em>Ambience</em>',
        'p1'        => 'A Cane House location is not a street stall. It is a carefully considered space — clean, considered, and premium. Where modern design meets tropical warmth.',
        'p2'        => 'Our aesthetic philosophy: less noise, more presence. Natural textures, deep greens, warm lighting, and the unmistakable scent of fresh cane in the air.',
        'details'   => [
            [ 'icon' => '🏡', 'strong' => 'Designed environments', 'text' => '— every location follows Cane House aesthetic standards. Premium, always.' ],
            [ 'icon' => '🌱', 'strong' => 'Natural materials',      'text' => '— wood, stone, and organic textures that feel honest and grounded.' ],
            [ 'icon' => '🎶', 'strong' => 'Curated atmosphere',     'text' => '— soundscapes, lighting, and presentation all considered as part of the experience.' ],
        ],
        'circle_class' => 'ambience',
        'circle_icon'  => '🏪',
        'has_big_icon' => false,
    ],
    [
        'class'     => 'panel-tropical',
        'side'      => 'left',
        'num'       => '05',
        'tag'       => 'The Feeling',
        'title'     => 'Tropical<br><em>Mood</em>',
        'p1'        => 'One sip and you are transported. The golden green of freshly pressed cane. The coolness against your palm. The earthy sweetness at the back of your throat.',
        'p2'        => 'This is not just refreshment — it is transport. A sensory postcard from the tropics, delivered wherever The Cane House is found.',
        'details'   => [
            [ 'icon' => '☀️', 'strong' => 'Tropical sensory experience', 'text' => '— the colour, scent, temperature, and taste all evoke something warm and real.' ],
            [ 'icon' => '🌊', 'strong' => 'Escapism in a glass',         'text' => '— for regular customers, that first sip is a mini holiday. That\'s the power of pure.' ],
            [ 'icon' => '💚', 'strong' => 'Emotional connection',         'text' => '— for many, cane juice connects deeply to heritage, memory, and home.' ],
        ],
        'circle_class' => 'tropical',
        'circle_icon'  => '🌴',
        'has_big_icon' => false,
    ],
    [
        'class'     => 'panel-natural',
        'side'      => 'right',
        'num'       => '06',
        'tag'       => 'The Ingredients',
        'title'     => 'Natural<br><em>Always</em>',
        'p1'        => 'Every ingredient that enters a Cane House glass is real, whole, and unprocessed. Sugarcane stalks, whole ginger root, fresh lemon, real mint leaves, cold-pressed fruits.',
        'p2'        => 'No shortcuts. No substitutes. No compromises. The ingredient list of a Cane House drink reads the same as nature\'s recipe.',
        'details'   => [
            [ 'icon' => '🌾', 'strong' => 'Yellow & Red Cane',        'text' => '— two varieties, each with distinct character. Both 100% natural, always fresh.' ],
            [ 'icon' => '🍋', 'strong' => 'Whole botanicals only',    'text' => '— lemon, ginger, mint. Real. Whole. Never extracted, never artificial.' ],
            [ 'icon' => '🍍', 'strong' => 'Cold-pressed tropical fruits','text' => '— pineapple, watermelon, strawberry. Actual fruit. Not syrup. Not concentrate.' ],
        ],
        'circle_class' => 'natural',
        'circle_icon'  => '🌿',
        'has_big_icon' => false,
    ],
];

$senses = [
    [ 'emoji' => '👁️', 'label' => 'Sight', 'desc' => 'The golden-green of freshly pressed cane. The theatre of live extraction. Beauty in motion.' ],
    [ 'emoji' => '👃', 'label' => 'Scent', 'desc' => 'The clean, grassy, faintly sweet aroma of freshly crushed cane. Unmistakable. Unforgettable.' ],
    [ 'emoji' => '👂', 'label' => 'Sound', 'desc' => 'The distinctive rhythm of the press. The pour. The clink. The first sip. A full sound story.' ],
    [ 'emoji' => '🤲', 'label' => 'Touch', 'desc' => 'The cool glass in your palm. The gentle condensation. The weight of something real.' ],
    [ 'emoji' => '👅', 'label' => 'Taste', 'desc' => 'Natural sweetness. Mineral depth. Clean botanical finish. Nothing artificial. Pure perfection.' ],
];
?>


<!-- ══ CINEMATIC HERO ══ -->
<section class="hero">
  <?php foreach ( $hero_stalks as $stalk ) : ?>
    <div class="stalk" style="left:<?php echo esc_attr( $stalk['left'] ); ?>;height:<?php echo esc_attr( $stalk['height'] ); ?>;animation-delay:<?php echo esc_attr( $stalk['delay'] ); ?>;<?php echo $stalk['opacity'] < 1 ? 'opacity:' . $stalk['opacity'] . ';' : ''; ?>"></div>
  <?php endforeach; ?>

  <div class="hero-inner">
    <div class="hero-eyebrow">The Cane House · A Sensory Journey</div>
    <h1>
      <span class="line1">The Cane</span>
      <span class="line2">Experience</span>
      <span class="line3">Freshly Crushed · Served Chilled · Crafted Live</span>
    </h1>
    <p class="hero-sub">This is not just a drink. It's a moment — pressed from the earth, cooled by nature, served with reverence. Welcome to the luxury of pure.</p>
    <div class="scroll-hint">
      <span>discover</span>
      <div class="line"></div>
    </div>
  </div>
</section>


<!-- ══ IMMERSIVE PANELS ══ -->
<?php foreach ( $panels as $panel ) : ?>
  <section class="panel <?php echo esc_attr( $panel['class'] ); ?>">
    <?php if ( $panel['class'] === 'panel-crushed' ) : ?>
      <div class="panel-deco"></div>
    <?php endif; ?>

    <?php if ( ! empty( $panel['has_big_icon'] ) ) : ?>
      <div class="big-icon"><?php echo $panel['big_icon']; ?></div>
    <?php endif; ?>

    <?php
      $reveal_content = $panel['side'] === 'left' ? 'reveal-left' : 'reveal-right';
      $reveal_visual  = $panel['side'] === 'left' ? 'reveal-right' : 'reveal-left';
    ?>

    <div class="panel-content <?php echo $reveal_content; ?>">
      <div class="panel-num"><?php echo esc_html( $panel['num'] ); ?></div>
      <div class="panel-tag"><?php echo esc_html( $panel['tag'] ); ?></div>
      <h2><?php echo $panel['title']; ?></h2>
      <p><?php echo esc_html( $panel['p1'] ); ?></p>
      <p><?php echo esc_html( $panel['p2'] ); ?></p>
      <div class="panel-details">
        <?php foreach ( $panel['details'] as $detail ) : ?>
          <div class="pd-item">
            <div class="pd-icon"><?php echo $detail['icon']; ?></div>
            <div class="pd-text"><strong><?php echo esc_html( $detail['strong'] ); ?></strong> <?php echo esc_html( $detail['text'] ); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="panel-visual <?php echo $reveal_visual; ?>">
      <div class="pv-circle <?php echo esc_attr( $panel['circle_class'] ); ?>">
        <div class="pv-ring"></div>
        <div class="pv-ring2"></div>
        <?php echo $panel['circle_icon']; ?>
      </div>
    </div>
  </section>
<?php endforeach; ?>


<!-- ══ SENSORY BREAKDOWN ══ -->
<section class="sensory-section">
  <div class="sensory-inner">
    <div class="sensory-tag">The Five Senses</div>
    <div class="sensory-title">Designed for Every <em>Sense</em></div>
    <div class="sensory-grid reveal">
      <?php foreach ( $senses as $sense ) : ?>
        <div class="sg-item">
          <div class="sg-emoji"><?php echo $sense['emoji']; ?></div>
          <div class="sg-label"><?php echo esc_html( $sense['label'] ); ?></div>
          <div class="sg-desc"><?php echo esc_html( $sense['desc'] ); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ══ CLOSER ══ -->
<section class="closer">
  <div class="closer-glow"></div>
  <div class="closer-inner reveal">
    <h2>This Is<br><em>The Cane House</em></h2>
    <p>The art of refreshment. Pressed live, served cool, crafted with the care this ancient crop deserves. Come experience it for yourself.</p>
    <a href="#" class="closer-btn">Find Your Nearest Location →</a>
  </div>
</section>


<script>
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => observer.observe(el));
</script>

<?php get_footer(); ?>