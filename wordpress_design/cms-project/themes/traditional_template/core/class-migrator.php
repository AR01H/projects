<?php
defined( 'ABSPATH' ) || exit;

class App_Migrator {
	private static function get_json( string $filename ) {
		$file = get_template_directory() . '/admin/data/' . $filename;
		if ( ! file_exists( $file ) ) return null;
		return json_decode( file_get_contents( $file ), true );
	}

	private static function flatten_and_insert( $prefix, $array ) {
		global $wpdb;
		if ( !is_array($array) ) return;
		foreach ( $array as $key => $value ) {
			if ( in_array($key, ['_comment', '_doc'], true) ) continue;
			$new_key = $prefix . '_' . $key;
			$val_str = is_array($value) ? wp_json_encode($value) : (string)$value;
			$wpdb->replace( $wpdb->prefix . 'tt_settings', ['setting_key' => $new_key, 'setting_value' => $val_str], ['%s', '%s'] );
		}
	}

	public static function run() {
		global $wpdb;
		$p = $wpdb->prefix . 'tt_';

		// ---- FAQS ----
		$wpdb->query("TRUNCATE TABLE {$p}faqs");
		$faqs_global = self::get_json('faqs.json');
		if ( is_array($faqs_global) ) {
			foreach ( $faqs_global as $i => $faq ) {
				$wpdb->insert("{$p}faqs", ['question'=>$faq['question']??'','answer'=>$faq['answer']??'','page_type'=>'global','sort_order'=>$i]);
			}
		}
		foreach ( ['events'=>'faqs_events.json','franchise'=>'faqs_franchise.json','order'=>'faqs_order.json'] as $type => $file ) {
			$data = self::get_json($file);
			if ( $data && isset($data['items']) ) {
				foreach ( $data['items'] as $i => $faq ) {
					$wpdb->insert("{$p}faqs", ['question'=>$faq['q']??$faq['question']??'','answer'=>$faq['a']??$faq['answer']??'','page_type'=>$type,'sort_order'=>$i]);
				}
			}
		}

		// ---- REVIEWS ----
		$wpdb->query("TRUNCATE TABLE {$p}reviews");
		$reviews_global = self::get_json('reviews.json');
		if ( $reviews_global && isset($reviews_global['items']) ) {
			foreach ( $reviews_global['items'] as $i => $rev ) {
				$wpdb->insert("{$p}reviews", ['reviewer_name'=>$rev['name']??$rev['reviewer_name']??'','review_text'=>$rev['text']??$rev['review_text']??'','source'=>$rev['source']??'Google','page_type'=>'global','sort_order'=>$i]);
			}
		}
		foreach ( ['events'=>'reviews_events.json','franchise'=>'reviews_franchise.json','order'=>'reviews_order.json'] as $type => $file ) {
			$data = self::get_json($file);
			if ( $data && isset($data['items']) ) {
				foreach ( $data['items'] as $i => $rev ) {
					$wpdb->insert("{$p}reviews", ['reviewer_name'=>$rev['name']??$rev['reviewer_name']??'','review_text'=>$rev['text']??$rev['review_text']??'','source'=>$rev['source']??'Google','page_type'=>$type,'sort_order'=>$i]);
				}
			}
		}

		// ---- TEAM ----
		$wpdb->query("TRUNCATE TABLE {$p}team");
		$team = self::get_json('team.json');
		if ( $team && isset($team['items']) ) {
			foreach ( $team['items'] as $i => $m ) {
				$wpdb->insert("{$p}team", ['name'=>$m['name']??'','role'=>$m['role']??'','bio'=>$m['bio']??'','image_url'=>$m['image']??$m['image_url']??'','sort_order'=>$i]);
			}
		}

		// ---- LOCATIONS ----
		$wpdb->query("TRUNCATE TABLE {$p}locations");
		$locs = self::get_json('locations.json');
		if ( $locs && isset($locs['items']) ) {
			foreach ( $locs['items'] as $i => $loc ) {
				$wpdb->insert("{$p}locations", ['name'=>$loc['name']??'','address'=>$loc['address']??'','phone'=>$loc['phone']??'','email'=>$loc['email']??'','map_url'=>$loc['map_url']??'','sort_order'=>$i]);
			}
		}

		// ---- HISTORY ----
		$wpdb->query("TRUNCATE TABLE {$p}history");
		$hist = self::get_json('history.json');
		if ( $hist && isset($hist['items']) ) {
			foreach ( $hist['items'] as $i => $m ) {
				$wpdb->insert("{$p}history", ['year'=>$m['year']??'','title'=>$m['title']??'','description'=>$m['description']??$m['body']??'','sort_order'=>$i]);
			}
		}

		// ---- MILESTONES ----
		$milestones = self::get_json('milestones.json');
		if ( $milestones && isset($milestones['items']) ) {
			foreach ( $milestones['items'] as $i => $m ) {
				$wpdb->insert("{$p}history", ['year'=>$m['year']??'','title'=>$m['title']??'','description'=>$m['body']??$m['description']??'','sort_order'=>100+$i]);
			}
		}

		// ---- SITE.JSON flat settings ----
		$wpdb->query("TRUNCATE TABLE {$p}settings");
		$site = self::get_json('site.json');
		if ( $site ) self::flatten_and_insert('site', $site);

		// ---- DRINKS from site.json ----
		$wpdb->query("TRUNCATE TABLE {$p}drinks");
		if ( $site && isset($site['drinks']['items']) ) {
			foreach ( $site['drinks']['items'] as $i => $item ) {
				$wpdb->insert("{$p}drinks", ['name'=>$item['name']??'','description'=>$item['desc']??'','image_url'=>$item['image']??'','sort_order'=>$i]);
			}
		}

		// ---- EVENTS FEATURES from site.json ----
		$wpdb->query("TRUNCATE TABLE {$p}events_features");
		if ( $site && isset($site['events']['features']) ) {
			foreach ( $site['events']['features'] as $i => $item ) {
				$wpdb->insert("{$p}events_features", ['label'=>$item['label']??'','icon_url'=>$item['icon']??'','sort_order'=>$i]);
			}
		}

		// ---- NAV ----
		$wpdb->query("TRUNCATE TABLE {$p}nav");
		$nav = self::get_json('nav.json');
		if ( $nav && isset($nav['header']) ) {
			foreach ( $nav['header'] as $i => $item ) {
				if ( isset($item['label']) ) $wpdb->insert("{$p}nav", ['label'=>$item['label'],'url'=>$item['href']??$item['url']??'#','sort_order'=>$i]);
			}
		}

		// ---- GALLERY ----
		$wpdb->query("TRUNCATE TABLE {$p}gallery");
		foreach ( ['gallery.json'=>'gallery','gallery_events.json'=>'events','gallery_franchise.json'=>'franchise','gallery_order.json'=>'order'] as $file => $section ) {
			$data = self::get_json($file);
			if ( $data && isset($data['images']) ) {
				foreach ( $data['images'] as $i => $img ) {
					$wpdb->insert("{$p}gallery", ['title'=>$img['title']??'','image_url'=>$img['src']??$img['url']??'','alt'=>$img['alt']??'','category'=>$img['category']??'general','section'=>$section,'sort_order'=>$i]);
				}
			}
		}
		// Photo carousel
		$carousel = self::get_json('photo_carousel.json');
		if ( is_array($carousel) ) {
			foreach ( $carousel as $i => $img ) {
				$url = is_string($img) ? $img : ($img['src']??$img['url']??'');
				if ($url) $wpdb->insert("{$p}gallery", ['image_url'=>$url,'section'=>'carousel','sort_order'=>$i]);
			}
		}

		// ---- HIRE PACKAGES ----
		$wpdb->query("TRUNCATE TABLE {$p}hire_packages");
		$pkgs = self::get_json('hire_packages.json');
		if ( is_array($pkgs) ) {
			foreach ( $pkgs as $i => $pkg ) {
				$wpdb->insert("{$p}hire_packages", ['name'=>$pkg['name']??'','description'=>$pkg['description']??$pkg['desc']??'','price'=>$pkg['price']??'','features'=>wp_json_encode($pkg['features']??[]),'sort_order'=>$i]);
			}
		}

		// ---- PRICING TIERS ----
		$wpdb->query("TRUNCATE TABLE {$p}pricing_tiers");
		$tiers = self::get_json('pricing_tiers.json');
		if ( $tiers && isset($tiers['tiers']) ) {
			foreach ( $tiers['tiers'] as $i => $t ) {
				$wpdb->insert("{$p}pricing_tiers", ['name'=>$t['name']??'','price'=>$t['price']??'','description'=>$t['description']??'','features'=>wp_json_encode($t['features']??[]),'is_featured'=>(int)($t['featured']??0),'sort_order'=>$i]);
			}
		}
		self::flatten_and_insert('pricing', ['title'=>$tiers['title']??'','sub'=>$tiers['sub']??'','tag'=>$tiers['tag']??'']);

		// ---- OPENING HOURS ----
		$wpdb->query("TRUNCATE TABLE {$p}opening_hours");
		$hours = self::get_json('opening_hours.json');
		if ( $hours && isset($hours['days']) ) {
			foreach ( $hours['days'] as $i => $d ) {
				$wpdb->insert("{$p}opening_hours", ['day_label'=>$d['day']??$d['label']??'','open_time'=>$d['open']??$d['open_time']??'','close_time'=>$d['close']??$d['close_time']??'','is_closed'=>(int)($d['closed']??0),'sort_order'=>$i]);
			}
		}
		if ($hours) self::flatten_and_insert('opening_hours', ['tag'=>$hours['tag']??'','title'=>$hours['title']??'','sub'=>$hours['sub']??'','note'=>$hours['note']??'']);

		// ---- TICKER ----
		$wpdb->query("TRUNCATE TABLE {$p}ticker_items");
		$ticker = self::get_json('ticker.json');
		if ( $ticker && isset($ticker['items']) ) {
			foreach ( $ticker['items'] as $i => $item ) {
				$text = is_string($item) ? $item : ($item['text']??wp_json_encode($item));
				$wpdb->insert("{$p}ticker_items", ['content'=>$text,'sort_order'=>$i]);
			}
		}

		// ---- VALUES ----
		$wpdb->query("TRUNCATE TABLE {$p}values");
		$vals = self::get_json('values.json');
		if ( $vals && isset($vals['items']) ) {
			foreach ( $vals['items'] as $i => $v ) {
				$wpdb->insert("{$p}values", ['title'=>$v['title']??'','description'=>$v['body']??$v['description']??'','icon_url'=>$v['icon']??'','sort_order'=>$i]);
			}
		}
		if ($vals) self::flatten_and_insert('values', ['tag'=>$vals['tag']??'','title'=>$vals['title']??'','sub'=>$vals['sub']??'']);

		// ---- CERTIFICATIONS ----
		$wpdb->query("TRUNCATE TABLE {$p}certifications");
		$certs = self::get_json('certifications.json');
		if ( is_array($certs) ) {
			foreach ( $certs as $i => $c ) {
				$wpdb->insert("{$p}certifications", ['name'=>$c['name']??$c['title']??'','image_url'=>$c['image']??$c['logo']??'','sort_order'=>$i]);
			}
		}

		// ---- LOGO STRIP ----
		$wpdb->query("TRUNCATE TABLE {$p}logo_strip");
		$logos = self::get_json('logo_strip.json');
		if ( $logos && isset($logos['items']) ) {
			foreach ( $logos['items'] as $i => $l ) {
				$wpdb->insert("{$p}logo_strip", ['name'=>$l['name']??'','image_url'=>$l['image']??$l['src']??'','link_url'=>$l['url']??'','sort_order'=>$i]);
			}
		}
		if ($logos) self::flatten_and_insert('logo_strip', ['tag'=>$logos['tag']??'','title'=>$logos['title']??'','sub'=>$logos['sub']??'']);

		// ---- PROCESS STEPS ----
		$wpdb->query("TRUNCATE TABLE {$p}process_steps");
		$steps = self::get_json('process_steps.json');
		if ( $steps && isset($steps['steps']) ) {
			foreach ( $steps['steps'] as $i => $s ) {
				$wpdb->insert("{$p}process_steps", ['title'=>$s['title']??'','description'=>$s['body']??$s['description']??'','icon_url'=>$s['icon']??'','step_number'=>$i+1,'sort_order'=>$i]);
			}
		}
		if ($steps) self::flatten_and_insert('process_steps', ['tag'=>$steps['tag']??'','title'=>$steps['title']??'','sub'=>$steps['sub']??'']);

		// ---- DELIVERY PRODUCTS ----
		$wpdb->query("TRUNCATE TABLE {$p}delivery_products");
		$products = self::get_json('delivery_products.json');
		if ( is_array($products) ) {
			foreach ( $products as $i => $prod ) {
				$wpdb->insert("{$p}delivery_products", ['name'=>$prod['name']??'','description'=>$prod['description']??$prod['desc']??'','image_url'=>$prod['image']??'','price'=>$prod['price']??'','sort_order'=>$i]);
			}
		}

		// ---- INFO CARDS ----
		$wpdb->query("TRUNCATE TABLE {$p}info_cards");
		$cards = self::get_json('info_cards.json');
		if ( $cards && isset($cards['items']) ) {
			foreach ( $cards['items'] as $i => $c ) {
				$wpdb->insert("{$p}info_cards", ['title'=>$c['title']??'','description'=>$c['body']??$c['description']??'','icon_url'=>$c['icon']??'','link_url'=>$c['url']??'','sort_order'=>$i]);
			}
		}
		if ($cards) self::flatten_and_insert('info_cards', ['tag'=>$cards['tag']??'','title'=>$cards['title']??'','sub'=>$cards['sub']??'']);

		// ---- SIGNATURE FLAVOURS ----
		$wpdb->query("TRUNCATE TABLE {$p}flavours");
		$flavours = self::get_json('flavours.json');
		if ( $flavours ) {
			foreach ( $flavours as $category => $items ) {
				if ( !is_array($items) ) continue;
				foreach ( $items as $i => $f ) {
					$name = is_string($f) ? $f : ($f['name']??'');
					if ($name) $wpdb->insert("{$p}flavours", ['name'=>$name,'category'=>$category,'sort_order'=>$i]);
				}
			}
		}
		// Signature flavours
		$sig = self::get_json('signature_flavours.json');
		if ( $sig && isset($sig['bottles']) ) {
			self::flatten_and_insert('signature_flavours', ['tag'=>$sig['tag']??'','title'=>$sig['title']??'','sub'=>$sig['sub']??'','button'=>$sig['button']??'']);
		}

		// ---- STATS ----
		$wpdb->query("TRUNCATE TABLE {$p}stats");
		$stats = self::get_json('stats.json');
		if ( $stats ) {
			foreach ( $stats as $key => $val ) {
				$wpdb->replace("{$p}stats", ['stat_key'=>$key,'stat_value'=>is_array($val)?$val['value']??'':$val,'label'=>is_array($val)?$val['label']??$key:$key]);
			}
		}

		// ---- FLAT SETTINGS from remaining JSONs ----
		foreach ( ['settings.json'=>'settings','cookies.json'=>'cookies','decor.json'=>'decor','map.json'=>'map','breadcrumbs.json'=>'breadcrumbs','blog.json'=>'blog','posts_preview.json'=>'posts_preview','home_media.json'=>'home_media','dialogs.json'=>'dialogs','newsletter.json'=>'newsletter','page_headers.json'=>'page_headers','cta_default.json'=>'cta_default','cta_events.json'=>'cta_events','cta_franchise.json'=>'cta_franchise','cta_products.json'=>'cta_products','franchise.json'=>'franchise','experience_data.json'=>'experience','paper_story.json'=>'paper_story','video_feature.json'=>'video_feature','split_feature.json'=>'split_feature','blocks.json'=>'blocks_config','hero_checks.json'=>'hero_checks','home.json'=>'home','home_banner.json'=>'home_banner','footer.json'=>'footer','legal.json'=>'legal','legal_cookies.json'=>'legal_cookies','legal_privacy.json'=>'legal_privacy','legal_terms.json'=>'legal_terms','compare_franchise.json'=>'compare_franchise','downloads.json'=>'downloads_config','quick_links.json'=>'quick_links_config','filter_cards.json'=>'filter_cards','tabs_services.json'=>'tabs_services','about.json'=>'about','careers.json'=>'careers'] as $file => $prefix ) {
			$data = self::get_json($file);
			if ($data) self::flatten_and_insert($prefix, is_array($data) ? ['items'=>$data] : $data);
		}

		return 'Migration Complete! All ' . count(glob(get_template_directory() . '/admin/data/*.json')) . ' JSON files processed.';
	}

}
