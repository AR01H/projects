#!/usr/bin/env python3
"""
Generates the Cane Rush vintage travel-poster SVG asset set.
Flat shapes, bold linocut-style ink outlines, limited retro palette.

HOW TO RESTYLE EVERYTHING AT ONCE:
  1. Edit the color values in the PALETTE section directly below.
  2. Run:  python3 generate_assets.py
  3. All 30 .svg files in this folder get overwritten with the new colors.
     (config.js references these files by plain filename — nothing else
     needs to change.)

You can also hand-edit any single .svg file in a text or vector editor
(they're plain SVG markup, not images) if you only want to tweak one
obstacle/collectible/power-up instead of the whole set.
"""
import os

OUT = os.path.dirname(os.path.abspath(__file__))  # writes flat, right next to index.html and the .js files
os.makedirs(OUT, exist_ok=True)

# ==================== PALETTE — change these, then rerun this script ====================
INK       = "#3B2A1E"   # deep umber outline, used on everything
CREAM     = "#F3E5C8"   # parchment
MUSTARD   = "#E8A33D"
TERRACOTTA= "#C1502E"
TEAL      = "#2F7A72"
OLIVE     = "#7A8450"
ROSE      = "#C77B71"
GOLDEN    = "#D9A441"
CREAM2    = "#EAD9AE"
NAVY      = "#2E4A5C"
SAND      = "#DEC28B"

def write(name, body, vb="0 0 140 160"):
    svg = f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{vb}">\n{body}\n</svg>\n'
    path = os.path.join(OUT, name)
    with open(path, "w") as f:
        f.write(svg)
    print("wrote", name)

def badge(fill, icon_body, ring=GOLDEN):
    """Round vintage stamp/badge backdrop used for collectibles & power-ups."""
    return f'''
  <circle cx="70" cy="70" r="56" fill="{CREAM}" stroke="{INK}" stroke-width="5"/>
  <circle cx="70" cy="70" r="47" fill="none" stroke="{ring}" stroke-width="3" stroke-dasharray="6 5"/>
  <circle cx="70" cy="70" r="40" fill="{fill}" stroke="{INK}" stroke-width="4"/>
  {icon_body}
'''

# =========================================================
# OBSTACLES — bold flat silhouettes, thick ink outline, one
# accent color each so the required action reads instantly.
# =========================================================

write("obstacle-fallen-cane.svg", f'''
  <g transform="rotate(-8 70 100)">
    <rect x="15" y="88" width="110" height="26" rx="12" fill="{OLIVE}" stroke="{INK}" stroke-width="5"/>
    <line x1="42" y1="88" x2="42" y2="114" stroke="{INK}" stroke-width="3"/>
    <line x1="70" y1="88" x2="70" y2="114" stroke="{INK}" stroke-width="3"/>
    <line x1="98" y1="88" x2="98" y2="114" stroke="{INK}" stroke-width="3"/>
  </g>
''')

write("obstacle-rolling-coconut.svg", f'''
  <circle cx="70" cy="95" r="34" fill="{SAND}" stroke="{INK}" stroke-width="5"/>
  <circle cx="58" cy="82" r="8" fill="{CREAM}" opacity="0.6"/>
  <path d="M50 95 Q70 82 90 95" stroke="{INK}" stroke-width="3" fill="none"/>
''')

write("obstacle-juice-crate.svg", f'''
  <rect x="25" y="65" width="90" height="65" rx="6" fill="{MUSTARD}" stroke="{INK}" stroke-width="5"/>
  <line x1="25" y1="65" x2="115" y2="130" stroke="{INK}" stroke-width="4"/>
  <line x1="115" y1="65" x2="25" y2="130" stroke="{INK}" stroke-width="4"/>
  <rect x="25" y="65" width="90" height="65" rx="6" fill="none" stroke="{INK}" stroke-width="5"/>
''')

write("obstacle-slippery-puddle.svg", f'''
  <ellipse cx="70" cy="112" rx="55" ry="16" fill="{TEAL}" opacity="0.75" stroke="{INK}" stroke-width="4"/>
  <ellipse cx="70" cy="112" rx="36" ry="9" fill="none" stroke="{CREAM}" stroke-width="2.5" opacity="0.8"/>
''')

