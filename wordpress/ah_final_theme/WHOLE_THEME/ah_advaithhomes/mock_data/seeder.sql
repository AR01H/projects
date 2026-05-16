-- ============================================================
-- AH Advaith Homes — Mock Data Seeder
-- ============================================================
-- Table prefix: replace {prefix} with your WordPress prefix
-- e.g. if your WP prefix is "wp_", tables are:
--   wp_ah_cms_plug_services, wp_ah_cms_plug_team, etc.
-- If using the default WP prefix and TABLE_MID_FIX='_cms_plug_':
--   Replace {prefix} → wp_ah_cms_plug_
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ── Services ─────────────────────────────────────────────────────────────────
INSERT INTO `{prefix}services` (`title`, `summary`, `icon`, `status`, `sort_order`) VALUES
('Property Search & Sourcing',    'We access the full market — including off-market and pre-market properties — to find homes that match your exact brief.',                                                                    '🔍', 'active', 1),
('Negotiation & Offer Strategy',  'Our agents have negotiated hundreds of purchases and know how to position your offer to win at the right price — often saving clients 3–8% of the purchase price.',                         '🤝', 'active', 2),
('Due Diligence & Research',      'We dig deep into every property — planning history, flood risk, structural issues, and comparable sales — so you buy with full confidence.',                                                 '📋', 'active', 3),
('Legal & Survey Coordination',   'We manage solicitors, surveyors, and mortgage brokers so you never have to chase anyone. One point of contact from offer to completion.',                                                    '⚖️', 'active', 4),
('Relocation Service',            'Moving from overseas or another city? We cover everything from area research and school catchments to removals coordination and council registration.',                                       '✈️', 'active', 5),
('Investment Portfolio Building', 'Building a buy-to-let portfolio? We source high-yield properties, analyse rental returns, and structure your acquisitions for long-term growth.',                                             '📈', 'active', 6);

-- ── Team ─────────────────────────────────────────────────────────────────────
INSERT INTO `{prefix}team` (`name`, `role`, `bio`, `photo_url`, `status`, `sort_order`) VALUES
('James Whitfield', 'Founder & Lead Buyer''s Agent', '15 years sourcing off-market properties across London and the South East. Former senior negotiator at a top-5 estate agency before founding Advaith Homes.',               '', 'active', 1),
('Priya Sharma',    'Senior Property Analyst',        'MRICS-qualified specialist in development site analysis and planning research. Particular expertise in new-build negotiations and developer contract review.',              '', 'active', 2),
('Tom Harding',     'Relocation Specialist',           'Helps international buyers and city movers find and secure homes outside London. Covers the Midlands, North West, and South West.',                                       '', 'active', 3),
('Anika Patel',     'Client Operations Manager',      'Keeps every transaction on track from offer to keys. Manages solicitor chains, surveyor bookings, and lender timelines so clients stay stress-free.',                     '', 'active', 4);

-- ── Reviews ──────────────────────────────────────────────────────────────────
INSERT INTO `{prefix}reviews` (`author_name`, `location`, `review_text`, `rating`, `result`, `status`) VALUES
('Sarah & Marcus T.',    'First-time buyers — East London',    'James found us a flat off-market, negotiated £18,000 off the asking price, and coordinated everything. Completion was 7 weeks from offer. Cannot recommend enough.',                     5.0, 'Saved £18,000 vs asking price',     'active'),
('Dr. Ravi Menon',       'Relocating from Dubai — Surrey',     'Bought a 5-bedroom home in Surrey entirely remotely. Priya handled every site visit, due diligence report, and legal question on my behalf. Flawless service.',                         5.0, 'Full remote purchase completed',     'active'),
('Claire Ashworth',      'Buy-to-let investor — Manchester',   'Third property I''ve bought through Advaith. Average yield across my portfolio is 7.4%. They genuinely understand investment criteria, not just nice homes.',                            5.0, '7.4% avg portfolio yield',          'active'),
('Henry & Jo Blackwell', 'Upsizing — Bristol',                 'After two failed offers on homes we found ourselves, we brought in Advaith. They found a better property and we got it first time. Three months of stress gone in three weeks.',          5.0, 'Secured on first offer',             'active'),
('Anoushka Reid',        'New build purchase — Canary Wharf',  'Developers are very savvy. Tom got a parking space, £5k in extras, and a better completion date included. The fee paid for itself three times over.',                                    5.0, '£5k+ in developer extras negotiated','active'),
('David & Lisa Okonkwo', 'Family home — Hertfordshire',        'School catchments, planning applications, flood risk — they checked everything. We bought knowing exactly what we were getting. No surprises after completion.',                           5.0, 'Full due diligence on family home',  'active');

