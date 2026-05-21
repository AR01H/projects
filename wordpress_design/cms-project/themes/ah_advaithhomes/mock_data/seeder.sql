-- ============================================================
-- Advaith Homes - Data Seeder (CMS Plugin Tables)
-- ============================================================
-- Uses the real plugin table structure (wp_ah_* prefix).
-- Default WP prefix: wp_ — change all occurrences if yours differs.
-- Safe to re-run: settings use ON DUPLICATE KEY UPDATE,
-- services use INSERT IGNORE (unique slug), others skip if
-- data already exists via the NOT EXISTS guard.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Site Settings ─────────────────────────────────────────────────────────
-- Inserts contact info, hero copy, and JSON blobs for process steps,
-- site stats, and trust signals. Re-run safe via ON DUPLICATE KEY UPDATE.
INSERT INTO `wp_ah_site_settings` (setting_key, setting_val, field_type, group_name, label)
VALUES
  ('phone',            '+44 7747 223762',                                          'phone',    'contact', 'Phone'),
  ('email',            'contact@advaithhomes.co.uk',                               'email',    'contact', 'Email'),
  ('address',          'London & Nationwide',                                      'textarea', 'contact', 'Address'),
  ('consultation_url', '/contact/',                                                'url',      'contact', 'Consultation URL'),
  ('map_embed_url',    'https://maps.google.com/maps?q=London,UK&output=embed&z=12','url',     'contact', 'Map Embed URL'),
  ('tagline',          'The UK''s buyer''s agent - working exclusively for you.',  'text',     'general', 'Tagline'),
  ('hero_headline',    'Make Smarter<br><em>Property Decisions</em>',              'text',     'home',    'Hero Headline'),
  ('hero_subline',     'Navigating the UK housing market can be complex, but having access to the right information makes all the difference. With unbiased market data, expert guidance, and practical tools, you can make confident property decisions based on facts rather than speculation. Whether you''re buying your first home, investing, or simply exploring the market, our insights help you better understand trends, pricing, and opportunities across the UK.', 'textarea', 'home', 'Hero Subline'),
  ('hero_cta_label',   'Book a Free Consultation',                                 'text',     'home',    'Hero CTA Label'),
  ('hero_cta_url',     '/contact/',                                                'url',      'home',    'Hero CTA URL'),
  ('hero_stat_1',      '£28M+',                                                    'text',     'home',    'Hero Stat 1'),
  ('hero_stat_1_label','Saved for clients',                                        'text',     'home',    'Hero Stat 1 Label'),
  ('hero_stat_2',      '94%',                                                      'text',     'home',    'Hero Stat 2'),
  ('hero_stat_2_label','Off-market success rate',                                  'text',     'home',    'Hero Stat 2 Label'),
  ('hero_stat_3',      '500+',                                                     'text',     'home',    'Hero Stat 3'),
  ('hero_stat_3_label','Homes secured',                                            'text',     'home',    'Hero Stat 3 Label'),
  ('hero_stat_4',      '4.9★',                                                     'text',     'home',    'Hero Stat 4'),
  ('hero_stat_4_label','Average client rating',                                    'text',     'home',    'Hero Stat 4 Label'),
  ('process_steps',
   '[{"num":"01","title":"Free Consultation","desc":"We learn your brief - budget, location, must-haves, timeline. No obligation, no pressure."},{"num":"02","title":"Property Search","desc":"We activate our network - estate agents, developers, and off-market connections - to source matched properties."},{"num":"03","title":"Shortlisting","desc":"We visit, assess, and report on every property before you see it. You only view the best 3-5 options."},{"num":"04","title":"Offer & Negotiation","desc":"We advise on value and negotiate hard. Our data-backed approach regularly achieves below-asking results."},{"num":"05","title":"Due Diligence","desc":"Planning checks, flood risk, structural surveys, local searches - we dig deep before you commit."},{"num":"06","title":"Completion Day","desc":"We manage solicitors, lenders, and agents to the finish line. You just need to pick up the keys."}]',
   'json', 'home', 'Process Steps'),
  ('site_stats',
   '[{"num":"£28M+","label":"Saved for clients in negotiations"},{"num":"500+","label":"Homes successfully secured"},{"num":"94%","label":"Clients access off-market properties"},{"num":"4.9★","label":"Average client satisfaction rating"}]',
   'json', 'general', 'Site Stats'),
  ('trust_signals',
   '[{"icon":"⭐","text":"4.9/5 average rating from 500+ clients"},{"icon":"🔍","text":"94% of clients secure off-market properties"},{"icon":"💰","text":"Average saving of £14,200 per purchase"},{"icon":"🇬🇧","text":"Covering all of England & Wales"},{"icon":"🤝","text":"We only work for buyers - never sellers"}]',
   'json', 'general', 'Trust Signals')
