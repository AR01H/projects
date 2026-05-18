-- ============================================================
-- The Cane House — Mock Data Seeder
-- Indian-authentic sugarcane juice business content
-- ============================================================
-- Table prefix: replace {prefix} with your WordPress prefix
-- e.g. if WP prefix is "wp_" and TABLE_MID_FIX='_cms_plug_':
--   Replace {prefix} → wp_ah_cms_plug_
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ── Reviews (Indian-authentic cultural context) ───────────────────────────────
INSERT INTO `{prefix}reviews`
    (`author_name`, `location`, `review_text`, `rating`, `result`, `status`)
VALUES
(
    'Priya Sharma',
    'Verified Customer — Leicester, Belgrave',
    'Reminds me of freshly pressed ganna ras from back home in Punjab! The ginger blend is absolutely incredible — just like the adrak-nimbu version you get at the roadside stalls. My whole family was so happy to have this at our Diwali celebration. Brought tears to my eyes!',
    5.0,
    'Perfect for Diwali celebrations',
    'active'
),
(
    'Mohammed Al-Rashid',
    'Event Client — Birmingham, Handsworth',
    'We hired The Cane House for our Eid family gathering. Over 80 guests and everyone was asking about the juice stall! The live pressing in front of guests was such a crowd puller — my aunties from back home said it was just like the sugarcane wallahs in Lahore. 10/10 service.',
    5.0,
    'Star attraction at the Eid gathering',
    'active'
),
(
    'Ananya & Rahul Patel',
    'Wedding Clients — Wolverhampton',
    'The highlight of our Indian wedding reception and Mehndi night! Our guests could not believe how fresh and natural it tasted — pressed live right in front of them. Our elders were nostalgic, saying it was just like the juice from the sugarcane machines in Gujarat. Absolutely recommended for desi weddings!',
    5.0,
    'Star of the Mehndi night',
    'active'
),
(
    'Sunita Reddy',
    'Verified Customer — Southall, West London',
    'Finally, authentic fresh sugarcane juice in the UK! No artificial flavours, no added sugar — just pure ganna ras the way we used to have it in Hyderabad. The lemon-ginger blend is my absolute favourite. I send The Cane House to all my UK desi friends now. A true taste of home!',
    5.0,
    'Authentic ganna ras taste of home',
    'active'
),
(
    'Vikram Singh',
    'Festival Organiser — Manchester, Rusholme',
    'Booked The Cane House for our Vaisakhi mela — 500+ attendees and the sugarcane stall was the longest queue all day! Professional, hygienic, and the taste was absolutely brilliant. Every sip felt like a celebration of our Punjabi heritage. Will definitely book again for our Diwali mela.',
    5.0,
    'Longest queue at the Vaisakhi mela',
    'active'
),
(
    'Sarah & James Thompson',
    'Verified Customer — Brighton',
    'Tried The Cane House at a summer festival — absolutely loved the pineapple tropical blend! So refreshing, so natural. My partner had never tried sugarcane juice before but now we are completely hooked. The staff explained the whole process and the history of ganna ras in India. Such a lovely experience.',
    5.0,
    'First-time sugarcane converts',
    'active'
),
(
    'Deepa Krishnamurthy',
    'Verified Customer — Coventry, Foleshill',
    'Growing up in Chennai, sugarcane juice was part of summer life. Finding The Cane House in the UK felt like such a gift! The pure cane variety is spot on — that slightly grassy, intensely sweet taste that takes you straight back. Thank you for bringing a piece of South India to our community.',
    5.0,
    'Taste of Tamil Nadu in the UK',
    'active'
),
(
    'Harpreet & Gurjit Dhillon',
    'Wedding Clients — Bradford',
    'Our Sangeet night was completely transformed by The Cane House! All our Punjabi family members were delighted — the uncles and aunties kept going back for more. The Red Cane with Mint was the most popular combination. Professional, on time, and genuinely passionate about their craft.',
    5.0,
    'Made the Sangeet night unforgettable',
    'active'
);

-- ── FAQs (10 entries covering juice, events, Indian context, franchise) ───────
INSERT INTO `{prefix}faqs`
    (`topic`, `question`, `answer`, `status`, `sort_order`)