-- ── FAQs ─────────────────────────────────────────────────────────────────────
INSERT INTO `{prefix}faqs` (`topic`, `question`, `answer`, `status`, `sort_order`) VALUES
('General', 'What is a buyer''s agent?',                             'A buyer''s agent works exclusively on behalf of the buyer — not the seller. Unlike an estate agent paid by the seller to achieve the highest price, we are paid by you to find the right property at the best possible price.',            'active', 1),
('General', 'How much does a buyer''s agent cost?',                  'Our fee structure is transparent: a retainer to begin the search, and a success fee (typically 1–2.5% of purchase price) on completion. We offer a free 30-minute consultation to explain costs with no obligation.',                    'active', 2),
('General', 'Can I use a buyer''s agent as a first-time buyer?',     'Absolutely. First-time buyers benefit enormously — you get expert guidance on every step of a process that''s unfamiliar, and we often save clients far more than our fee through negotiation alone.',                                    'active', 3),
('Process', 'How do you find off-market properties?',                'We maintain active relationships with estate agents, developers, property solicitors, and institutional landlords. Many properties are offered to us before they go to Rightmove — sometimes weeks before.',                               'active', 4),
('Process', 'How long does the process take?',                       'From instruction to completion, most clients are done in 3–6 months. We have completed some relocations in under 8 weeks. Timeline depends on your requirements and market conditions in your target area.',                               'active', 5),
('Process', 'Do you cover the whole of the UK?',                    'Yes — we work nationally. We have specialist knowledge in London, the South East, the Midlands, and the North West, with agents on the ground in each region.',                                                                            'active', 6),
('Finance', 'Should I get a mortgage in principle before searching?', 'Yes, always. A mortgage in principle shows sellers and estate agents you are a serious buyer. We can refer you to independent mortgage brokers who access the whole market.',                                                             'active', 7),
('Finance', 'What is stamp duty and how much will I pay?',           'Stamp Duty Land Tax (SDLT) is a tax on property purchases in England. Rates vary depending on purchase price and buyer type. Use our stamp duty calculator for a personalised estimate.',                                                 'active', 8),
('Legal',   'What does conveyancing involve?',                       'Conveyancing is the legal transfer of a property. Your solicitor conducts searches, reviews contracts, manages exchange of funds, and registers the property in your name at the Land Registry.',                                          'active', 9),
('Legal',   'Do I need a survey?',                                   'For any property built before the 1990s, we strongly recommend a Level 2 or Level 3 structural survey. A £600 survey can identify £20,000 of hidden issues. We coordinate surveys and help you interpret the report.',                    'active', 10);

-- ── News Bar ─────────────────────────────────────────────────────────────────
INSERT INTO `{prefix}news_bar` (`message`, `status`, `sort_order`) VALUES
('✦ Mortgage rates update: average 5-year fix now at 4.2% — our guide explains what this means for buyers', 'active', 1),
('✦ Off-market deals available now in London, Bristol, and Manchester — speak to our team today',           'active', 2),
('✦ Stamp duty relief for first-time buyers extended — check our calculator for your savings',              'active', 3),
('✦ New: Free 30-minute consultation with a buyer''s agent — limited slots available this week',            'active', 4),
('✦ Q1 2025: Average negotiation saving for Advaith Homes clients was £14,200',                            'active', 5);

-- ── WordPress Options (run via PHP, not SQL) ──────────────────────────────────
-- The following options are best inserted via the PHP seeder (seeder.php).
-- They use JSON and require wp_json_encode / update_option.
-- Options:
--   ah_site_settings, ah_home_settings, ah_guide_nav, ah_guide_categories,
--   ah_nav_buying_topics, ah_nav_finance_topics, ah_nav_legal_topics,
--   ah_process_steps, ah_site_stats
-- ─────────────────────────────────────────────────────────────────────────────