ON DUPLICATE KEY UPDATE
  setting_val = VALUES(setting_val),
  field_type  = VALUES(field_type),
  group_name  = VALUES(group_name),
  label       = VALUES(label);

-- ── News Bar Items ────────────────────────────────────────────────────────
-- Skip if rows already exist.
INSERT INTO `wp_ah_news_bar_items` (text, status, sort_order)
SELECT * FROM (
  SELECT '✦ Mortgage rates update: average 5-year fix now at 4.2% - our guide explains what this means for buyers' AS text, 'active' AS status, 1 AS sort_order UNION ALL
  SELECT '✦ Off-market deals available now in London, Bristol, and Manchester - speak to our team today',             'active', 2 UNION ALL
  SELECT '✦ Stamp duty relief for first-time buyers extended - check our calculator for your savings',               'active', 3 UNION ALL
  SELECT '✦ New: Free 30-minute consultation with a buyer''s agent - limited slots available this week',             'active', 4 UNION ALL
  SELECT '✦ Q1 2025: Average negotiation saving for Advaith clients was £14,200',                                   'active', 5
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `wp_ah_news_bar_items` LIMIT 1);

-- ── Services ──────────────────────────────────────────────────────────────
-- slug has a UNIQUE KEY so INSERT IGNORE is safe to re-run.
INSERT IGNORE INTO `wp_ah_services` (title, slug, short_desc, sort_order, status) VALUES
  ('Property Search & Sourcing',    'property-search-sourcing',     'We access the full market - including off-market and pre-market properties - to find homes that match your exact brief.',                                                                             1, 'active'),
  ('Negotiation & Offer Strategy',  'negotiation-offer-strategy',   'Our agents have negotiated hundreds of purchases and know how to position your offer to win at the right price - often saving clients 3-8%.',                                                         2, 'active'),
  ('Due Diligence & Research',      'due-diligence-research',       'We dig deep into every property - planning history, flood risk, structural issues, and comparable sales - so you buy with full confidence.',                                                           3, 'active'),
  ('Legal & Survey Coordination',   'legal-survey-coordination',    'We manage solicitors, surveyors, and mortgage brokers so you never have to chase anyone. One point of contact from offer to completion.',                                                             4, 'active'),
  ('Relocation Service',            'relocation-service',           'Moving from overseas or another city? We cover everything from area research and school catchments to removals coordination and council registration.',                                               5, 'active'),
  ('Investment Portfolio Building', 'investment-portfolio-building', 'Building a buy-to-let portfolio? We source high-yield properties, analyse rental returns, and structure your acquisitions for long-term growth.',                                                     6, 'active');

-- ── Team Members ─────────────────────────────────────────────────────────
-- Skip if rows already exist.
INSERT INTO `wp_ah_team_members` (name, designation, bio, sort_order, status)
SELECT * FROM (
  SELECT 'James Whitfield' AS name, 'Founder & Lead Buyer''s Agent' AS designation, '15 years sourcing off-market properties across London and the South East. Former senior negotiator at a top-5 estate agency.' AS bio, 1 AS sort_order, 'active' AS status UNION ALL
  SELECT 'Priya Sharma',    'Senior Property Analyst',      'MRICS-qualified specialist in development site analysis and planning research. Particular expertise in new-build negotiations.',                                        2, 'active' UNION ALL
  SELECT 'Tom Harding',     'Relocation Specialist',         'Helps international buyers and city movers find homes outside London. Covers the Midlands, North West, and South West.',                                              3, 'active' UNION ALL
  SELECT 'Anika Patel',     'Client Operations Manager',    'Keeps every transaction on track from offer to keys. Manages solicitor chains, surveyor bookings, and lender timelines.',                                              4, 'active'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `wp_ah_team_members` LIMIT 1);