write("obstacle-coconut-pile.svg", f'''
  <circle cx="45" cy="108" r="26" fill="{SAND}" stroke="{INK}" stroke-width="5"/>
  <circle cx="95" cy="108" r="26" fill="{SAND}" stroke="{INK}" stroke-width="5"/>
  <circle cx="70" cy="72" r="28" fill="{SAND}" stroke="{INK}" stroke-width="5"/>
  <circle cx="62" cy="64" r="8" fill="{CREAM}" opacity="0.55"/>
''')

write("obstacle-wooden-barrier.svg", f'''
  <rect x="18" y="18" width="104" height="34" rx="6" fill="{TERRACOTTA}" stroke="{INK}" stroke-width="5"/>
  <rect x="18" y="18" width="104" height="12" rx="4" fill="{ROSE}"/>
  <rect x="10" y="46" width="12" height="100" fill="{INK}" opacity="0.85"/>
  <rect x="118" y="46" width="12" height="100" fill="{INK}" opacity="0.85"/>
  <line x1="42" y1="18" x2="42" y2="52" stroke="{INK}" stroke-width="3"/>
  <line x1="70" y1="18" x2="70" y2="52" stroke="{INK}" stroke-width="3"/>
  <line x1="98" y1="18" x2="98" y2="52" stroke="{INK}" stroke-width="3"/>
''', vb="0 0 140 170")

write("obstacle-market-arch.svg", f'''
  <rect x="18" y="18" width="104" height="34" rx="6" fill="{NAVY}" stroke="{INK}" stroke-width="5"/>
  <rect x="18" y="18" width="104" height="12" rx="4" fill="{TEAL}"/>
  <rect x="10" y="46" width="12" height="100" fill="{INK}" opacity="0.85"/>
  <rect x="118" y="46" width="12" height="100" fill="{INK}" opacity="0.85"/>
  <path d="M30 34 h80" stroke="{GOLDEN}" stroke-width="4" stroke-dasharray="6 5"/>
''', vb="0 0 140 170")

write("obstacle-fruit-cart.svg", f'''
  <rect x="15" y="55" width="110" height="55" rx="12" fill="{TERRACOTTA}" stroke="{INK}" stroke-width="5"/>
  <rect x="24" y="62" width="92" height="22" rx="6" fill="{CREAM}" stroke="{INK}" stroke-width="3"/>
  <circle cx="42" cy="72" r="9" fill="{MUSTARD}" stroke="{INK}" stroke-width="2.5"/>
  <circle cx="70" cy="72" r="9" fill="{OLIVE}" stroke="{INK}" stroke-width="2.5"/>
  <circle cx="98" cy="72" r="9" fill="{ROSE}" stroke="{INK}" stroke-width="2.5"/>
  <circle cx="38" cy="128" r="13" fill="{INK}"/>
  <circle cx="102" cy="128" r="13" fill="{INK}"/>
  <circle cx="38" cy="128" r="5" fill="{SAND}"/>
  <circle cx="102" cy="128" r="5" fill="{SAND}"/>
''')

write("obstacle-delivery-truck.svg", f'''
  <rect x="12" y="30" width="116" height="78" rx="10" fill="{TEAL}" stroke="{INK}" stroke-width="5"/>
  <rect x="20" y="38" width="100" height="30" rx="6" fill="{CREAM}" stroke="{INK}" stroke-width="3"/>
  <rect x="24" y="72" width="92" height="10" rx="4" fill="{CREAM}" opacity="0.6"/>
  <circle cx="38" cy="118" r="13" fill="{INK}"/>
  <circle cx="102" cy="118" r="13" fill="{INK}"/>
  <circle cx="38" cy="118" r="5" fill="{SAND}"/>
  <circle cx="102" cy="118" r="5" fill="{SAND}"/>
''')

write("obstacle-market-stall.svg", f'''
  <rect x="14" y="18" width="112" height="22" rx="4" fill="{ROSE}" stroke="{INK}" stroke-width="5"/>
  <path d="M14 40 l16 22 l16 -22 l16 22 l16 -22 l16 22 l16 -22 l16 22" fill="{CREAM}" stroke="{INK}" stroke-width="3"/>
  <rect x="18" y="62" width="104" height="62" rx="4" fill="{SAND}" stroke="{INK}" stroke-width="5"/>
  <circle cx="46" cy="90" r="11" fill="{MUSTARD}" stroke="{INK}" stroke-width="2.5"/>
  <circle cx="70" cy="90" r="11" fill="{OLIVE}" stroke="{INK}" stroke-width="2.5"/>
  <circle cx="94" cy="90" r="11" fill="{ROSE}" stroke="{INK}" stroke-width="2.5"/>
''')

