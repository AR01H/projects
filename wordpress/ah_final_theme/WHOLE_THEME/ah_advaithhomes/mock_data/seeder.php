<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH Theme Seeder
 * Populates all CMS tables and WordPress options with realistic demo data.
 * Triggered from Theme Admin → Install Mock Data.
 */
class AH_Theme_Seeder {

	// ── Table creation (run before seeding when plugin is not active) ────────────

	public static function create_tables(): void {
		global $wpdb;
		$cs = $wpdb->get_charset_collate();

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'services' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			title      VARCHAR(255) NOT NULL,
			summary    TEXT,
			icon       VARCHAR(100),
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'team' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name       VARCHAR(200) NOT NULL,
			role       VARCHAR(200),
			bio        TEXT,
			photo_url  VARCHAR(500),
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'reviews' ) . "` (
			id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			author_name VARCHAR(200) NOT NULL,
			location    VARCHAR(200),
			review_text TEXT NOT NULL,
			rating      TINYINT UNSIGNED DEFAULT 5,
			result      VARCHAR(200),
			status      ENUM('active','inactive') DEFAULT 'active',
			created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'faqs' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			topic      VARCHAR(150),
			question   TEXT NOT NULL,
			answer     TEXT NOT NULL,
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ah_theme_table( 'news_bar' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			message    VARCHAR(500) NOT NULL,
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/** @return array{inserted:int, updated:int, errors:string[]} */
	public static function seed_all(): array {
		self::create_tables();
		$results = [ 'inserted' => 0, 'updated' => 0, 'errors' => [] ];
		$methods = [
			'seed_settings',
			'seed_home_settings',
			'seed_guide_nav',
			'seed_guide_categories',
			'seed_nav_topics',
			'seed_process_steps',
			'seed_site_stats',
			'seed_trust_signals',
			'seed_news_bar',
			'seed_services',
			'seed_team',
			'seed_reviews',
			'seed_faqs',
			'seed_properties',
			'seed_contact_settings',
			'seed_blog_posts',
			'seed_static_pages',
		];
		foreach ( $methods as $method ) {
			try {
				$r = self::$method();
				$results['inserted'] += $r['inserted'] ?? 0;
				$results['updated']  += $r['updated']  ?? 0;
			} catch ( \Throwable $e ) {
				$results['errors'][] = "{$method}: " . $e->getMessage();
			}
		}
		return $results;
	}

	/** @return array{inserted:int,updated:int} */
	public static function seed_settings(): array {
		update_option( 'ah_site_settings', wp_json_encode( [
			'phone'            => '+44 7747 223762',
			'email'            => 'contact@advaithhomes.co.uk',
			'address'          => 'London & Nationwide',
			'facebook_url'     => 'https://facebook.com/advaithhomes',
			'instagram_url'    => 'https://instagram.com/advaithhomes',
			'twitter_url'      => 'https://twitter.com/advaithhomes',
			'linkedin_url'     => 'https://linkedin.com/company/advaithhomes',
			'youtube_url'      => '',
			'consultation_url' => '/contact/',
			'tagline'          => "The UK's buyer's agent — working exclusively for you.",
		] ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_home_settings(): array {
		update_option( 'ah_home_settings', wp_json_encode( [
			'hero_headline'      => "Your Expert on the<br><em>Buying Side</em>",
			'hero_subline'       => "The UK's only buyer's agent combining deep market access, expert negotiation, and end-to-end coordination — so you buy the right property at the right price.",
			'hero_cta_label'     => 'Book a Free Consultation',
			'hero_cta_url'       => '/contact/',
			'hero_stat_1'        => '£28M+',
			'hero_stat_1_label'  => 'Saved for clients',
			'hero_stat_2'        => '94%',
			'hero_stat_2_label'  => 'Off-market success rate',
			'hero_stat_3'        => '500+',
			'hero_stat_3_label'  => 'Homes secured',
			'hero_stat_4'        => '4.9★',
			'hero_stat_4_label'  => 'Average client rating',
		] ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_guide_nav(): array {
		update_option( 'ah_guide_nav', wp_json_encode( ah_mock_guide_nav() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_guide_categories(): array {
		update_option( 'ah_guide_categories', wp_json_encode( ah_mock_guide_categories_array() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_nav_topics(): array {
		update_option( 'ah_nav_buying_topics',  wp_json_encode( ah_mock_nav_buying_topics() ) );
		update_option( 'ah_nav_finance_topics', wp_json_encode( ah_mock_nav_finance_topics() ) );
		update_option( 'ah_nav_legal_topics',   wp_json_encode( ah_mock_nav_legal_topics() ) );
		return [ 'inserted' => 0, 'updated' => 3 ];
	}

	public static function seed_process_steps(): array {
		update_option( 'ah_process_steps', wp_json_encode( ah_mock_process_steps() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_site_stats(): array {
		update_option( 'ah_site_stats', wp_json_encode( ah_mock_site_stats() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_trust_signals(): array {
		update_option( 'ah_trust_signals', wp_json_encode( ah_mock_trust_signals() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_news_bar(): array {
		global $wpdb;
		$table = ah_theme_table( 'news_bar' );
		if ( ! self::table_exists( $table ) ) {
			update_option( 'ah_news_bar_items', wp_json_encode( ah_mock_news_bar_items() ) );
			return [ 'inserted' => 0, 'updated' => 1 ];
		}
		$items   = ah_mock_news_bar_items();
		$count   = 0;
		foreach ( $items as $i => $msg ) {
			$wpdb->insert( $table, [ 'message' => $msg, 'status' => 'active', 'sort_order' => $i + 1 ] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_services(): array {
		global $wpdb;
		$table = ah_theme_table( 'services' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'services table missing' );
		$rows  = ah_mock_services();
		$count = 0;
		foreach ( $rows as $row ) {
			$wpdb->insert( $table, [
				'title'      => $row->title,
				'summary'    => $row->summary,
				'icon'       => $row->icon,
				'status'     => 'active',
				'sort_order' => $row->sort_order,
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_team(): array {
		global $wpdb;
		$table = ah_theme_table( 'team' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'team table missing' );
		$rows  = ah_mock_team();
		$count = 0;
		foreach ( $rows as $i => $row ) {
			$wpdb->insert( $table, [
				'name'       => $row->name,
				'role'       => $row->role,
				'bio'        => $row->bio,
				'photo_url'  => '',
				'status'     => 'active',
				'sort_order' => $i + 1,
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_reviews(): array {
		global $wpdb;
		$table = ah_theme_table( 'reviews' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'reviews table missing' );
		$rows  = ah_mock_reviews();
		$count = 0;
		foreach ( $rows as $row ) {
			$wpdb->insert( $table, [
				'author_name' => $row->author_name,
				'location'    => $row->location,
				'review_text' => $row->review_text,
				'rating'      => $row->rating,
				'result'      => $row->result,
				'status'      => 'active',
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_faqs(): array {
		global $wpdb;
		$table = ah_theme_table( 'faqs' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'faqs table missing' );
		$rows  = ah_mock_faqs();
		$count = 0;
		foreach ( $rows as $row ) {
			$wpdb->insert( $table, [
				'topic'      => $row->topic,
				'question'   => $row->question,
				'answer'     => $row->answer,
				'status'     => 'active',
				'sort_order' => $row->sort_order ?? 0,
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_properties(): array {
		update_option( 'ah_featured_properties', wp_json_encode( ah_mock_properties() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_contact_settings(): array {
		update_option( 'ah_contact_settings', wp_json_encode( [
			'recipient_email' => get_option( 'admin_email' ),
			'subject_prefix'  => '[Advaith Homes Enquiry]',
			'thank_you_msg'   => "Thanks for getting in touch! We'll respond within one working day.",
			'show_phone'      => true,
			'show_budget'     => true,
			'show_timeline'   => true,
		] ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_blog_posts(): array {
		$posts = [
			[
				'title'   => 'How Long Does Buying a Home in the UK Really Take?',
				'content' => '<p>If you\'ve been told buying a home in the UK takes "about three months", that\'s broadly right — but it tells you almost nothing useful. This guide breaks every week down into what\'s happening, who\'s doing it, and what you can do (and not do) to keep things moving.</p><h2 id="before-week-zero">Before week zero: the work that pays itself back</h2><p>The fastest completions we\'ve ever seen all share one thing — the buyer had their finances mortgage-ready before they even made an offer. That means an Agreement in Principle (AIP) from a lender, a deposit sitting in an account ready to be transferred, and a solicitor on standby.</p><p>An AIP is a soft credit check that tells you what a lender is willing to lend you. It takes 24–48 hours and lasts 60–90 days. It\'s not a binding offer, but it tells estate agents you\'re serious.</p>',
				'excerpt' => 'The complete week-by-week guide to UK property buying timelines — what happens, who does it, and how to avoid delays.',
				'cat'     => 'Buying Guides',
				'featured'=> true,
			],
			[
				'title'   => 'Off-Market Property: What It Is and How to Find It',
				'content' => '<p>Around 25–30% of UK property transactions happen before the home ever reaches Rightmove or Zoopla. These off-market deals go to buyers with the right connections — or the right agent working on their behalf.</p><h2>Why sellers go off-market</h2><p>There are several reasons a seller might prefer a quiet sale: privacy, avoiding the disruption of viewings, or simply because they trust an agent to bring a qualified buyer directly. Probate sales, corporate relocations, and downsizing retirees are common sources.</p>',
				'excerpt' => 'Discover how to access properties that never appear on Rightmove — the buyers who win off-market deals and the agents who find them.',
				'cat'     => 'Buying Guides',
				'featured'=> false,
			],
			[
				'title'   => 'Stamp Duty 2025: The Complete Guide for Buyers',
				'content' => '<p>Stamp Duty Land Tax (SDLT) is one of the largest costs of buying a property in England. The rules changed again in 2024 and the thresholds are different depending on whether you\'re a first-time buyer, moving home, or purchasing an additional property.</p><h2>Current stamp duty rates (2025)</h2><p>For properties purchased as your main home: 0% on the first £250,000; 5% on £250,001–£925,000; 10% on £925,001–£1.5M; 12% above £1.5M.</p>',
				'excerpt' => 'Everything buyers need to know about stamp duty in 2025 — rates, thresholds, first-time buyer relief, and the additional property surcharge.',
				'cat'     => 'Finance',
				'featured'=> false,
			],
		];
		$count = 0;
		foreach ( $posts as $p ) {
			$existing = get_page_by_title( $p['title'], OBJECT, 'post' );
			if ( $existing ) continue;
			$post_id = wp_insert_post( [
				'post_title'   => $p['title'],
				'post_content' => $p['content'],
				'post_excerpt' => $p['excerpt'],
				'post_status'  => 'publish',
				'post_type'    => 'post',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				if ( ! empty( $p['cat'] ) ) wp_set_object_terms( $post_id, $p['cat'], 'category' );
				if ( ! empty( $p['featured'] ) ) update_post_meta( $post_id, '_ah_featured', '1' );
				$count++;
			}
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_static_pages(): array {
		$dir = trailingslashit( get_template_directory() ) . 'static/';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$defs = self::static_page_defs();
		$fc   = 0;
		$pc   = 0;
		foreach ( $defs as $slug => $page ) {
			if ( ! file_exists( $dir . $slug . '.html' ) ) {
				file_put_contents( $dir . $slug . '.html', $page['html'] );
				$fc++;
			}
			$existing = get_page_by_path( $slug );
			if ( ! $existing ) {
				$id = wp_insert_post( [
					'post_title'   => $page['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '',
					'post_excerpt' => $page['excerpt'] ?? '',
				] );
				if ( $id && ! is_wp_error( $id ) ) {
					update_post_meta( $id, '_ah_static_page', $slug );
					update_post_meta( $id, '_wp_page_template', 'template-static-page.php' );
					$pc++;
				}
			} else {
				update_post_meta( $existing->ID, '_ah_static_page', $slug );
				update_post_meta( $existing->ID, '_wp_page_template', 'template-static-page.php' );
			}
		}
		return [ 'inserted' => $fc + $pc, 'updated' => 0 ];
	}

	private static function sp_css(): string {
		return '<style>*{box-sizing:border-box}body{font-family:system-ui,-apple-system,sans-serif;max-width:760px;margin:40px auto;padding:0 24px;color:#1e293b;line-height:1.7}h1{font-size:1.75rem;font-weight:800;margin-bottom:6px}h2{font-size:1.1rem;font-weight:700;margin:26px 0 8px;color:#0f172a}p.sub{color:#64748b;margin:0 0 24px}.card{background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.05)}label{display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;color:#374151}input[type=number],select{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:1rem;margin-bottom:18px;font-family:inherit}input:focus,select:focus{border-color:#b7791f;outline:none;box-shadow:0 0 0 3px rgba(183,121,31,.12)}.btn{width:100%;background:#b7791f;color:#fff;border:none;padding:13px;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;font-family:inherit}.btn:hover{background:#7c4a08}.res{background:linear-gradient(135deg,#b7791f,#7c4a08);color:#fff;border-radius:12px;padding:22px;margin-top:16px;display:none}.amt{font-size:2rem;font-weight:800;margin:4px 0}.brk{font-size:.875rem;opacity:.9;margin-top:10px;line-height:1.9}.badge{display:inline-block;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:20px;padding:3px 12px;font-size:.75rem;font-weight:700;margin-bottom:20px}table{width:100%;border-collapse:collapse;font-size:.875rem}th,td{padding:9px 14px;border-bottom:1px solid #f1f5f9;text-align:left}th{font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8}tr:last-child td{border:none}.ci{display:flex;align-items:baseline;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9}.ci input[type=checkbox]{width:16px;height:16px;flex-shrink:0;accent-color:#b7791f;cursor:pointer;margin-top:2px}.ci label{font-size:.9rem;cursor:pointer;color:#374151}dt{font-weight:700;color:#0f172a;margin-top:18px}dd{color:#64748b;margin:4px 0 0 12px;padding-left:12px;border-left:3px solid #fde68a;font-size:.875rem}.sec{background:#f8fafc;border-radius:8px;padding:16px 20px;margin:12px 0}.sec p{color:#374151;margin:4px 0 0}@media(max-width:600px){h1{font-size:1.35rem}.amt{font-size:1.6rem}}</style>';
	}

	private static function sp_page( string $title, string $body, string $badge = '' ): string {
		$b = $badge ? '<span class="badge">' . htmlspecialchars( $badge, ENT_QUOTES, 'UTF-8' ) . '</span>' : '';
		return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'
			. htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ) . ' | Advaith Homes</title>'
			. self::sp_css() . '</head><body>' . $b . $body . '</body></html>';
	}

	private static function static_page_defs(): array {
		return [

			'stamp-duty-calculator' => [
				'title'   => 'Stamp Duty Calculator',
				'excerpt' => 'Calculate your stamp duty land tax for any UK property purchase, updated for 2025.',
				'html'    => self::sp_page(
					'Stamp Duty Calculator',
					'<h1>Stamp Duty Calculator 2025</h1><p class="sub">Calculate your Stamp Duty Land Tax (SDLT) instantly. Updated for 2025 thresholds — covers standard purchases, first-time buyers, and additional properties.</p><div class="card"><label for="sp">Property price (&pound;)</label><input type="number" id="sp" placeholder="e.g. 450000" min="0" step="1000"><label for="st">Buyer type</label><select id="st"><option value="std">Standard buyer (main home)</option><option value="ftb">First-time buyer</option><option value="btl">Additional property / Buy-to-let</option></select><button class="btn" onclick="sdlt()">Calculate Stamp Duty</button><div class="res" id="sr"><p style="opacity:.8;font-size:.875rem;margin:0">Your SDLT liability</p><div class="amt" id="sa"></div><div class="brk" id="sb"></div></div></div><script>function sf(n){return"£"+Math.round(n).toLocaleString("en-GB")}function sdlt(){var p=parseFloat(document.getElementById("sp").value)||0,t=document.getElementById("st").value,x=0,ls=[];if(t==="ftb"&&p<=425e3){ls=["No SDLT — first-time buyer relief applies (up to £425,000)"]}else if(t==="ftb"&&p<=625e3){x=(p-425e3)*.05;ls=["5% on "+sf(p-425e3)+" above £425k limit = "+sf(x)]}else{var bs=t==="btl"?[[25e4,.05],[925e3,.1],[15e5,.15],[1e18,.17]]:[[25e4,0],[925e3,.05],[15e5,.1],[1e18,.12]],pr=0;bs.forEach(function(b){if(p>pr){var c=Math.min(p,b[0])-pr,v=c*b[1];x+=v;if(v>0)ls.push(b[1]*100+"% on "+sf(c)+" = "+sf(v));pr=b[0]}})}if(t==="btl")ls.unshift("3% surcharge applies (additional / BTL property)");document.getElementById("sa").textContent=sf(x);document.getElementById("sb").innerHTML=ls.join("<br>")||"No stamp duty payable";document.getElementById("sr").style.display="block"}</script>',
					'Free Calculator'
				),
			],

			'mortgage-calculator' => [
				'title'   => 'Mortgage Calculator',
				'excerpt' => 'Estimate your monthly mortgage repayments and total interest over the term.',
				'html'    => self::sp_page(
					'Mortgage Calculator',
					'<h1>Mortgage Repayment Calculator</h1><p class="sub">Estimate monthly payments and total interest cost. For personalised advice, speak with a qualified mortgage broker.</p><div class="card"><label for="ml">Loan amount (&pound;)</label><input type="number" id="ml" placeholder="e.g. 300000" min="0" step="5000"><label for="mr">Annual interest rate (%)</label><input type="number" id="mr" placeholder="e.g. 4.5" min="0" max="30" step="0.1"><label for="mt">Mortgage term (years)</label><input type="number" id="mt" placeholder="e.g. 25" min="1" max="40" step="1"><button class="btn" onclick="mcalc()">Calculate</button><div class="res" id="mr2"><p style="opacity:.8;font-size:.875rem;margin:0">Monthly repayment</p><div class="amt" id="ma"></div><div class="brk" id="mb"></div></div></div><script>function mf(n){return"£"+Math.round(n).toLocaleString("en-GB")}function mcalc(){var L=parseFloat(document.getElementById("ml").value)||0,R=parseFloat(document.getElementById("mr").value)||0,Y=parseFloat(document.getElementById("mt").value)||0;if(!L||!R||!Y){alert("Please fill in all three fields");return}var r=R/100/12,n=Y*12,pmt=r?L*(r*Math.pow(1+r,n))/(Math.pow(1+r,n)-1):L/n,tot=pmt*n,ti=tot-L;document.getElementById("ma").textContent=mf(pmt)+" / month";document.getElementById("mb").innerHTML="Total repaid: "+mf(tot)+"<br>Total interest: "+mf(ti)+"<br>Loan (capital): "+mf(L);document.getElementById("mr2").style.display="block"}</script>',
					'Free Calculator'
				),
			],

			'first-time-buyer-checklist' => [
				'title'   => 'First-Time Buyer Checklist',
				'excerpt' => 'A step-by-step checklist covering everything first-time buyers need before, during and after purchase.',
				'html'    => self::sp_page(
					'First-Time Buyer Checklist',
					'<h1>First-Time Buyer Checklist</h1><p class="sub">Work through each step in order. Tick items off as you complete them.</p><div class="card"><h2>1. Finances</h2><div class="ci"><input type="checkbox" id="c1"><label for="c1">Check your credit report free via Experian, Equifax, or Credit Karma</label></div><div class="ci"><input type="checkbox" id="c2"><label for="c2">Calculate your total budget: deposit + stamp duty + legal fees + survey costs</label></div><div class="ci"><input type="checkbox" id="c3"><label for="c3">Get an Agreement in Principle (AIP) from a lender or mortgage broker</label></div><div class="ci"><input type="checkbox" id="c4"><label for="c4">Check eligibility for a Lifetime ISA (25% government bonus on savings up to &pound;4,000/year)</label></div><div class="ci"><input type="checkbox" id="c5"><label for="c5">Confirm stamp duty position — no SDLT on first &pound;425k for properties up to &pound;625k</label></div></div><div class="card"><h2>2. Search and View</h2><div class="ci"><input type="checkbox" id="c6"><label for="c6">Register with local estate agents and set up Rightmove / Zoopla alerts</label></div><div class="ci"><input type="checkbox" id="c7"><label for="c7">View at least 5 to 8 properties before making an offer</label></div><div class="ci"><input type="checkbox" id="c8"><label for="c8">Research recent sold prices in target area via Land Registry data</label></div><div class="ci"><input type="checkbox" id="c9"><label for="c9">Ask the vendor why they are selling and how long the property has been listed</label></div></div><div class="card"><h2>3. Offer and Legal</h2><div class="ci"><input type="checkbox" id="c10"><label for="c10">Submit your offer in writing via the estate agent</label></div><div class="ci"><input type="checkbox" id="c11"><label for="c11">Instruct a solicitor or licensed conveyancer immediately after offer acceptance</label></div><div class="ci"><input type="checkbox" id="c12"><label for="c12">Submit full mortgage application within 2 to 3 weeks of offer acceptance</label></div><div class="ci"><input type="checkbox" id="c13"><label for="c13">Book a RICS HomeBuyer Report or full Building Survey</label></div><div class="ci"><input type="checkbox" id="c14"><label for="c14">Review results of local authority, water, and environmental searches</label></div></div><div class="card"><h2>4. Exchange and Completion</h2><div class="ci"><input type="checkbox" id="c15"><label for="c15">Pay exchange deposit (typically 10% of purchase price)</label></div><div class="ci"><input type="checkbox" id="c16"><label for="c16">Agree a completion date with the vendor</label></div><div class="ci"><input type="checkbox" id="c17"><label for="c17">Arrange buildings insurance to begin from date of exchange</label></div><div class="ci"><input type="checkbox" id="c18"><label for="c18">Transfer remaining balance to solicitor and collect your keys!</label></div></div>',
					'Free Guide'
				),
			],

			'property-glossary' => [
				'title'   => 'Property Glossary',
				'excerpt' => 'Plain-English definitions of UK property buying terms from AIP to title deeds.',
				'html'    => self::sp_page(
					'Property Glossary',
					'<h1>UK Property Glossary</h1><p class="sub">Plain-English definitions of terms you will encounter when buying a property in the UK.</p><dl><dt>Agreement in Principle (AIP)</dt><dd>A conditional indication from a lender of how much they will lend. Not a binding offer, but signals to sellers that you are a credible buyer. Also known as a Mortgage in Principle or Decision in Principle.</dd><dt>Chain</dt><dd>A sequence of linked transactions where each purchase depends on another completing simultaneously. Chains collapse if any participant withdraws. Chain-free purchases complete faster and with less risk.</dd><dt>Completion</dt><dd>The final stage of purchase. Ownership transfers, the balance is paid, and keys are handed over. Usually 1 to 4 weeks after exchange of contracts.</dd><dt>Conveyancing</dt><dd>The legal transfer of property ownership from seller to buyer, handled by a solicitor or licensed conveyancer. Typically costs &pound;1,000 to &pound;2,500.</dd><dt>Exchange of Contracts</dt><dd>The stage where both parties sign identical contracts and a deposit (usually 10%) is transferred. The sale becomes legally binding. Neither party can withdraw without significant financial penalty.</dd><dt>Freehold</dt><dd>Outright ownership of the property and the land it stands on, indefinitely. The most straightforward ownership structure for houses.</dd><dt>Ground Rent</dt><dd>An annual charge paid by a leaseholder to the freeholder. Under the Leasehold Reform Act 2022, new residential leases must have zero ground rent.</dd><dt>Land Registry</dt><dd>The government body that records all land and property ownership in England and Wales. Your ownership is registered here after completion.</dd><dt>Leasehold</dt><dd>Ownership of a property for a fixed term (e.g. 125 years) under the terms of a lease. Common for flats. Leases below 80 years can be costly to extend.</dd><dt>SDLT (Stamp Duty Land Tax)</dt><dd>A tax on residential purchases over &pound;250,000 in England. Rates: 0% to 12% standard (17% for additional properties). First-time buyers pay no SDLT on the first &pound;425,000 of purchases up to &pound;625,000.</dd><dt>Service Charge</dt><dd>A fee paid by leaseholders for maintenance of shared areas and building structure. Common in flats. Can vary significantly year to year.</dd><dt>Survey</dt><dd>A professional inspection of a property. Types: basic Mortgage Valuation (for lender only), RICS HomeBuyer Report, and the comprehensive Building Survey.</dd><dt>Title Deeds</dt><dd>Documents evidencing ownership and the history of a property. Now held electronically by HM Land Registry for most UK properties.</dd></dl>',
					'Reference'
				),
			],

			'conveyancing-explained' => [
				'title'   => 'Conveyancing Explained',
				'excerpt' => 'What conveyancing is, how long it takes, and what to expect at each stage of the legal process.',
				'html'    => self::sp_page(
					'Conveyancing Explained',
					'<h1>Conveyancing Explained</h1><p class="sub">A plain-English guide to the legal process of buying a property in England.</p><div class="sec"><h2>What is conveyancing?</h2><p>Conveyancing is the legal transfer of property ownership from seller to buyer. A solicitor or licensed conveyancer handles it, covering the draft contract, mortgage deed, searches, and Land Registry registration.</p></div><div class="sec"><h2>How long does it take?</h2><p>Typically 8 to 16 weeks from offer acceptance to completion. Chain-free purchases are faster. Common delays include slow mortgage offers, missing paperwork, and chain complications.</p></div><h2>Stages at a glance</h2><table><thead><tr><th>Stage</th><th>Who</th><th>Timing</th></tr></thead><tbody><tr><td>Instruct solicitor</td><td>Buyer</td><td>Day 1 to 3</td></tr><tr><td>Draft contract issued</td><td>Vendor solicitor</td><td>Week 1 to 2</td></tr><tr><td>Local searches ordered</td><td>Buyer solicitor</td><td>Week 1 to 3</td></tr><tr><td>Mortgage offer received</td><td>Lender</td><td>Week 2 to 6</td></tr><tr><td>Searches returned</td><td>Local authority</td><td>Week 3 to 8</td></tr><tr><td>Enquiries resolved</td><td>Both solicitors</td><td>Week 4 to 10</td></tr><tr><td>Exchange of contracts</td><td>Both solicitors</td><td>Week 8 to 14</td></tr><tr><td>Completion</td><td>Solicitors + lender</td><td>1 to 4 weeks after exchange</td></tr></tbody></table><h2>Typical costs</h2><table><thead><tr><th>Item</th><th>Typical cost</th></tr></thead><tbody><tr><td>Solicitor / conveyancer fees</td><td>&pound;900 to &pound;2,000</td></tr><tr><td>Local authority searches</td><td>&pound;250 to &pound;450</td></tr><tr><td>Land Registry fee</td><td>&pound;30 to &pound;910 (by price)</td></tr><tr><td>CHAPS bank transfer fee</td><td>&pound;25 to &pound;50</td></tr><tr><td>ID verification check</td><td>&pound;10 to &pound;20</td></tr></tbody></table>',
					'Guide'
				),
			],

			'privacy-policy' => [
				'title'   => 'Privacy Policy',
				'excerpt' => 'How Advaith Homes collects, uses and protects your personal data in line with UK GDPR.',
				'html'    => self::sp_page(
					'Privacy Policy',
					'<h1>Privacy Policy</h1><p class="sub">Last updated: May 2025. Advaith Homes is committed to protecting your personal data in line with UK GDPR and the Data Protection Act 2018.</p><div class="sec"><h2>Who we are</h2><p>Advaith Homes is a buyer\'s agent operating across the UK. Contact: <strong>contact@advaithhomes.co.uk</strong></p></div><h2>Data we collect</h2><table><thead><tr><th>Category</th><th>Examples</th><th>Purpose</th></tr></thead><tbody><tr><td>Contact data</td><td>Name, email, phone</td><td>Responding to enquiries</td></tr><tr><td>Property preferences</td><td>Budget, area, property type</td><td>Tailoring our service</td></tr><tr><td>Usage data</td><td>Pages visited, session time</td><td>Improving our website</td></tr><tr><td>Cookie data</td><td>Session ID, analytics IDs</td><td>Site functionality and analytics</td></tr></tbody></table><h2>Legal basis for processing</h2><table><thead><tr><th>Activity</th><th>Legal basis</th></tr></thead><tbody><tr><td>Responding to enquiries</td><td>Legitimate interests</td></tr><tr><td>Providing buyer\'s agent service</td><td>Contract performance</td></tr><tr><td>Sending market updates</td><td>Consent (opt-in)</td></tr></tbody></table><h2>Your rights under UK GDPR</h2><p class="sub">You have the right to: access your data, correct inaccuracies, request deletion, restrict processing, and withdraw consent at any time. Contact <strong>contact@advaithhomes.co.uk</strong> to exercise any right.</p><h2>Data retention</h2><p class="sub">Contact data retained for 2 years from last interaction. Analytics data retained for 13 months.</p>'
				),
			],

			'cookie-policy' => [
				'title'   => 'Cookie Policy',
				'excerpt' => 'Information about the cookies used on the Advaith Homes website and how to manage them.',
				'html'    => self::sp_page(
					'Cookie Policy',
					'<h1>Cookie Policy</h1><p class="sub">Last updated: May 2025. This page explains how Advaith Homes uses cookies on our website.</p><div class="sec"><h2>What are cookies?</h2><p>Cookies are small text files placed on your device when you visit a website. They help us recognise returning visitors and understand site usage.</p></div><h2>Cookies we use</h2><table><thead><tr><th>Cookie</th><th>Type</th><th>Purpose</th><th>Duration</th></tr></thead><tbody><tr><td>wordpress_*</td><td>Essential</td><td>WordPress session management</td><td>Session</td></tr><tr><td>wordpress_logged_in_*</td><td>Essential</td><td>Keeps admin users logged in</td><td>Session</td></tr><tr><td>_ga, _gid</td><td>Analytics</td><td>Google Analytics — anonymous visit tracking</td><td>2 years / 24h</td></tr><tr><td>ah_consent</td><td>Functional</td><td>Remembers your cookie consent choice</td><td>1 year</td></tr></tbody></table><h2>Managing cookies</h2><p class="sub">Control cookies through your browser settings. Blocking essential cookies may affect site functionality. To opt out of Google Analytics, use the official opt-out add-on at tools.google.com/dlpage/gaoptout</p>'
				),
			],

		];
	}

	// ── Cleanup ───────────────────────────────────────────────────────────────

	/** @return array{deleted:int} */
	public static function cleanup_all(): array {
		$deleted = 0;
		$deleted += self::cleanup_db_table( 'services' );
		$deleted += self::cleanup_db_table( 'team' );
		$deleted += self::cleanup_db_table( 'reviews' );
		$deleted += self::cleanup_db_table( 'faqs' );
		$deleted += self::cleanup_db_table( 'news_bar' );

		$options = [
			'ah_site_settings', 'ah_home_settings', 'ah_guide_nav',
			'ah_guide_categories', 'ah_nav_buying_topics', 'ah_nav_finance_topics',
			'ah_nav_legal_topics', 'ah_process_steps', 'ah_site_stats',
			'ah_trust_signals', 'ah_news_bar_items', 'ah_featured_properties',
			'ah_contact_settings', 'ah_html_blocks',
			'ah_static_quick_links', 'ah_nav_static_page_links',
		];
		foreach ( $options as $opt ) {
			if ( get_option( $opt ) !== false ) {
				delete_option( $opt );
				$deleted++;
			}
		}
		return [ 'deleted' => $deleted ];
	}

	private static function cleanup_db_table( string $name ): int {
		global $wpdb;
		$table = ah_theme_table( $name );
		if ( ! self::table_exists( $table ) ) return 0;
		return (int) $wpdb->query( "TRUNCATE TABLE `{$table}`" );
	}

	// ── Row counts (for admin status display) ─────────────────────────────────

	public static function table_counts(): array {
		global $wpdb;
		$tables  = [ 'services', 'team', 'reviews', 'faqs', 'news_bar' ];
		$counts  = [];
		foreach ( $tables as $t ) {
			$table = ah_theme_table( $t );
			if ( self::table_exists( $table ) ) {
				$counts[ $t ] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
			} else {
				$counts[ $t ] = null; // null = table doesn't exist (plugin not installed)
			}
		}
		$options = [ 'ah_site_settings', 'ah_home_settings', 'ah_guide_nav', 'ah_guide_categories', 'ah_process_steps', 'ah_site_stats', 'ah_trust_signals' ];
		foreach ( $options as $opt ) {
			$counts[ $opt ] = get_option( $opt ) !== false ? '✓' : '—';
		}
		return $counts;
	}

	// ── Utilities ─────────────────────────────────────────────────────────────

	private static function table_exists( string $table ): bool {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}

	private static function skip( string $reason ): array {
		return [ 'inserted' => 0, 'updated' => 0, '_skip' => $reason ];
	}
}