-- ── Reviews ───────────────────────────────────────────────────────────────
-- short_desc column added by plugin migration (ensure_review_short_desc).
-- Skip if rows already exist.
INSERT INTO `wp_ah_reviews` (reviewer_name, reviewer_title, review_text, short_desc, rating, source, is_featured, sort_order, status)
SELECT * FROM (
  SELECT 'Sarah & Marcus T.'    AS reviewer_name,
         'First-time buyers - East London'   AS reviewer_title,
         'James found us a flat off-market, negotiated £18,000 off the asking price, and coordinated everything. Completion was 7 weeks from offer. Cannot recommend enough.' AS review_text,
         'Saved £18,000 vs asking price'     AS short_desc,
         5 AS rating, 'manual' AS source, 1 AS is_featured, 1 AS sort_order, 'active' AS status
  UNION ALL
  SELECT 'Dr. Ravi Menon', 'Relocating from Dubai - Surrey',
         'Bought a 5-bedroom home in Surrey entirely remotely. Priya handled every site visit, due diligence report, and legal question on my behalf. Flawless service.',
         'Full remote purchase completed', 5, 'manual', 1, 2, 'active'
  UNION ALL
  SELECT 'Claire Ashworth', 'Buy-to-let investor - Manchester',
         'Third property I''ve bought through Advaith. Average yield across my portfolio is 7.4%. They genuinely understand investment criteria, not just nice homes.',
         '7.4% avg portfolio yield', 5, 'manual', 0, 3, 'active'
  UNION ALL
  SELECT 'Henry & Jo Blackwell', 'Upsizing - Bristol',
         'After two failed offers on homes we found ourselves, we brought in Advaith. They found a better property and we got it first time. Three months of stress gone in three weeks.',
         'Secured on first offer', 5, 'manual', 0, 4, 'active'
  UNION ALL
  SELECT 'Anoushka Reid', 'New build purchase - Canary Wharf',
         'Developers are very savvy. Tom explained what was negotiable and got a parking space, £5k in extras, and a better completion date included. The fee paid for itself three times over.',
         '£5k+ in developer extras negotiated', 5, 'manual', 0, 5, 'active'
  UNION ALL
  SELECT 'David & Lisa Okonkwo', 'Family home - Hertfordshire',
         'School catchments, planning applications, flood risk, Japanese knotweed - they checked everything. We bought knowing exactly what we were getting. No surprises after completion.',
         'Full due diligence on family home', 5, 'manual', 0, 6, 'active'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `wp_ah_reviews` LIMIT 1);

-- ── FAQs ──────────────────────────────────────────────────────────────────
-- No topic column in plugin table (use page_id to group if needed).
-- Skip if rows already exist.
INSERT INTO `wp_ah_faqs` (question, answer, sort_order, status)
SELECT * FROM (
  SELECT 'What is a buyer''s agent?'                              AS question,
         'A buyer''s agent works exclusively on behalf of the buyer - not the seller. Unlike an estate agent paid by the seller to achieve the highest price, we are paid by you to find the right property at the best possible price.' AS answer,
         1 AS sort_order, 'active' AS status
  UNION ALL
  SELECT 'How much does a buyer''s agent cost?',
         'Our fee structure is transparent: a retainer to begin the search, and a success fee (typically 1-2.5% of purchase price) on completion. We offer a free 30-minute consultation to explain costs with no obligation.',
         2, 'active'
  UNION ALL
  SELECT 'Can I use a buyer''s agent as a first-time buyer?',
         'Absolutely. First-time buyers benefit enormously - you get expert guidance on every step of a process that''s unfamiliar, and we often save clients far more than our fee through negotiation alone.',
         3, 'active'
  UNION ALL
  SELECT 'How do you find off-market properties?',
         'We maintain active relationships with estate agents, developers, property solicitors, and institutional landlords. Many properties are offered to us before they go to Rightmove - sometimes weeks before.',
         4, 'active'
  UNION ALL
  SELECT 'How long does the process take?',
         'From instruction to completion, most clients are done in 3-6 months. We''ve completed some relocations in under 8 weeks. Timeline depends on your requirements and market conditions in your target area.',
         5, 'active'
  UNION ALL
  SELECT 'Do you cover the whole of the UK?',
         'Yes - we work nationally. We have specialist knowledge in London, the South East, the Midlands, and the North West, with agents on the ground in each region.',
         6, 'active'
  UNION ALL
  SELECT 'Should I get a mortgage in principle before searching?',
         'Yes, always. A mortgage in principle (MIP) shows sellers and estate agents you are a serious buyer. We can refer you to independent mortgage brokers who access the whole market.',
         7, 'active'
  UNION ALL
  SELECT 'What is stamp duty and how much will I pay?',
         'Stamp Duty Land Tax (SDLT) is a tax on property purchases in England. Rates vary depending on purchase price, whether it''s your main home, a second property, or a first-time purchase. Use our stamp duty calculator for an estimate.',
         8, 'active'
  UNION ALL
  SELECT 'What does conveyancing involve?',
         'Conveyancing is the legal transfer of a property from seller to buyer. Your solicitor conducts searches, reviews contracts, manages exchange of funds, and registers the property in your name at the Land Registry.',
         9, 'active'
  UNION ALL
  SELECT 'Do I need a survey?',
         'For any property built before the 1990s, we strongly recommend a Level 2 or Level 3 structural survey. A £600 survey can identify £20,000 of hidden issues. We coordinate surveys and help you interpret the report.',
         10, 'active'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `wp_ah_faqs` LIMIT 1);