write("obstacle-bottle-stack.svg", f'''
  <rect x="24" y="76" width="26" height="46" rx="9" fill="{OLIVE}" stroke="{INK}" stroke-width="4"/>
  <rect x="90" y="76" width="26" height="46" rx="9" fill="{TERRACOTTA}" stroke="{INK}" stroke-width="4"/>
  <rect x="57" y="36" width="26" height="46" rx="9" fill="{TEAL}" stroke="{INK}" stroke-width="4"/>
  <rect x="31" y="64" width="12" height="14" rx="3" fill="{CREAM}"/>
  <rect x="97" y="64" width="12" height="14" rx="3" fill="{CREAM}"/>
  <rect x="64" y="24" width="12" height="14" rx="3" fill="{CREAM}"/>
''')

# =========================================================
# COLLECTIBLES — vintage stamp badges
# =========================================================

leaf = f'<path d="M0 -6 Q-10 -22 0 -30 Q10 -22 0 -6" fill="{OLIVE}" stroke="{INK}" stroke-width="2.5"/>'

write("collectible-cane.svg", badge(OLIVE, f'''
  <rect x="64" y="46" width="12" height="46" rx="6" fill="{CREAM}" stroke="{INK}" stroke-width="3" transform="rotate(8 70 70)"/>
  <g transform="translate(70,46)">{leaf}</g>
'''))

write("collectible-cane-fresh.svg", badge("#8FAE4E", f'''
  <rect x="64" y="46" width="12" height="46" rx="6" fill="{CREAM}" stroke="{INK}" stroke-width="3" transform="rotate(8 70 70)"/>
  <g transform="translate(70,46)">{leaf}</g>
''', ring=OLIVE))

write("collectible-cane-golden.svg", badge(GOLDEN, f'''
  <rect x="64" y="46" width="12" height="46" rx="6" fill="{CREAM}" stroke="{INK}" stroke-width="3" transform="rotate(8 70 70)"/>
  <g transform="translate(70,46)">{leaf}</g>
  <path d="M50 55 l4 8 l8 1 l-6 6 l1 8 l-7 -4 l-7 4 l1 -8 l-6 -6 l8 -1 z" fill="{CREAM}" opacity="0.85"/>
''', ring=TERRACOTTA))

write("collectible-coin.svg", badge(MUSTARD, f'''
  <text x="70" y="80" font-family="Georgia, serif" font-size="34" font-weight="900" fill="{INK}" text-anchor="middle">C</text>
''', ring=TERRACOTTA))

write("collectible-fruit-mango.svg", badge(MUSTARD, f'''
  <ellipse cx="70" cy="72" rx="20" ry="24" fill="{TERRACOTTA}" opacity="0.55"/>
'''))
write("collectible-fruit-pineapple.svg", badge(MUSTARD, f'''
  <ellipse cx="70" cy="76" rx="17" ry="20" fill="{OLIVE}" opacity="0.4"/>
  <path d="M70 46 Q64 34 70 26 Q76 34 70 46" fill="{OLIVE}" stroke="{INK}" stroke-width="2"/>
'''))
write("collectible-fruit-lemon.svg", badge("#F2E24A", f'''
  <ellipse cx="70" cy="70" rx="18" ry="24" fill="none" stroke="{INK}" stroke-width="2" opacity="0.5"/>
'''))
write("collectible-fruit-coconut.svg", badge(SAND, f'''
  <circle cx="60" cy="60" r="6" fill="{INK}" opacity="0.6"/>
  <circle cx="80" cy="60" r="6" fill="{INK}" opacity="0.6"/>
  <circle cx="70" cy="76" r="6" fill="{INK}" opacity="0.6"/>
'''))
write("collectible-fruit-watermelon.svg", badge(ROSE, f'''
  <path d="M50 70 A20 20 0 0 1 90 70" fill="none" stroke="{OLIVE}" stroke-width="5"/>
  <circle cx="64" cy="76" r="2.4" fill="{INK}"/>
  <circle cx="76" cy="76" r="2.4" fill="{INK}"/>
  <circle cx="70" cy="84" r="2.4" fill="{INK}"/>
'''))