VALUES
(
    'General',
    'Do you add any sugar or preservatives?',
    'No, absolutely not. Our sugarcane juice is 100% natural, pressed live from the stalk. The sweetness comes entirely from the natural sugars in the cane itself — just as it has been enjoyed across India, South Asia, and tropical cultures for over 2,000 years. Ganna ras in its purest form.',
    'active', 1
),
(
    'General',
    'How long does the juice stay fresh?',
    'Fresh sugarcane juice is best enjoyed immediately after pressing — just like a fresh glass of ganna ras from a roadside stall in India! If kept chilled, it can stay fresh for up to 24 hours. We always recommend drinking it cool and fresh for the very best flavour.',
    'active', 2
),
(
    'General',
    'Is the juice suitable for everyone?',
    'Yes! Fresh sugarcane juice is enjoyed by people of all ages and dietary backgrounds — it is naturally vegan, gluten-free, and dairy-free. In Ayurvedic medicine, sugarcane is classified as a sheetal (cooling) and balancing food. Please consume responsibly if you are managing blood sugar levels.',
    'active', 3
),
(
    'Events',
    'What types of events can I hire you for?',
    'We cater for all types of events including Indian weddings, Mehndi nights, Sangeet evenings, Baraat receptions, Eid parties, Diwali celebrations, Vaisakhi melas, birthdays, corporate gatherings, festivals, and community events across the UK. Our live pressing stall is always the star of the show!',
    'active', 4
),
(
    'Events',
    'How much does it cost to hire for an event?',
    'Pricing is customised based on your event size, location, duration, and the number of guests. We offer competitive packages for intimate private gatherings of 30 guests right up to large-scale melas and corporate events. Contact us for a personalised quote — we always work to accommodate your budget.',
    'active', 5
),
(
    'Events',
    'How much notice do you need for event bookings?',
    'We recommend booking at least 2–4 weeks in advance to secure your preferred date, especially during peak wedding and festival season (April–October) when our calendar fills quickly. Do reach out even at shorter notice and we will do our very best to accommodate you.',
    'active', 6
),
(
    'Juice',
    'What is the difference between Yellow Cane and Red Cane?',
    'Yellow Cane produces a lighter, more refreshing golden juice with a clean, mild sweetness — similar to the most common ganna ras you find across North India. Red Cane (+£0.50) is naturally richer with a deeper amber colour and a more intense, almost molasses-like sweetness. Both are 100% natural with no additives.',
    'active', 7
),
(
    'Juice',
    'What flavour blends do you offer?',
    'We offer Pure Cane (natural, included), Citrus Blends including Lemon, Ginger (adrak), Lemon & Ginger, and Mint (+£0.50 each), and Tropical Blends including Pineapple, Watermelon, Strawberry, and Blueberry Burst (+£1.00 each). The Ginger and Lemon & Ginger blends are especially popular with our South Asian customers — a nod to the classic nimbu-adrak ganna ras!',
    'active', 8
),
(
    'General',
    'Is your sugarcane sustainable?',
    'Yes! Sugarcane is one of the most sustainable crops on earth. Even our leftover fibre (bagasse — the same by-product used to make eco-friendly packaging in India) is completely biodegradable. We are committed to responsible, eco-conscious serving practices across all our events.',
    'active', 9
),
(
    'Franchise',
    'How can I become a franchise partner?',
    'We warmly welcome franchise enquiries from across the UK — especially from those with roots in South Asian communities where sugarcane has always been cherished. Whether you want to run a permanent stall, a mobile unit, or an events-focused operation in your city, we have a model for you. Call +44 7887 699 208 or use the contact form.',
    'active', 10
);

-- ── News Bar / Marquee (The Cane House messaging) ─────────────────────────────
INSERT INTO `{prefix}news_bar`
    (`message`, `status`, `sort_order`)
VALUES
('✦ Fresh ganna ras — no added sugar, no preservatives, pressed live at every order', 'active', 1),
('✦ Now hiring for our Vaisakhi & Eid season — book your event stall today', 'active', 2),
('✦ Lemon & Ginger blend — the classic nimbu-adrak combination, now available year-round', 'active', 3),
('✦ Franchise opportunities available in Southall, Leicester, Birmingham & Manchester', 'active', 4),
('✦ New: Group Sharing 1.5L — perfect for family gatherings and mehndi nights', 'active', 5);
-- ============================================================
-- WordPress Options are set via the PHP seeder (seeder.php)
-- ============================================================