-- ── WordPress Options (nav data) ──────────────────────────────────────────
-- Guide nav, categories, and header dropdown topics stored as JSON options.
INSERT INTO `wp_options` (option_name, option_value, autoload)
VALUES
  ('ah_guide_nav',
   '[{"icon":"🏠","title":"First-Time Buyers","slug":"first-time-buyers","desc":"Complete step-by-step guide"},{"icon":"🔑","title":"Moving Home","slug":"moving-home","desc":"What changes when you upsize"},{"icon":"🏘️","title":"Buy-to-Let","slug":"buy-to-let","desc":"Investor buying strategy"},{"icon":"🔍","title":"Off-Market Properties","slug":"off-market","desc":"Homes not on Rightmove"},{"icon":"🏗️","title":"New Builds","slug":"new-builds","desc":"Developer deals & pitfalls"},{"icon":"🤝","title":"Using a Buyer''s Agent","slug":"buyers-agent","desc":"What we do & why it works","highlight":true},{"icon":"🏦","title":"Mortgage Guide","slug":"mortgage-guide","desc":"Rates, types & best deals"},{"icon":"💰","title":"Deposit Guide","slug":"deposit-guide","desc":"How much do you really need?"},{"icon":"📋","title":"Stamp Duty Guide","slug":"stamp-duty","desc":"2025 rates & exemptions"}]',
   'yes'),
  ('ah_guide_categories',
   '[{"icon":"🏠","title":"Buying Guides","desc":"Step-by-step guides to buying your first, next, or investment property.","count":12,"slug":"buying"},{"icon":"🏦","title":"Finance & Mortgages","desc":"Understand mortgage options, deposit requirements, stamp duty, and costs.","count":8,"slug":"finance"},{"icon":"⚖️","title":"Legal & Surveys","desc":"Conveyancing, property surveys, legal searches, and what happens after.","count":7,"slug":"legal"},{"icon":"🔑","title":"Moving & Settling","desc":"Area research, school catchments, removal companies, and utilities.","count":6,"slug":"moving"}]',
   'yes'),
  ('ah_nav_buying_topics',
   '[{"icon":"🏠","title":"First-Time Buyers","desc":"Complete step-by-step guide","slug":"first-time-buyers"},{"icon":"🔑","title":"Moving Home","desc":"What changes when you upsize","slug":"moving-home"},{"icon":"🏘️","title":"Buy-to-Let","desc":"Investor buying strategy","slug":"buy-to-let"},{"icon":"🔍","title":"Off-Market Properties","desc":"Homes not on Rightmove","slug":"off-market"},{"icon":"🏗️","title":"New Builds","desc":"Developer deals & pitfalls","slug":"new-builds"},{"icon":"🤝","title":"Using a Buyer''s Agent","desc":"What we do & why it works","slug":"buyers-agent","highlight":true}]',
   'yes'),
  ('ah_nav_finance_topics',
   '[{"icon":"🏦","title":"Mortgage Guide","desc":"Rates, types & best deals","slug":"mortgage-guide"},{"icon":"💰","title":"Deposit Guide","desc":"How much do you really need?","slug":"deposit-guide"},{"icon":"📋","title":"Stamp Duty Guide","desc":"2025 rates & exemptions","slug":"stamp-duty"},{"icon":"🧮","title":"Cost Calculator","desc":"Hidden costs of buying","slug":"price-calculator","highlight":true}]',
   'yes'),
  ('ah_nav_legal_topics',
   '[{"icon":"⚖️","title":"Legal Search Packs","desc":"What''s hidden in the paperwork","slug":"legal-search"},{"icon":"📄","title":"Conveyancing Guide","desc":"The legal process explained","slug":"conveyancing"},{"icon":"🔬","title":"Survey Types","desc":"Which survey do you need?","slug":"surveys"},{"icon":"📊","title":"Property Research","desc":"Deep analysis before you buy","slug":"property-research"}]',
   'yes')
ON DUPLICATE KEY UPDATE
  option_value = VALUES(option_value);

SET FOREIGN_KEY_CHECKS = 1;