# =========================================================
# POWER-UP BADGES — one distinct glyph each, badge ring color
# doubles as a quick-glance identity cue.
# =========================================================

write("powerup-cane-magnet.svg", badge(NAVY, f'''
  <path d="M56 50 v20 a14 14 0 0 0 28 0 v-20" fill="none" stroke="{CREAM}" stroke-width="7"/>
  <rect x="50" y="44" width="12" height="12" fill="{CREAM}"/>
  <rect x="78" y="44" width="12" height="12" fill="{CREAM}"/>
  <rect x="50" y="44" width="12" height="4" fill="{TERRACOTTA}"/>
  <rect x="78" y="44" width="12" height="4" fill="{TERRACOTTA}"/>
'''))

write("powerup-sugar-rush.svg", badge(TERRACOTTA, f'''
  <path d="M76 42 L54 76 h14 l-6 26 l28 -38 h-15 z" fill="{CREAM}" stroke="{INK}" stroke-width="2.5"/>
'''))

write("powerup-juice-shield.svg", badge(TEAL, f'''
  <path d="M70 44 L92 52 V72 Q92 92 70 100 Q48 92 48 72 V52 Z" fill="{CREAM}" stroke="{INK}" stroke-width="3"/>
'''))

write("powerup-golden-cane.svg", badge(GOLDEN, f'''
  <path d="M70 44 l7 15 16 2 -12 11 3 16 -14 -8 -14 8 3 -16 -12 -11 16 -2 z" fill="{CREAM}" stroke="{INK}" stroke-width="2.5"/>
''', ring=TERRACOTTA))

write("powerup-fruit-blast.svg", badge(ROSE, f'''
  <path d="M70 42 l6 12 13 -4 -6 12 12 6 -13 4 2 14 -10 -9 -10 9 2 -14 -13 -4 12 -6 -6 -12 13 4 z" fill="{CREAM}" stroke="{INK}" stroke-width="2"/>
'''))

write("powerup-super-jump.svg", badge(OLIVE, f'''
  <path d="M70 42 L92 74 H78 V98 H62 V74 H48 Z" fill="{CREAM}" stroke="{INK}" stroke-width="2.5"/>
'''))

write("powerup-fruit-boost.svg", badge(ROSE, f'''
  <rect x="58" y="46" width="24" height="42" rx="8" fill="{CREAM}" stroke="{INK}" stroke-width="2.5"/>
  <rect x="64" y="38" width="12" height="10" rx="3" fill="{CREAM}" stroke="{INK}" stroke-width="2"/>
  <line x1="58" y1="62" x2="82" y2="62" stroke="{TERRACOTTA}" stroke-width="3"/>
'''))

write("powerup-second-wind.svg", badge("#8FAE4E", f'''
  <path d="M70 42 A26 26 0 1 1 46 62" fill="none" stroke="{CREAM}" stroke-width="7"/>
  <path d="M46 50 L46 62 L58 62" fill="none" stroke="{CREAM}" stroke-width="7" stroke-linejoin="round" stroke-linecap="round"/>
''', ring=TEAL))

write("powerup-slow-sugar.svg", badge(SAND, f'''
  <circle cx="70" cy="70" r="20" fill="none" stroke="{INK}" stroke-width="4"/>
  <line x1="70" y1="70" x2="70" y2="56" stroke="{INK}" stroke-width="4" stroke-linecap="round"/>
  <line x1="70" y1="70" x2="80" y2="76" stroke="{INK}" stroke-width="4" stroke-linecap="round"/>
''', ring=NAVY))

write("powerup-coin-rain.svg", badge(MUSTARD, f'''
  <circle cx="58" cy="58" r="8" fill="{CREAM}" stroke="{INK}" stroke-width="2"/>
  <circle cx="80" cy="62" r="8" fill="{CREAM}" stroke="{INK}" stroke-width="2"/>
  <circle cx="68" cy="82" r="8" fill="{CREAM}" stroke="{INK}" stroke-width="2"/>
''', ring=TERRACOTTA))

print(f"\n{len([f for f in os.listdir(OUT) if f.endswith('.svg')])} SVG files written to {OUT}")
