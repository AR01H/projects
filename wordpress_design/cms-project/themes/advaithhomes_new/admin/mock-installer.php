<?php
/**
 * admin/mock-installer.php
 *
 * Reads CSV files from data/mockdata/{dataset}/ and seeds demo content
 * into the CMS plugin's real data model.
 *
 * Idempotent: slugs/titles already in the DB are skipped.
 * All installed items are tagged so remove_all() can clean up cleanly.
 *
 * Supported types:
 *   taxonomy  → taxonomy.csv  → Parent Terms + Topic Terms (wp_ah_taxonomies)
 *   guides    → guides.csv    → WP posts linked to topic terms
 *   news      → news.csv      → WP posts linked to a News term
 *   faqs      → faqs.csv      → FAQ rows (wp_ah_faqs)
 *   reviews   → reviews.csv   → Review/testimonial rows (wp_ah_reviews)
 *   members   → members.csv   → Expert profiles (wp_ah_experts via AH_Expert_DB)
 *   terms     → terms.csv     → Glossary terms (wp_ah_taxonomies, type=glossary)
 *   notices   → notices.csv   → Active site notice via AH_Notice_Helper
 *
 * Cleanup:
 *   WP posts  → tagged with _adn_mock_data = 1  (meta query for removal)
 *   DB rows   → IDs logged to wp_option 'adn_mock_install_log'
 */

defined( 'ABSPATH' ) || exit;

class ADN_Mock_Installer {

    const LOG_OPTION  = 'adn_mock_install_log';
    const META_KEY    = '_adn_mock_data';
    const META_DS_KEY = '_adn_mock_dataset';

    // ── Entry: seed ──────────────────────────────────────────────────────────────

    /**
     * Called by ADN_Theme_Admin::handle_seed_content().
     * POST: seed[{dataset}][{type}] = '1'
     *
     * @return array{ ok:bool, message:string }
     */
    public static function seed() {
        $raw = ( isset( $_POST['seed'] ) && is_array( $_POST['seed'] ) )
            ? wp_unslash( $_POST['seed'] ) : array();

        if ( empty( $raw ) ) {
            return array( 'ok' => false, 'message' => __( 'Nothing selected - tick at least one checkbox and try again.', ADN_TEXT_DOMAIN ) );
        }

        $allowed_types = array( 'taxonomy', 'guides', 'news', 'faqs', 'reviews', 'members', 'terms', 'banners', 'notices' );
        $mockdata_base = realpath( ADN_THEME_DIR . '/data/mockdata' );

        $plan = array();
        foreach ( $raw as $dataset => $types ) {
            $dataset = sanitize_key( $dataset );
            if ( ! $dataset || ! is_array( $types ) ) { continue; }
            $ds_dir = realpath( $mockdata_base . '/' . $dataset );
            if ( ! $ds_dir || 0 !== strpos( $ds_dir, $mockdata_base ) ) { continue; }
            foreach ( array_keys( $types ) as $t ) {
                $t = sanitize_key( $t );
                if ( in_array( $t, $allowed_types, true ) ) {
                    $plan[ $dataset ][] = $t;
                }
            }
        }

        if ( empty( $plan ) ) {
            return array( 'ok' => false, 'message' => __( 'No valid selections found. Please try again.', ADN_TEXT_DOMAIN ) );
        }

        $totals = array( 'parents' => 0, 'terms' => 0, 'posts' => 0, 'faqs' => 0, 'reviews' => 0, 'members' => 0, 'glossary' => 0, 'banners' => 0, 'notices' => 0 );
        $errors = array();

        foreach ( $plan as $dataset => $types ) {
            $ds_dir = realpath( ADN_THEME_DIR . '/data/mockdata/' . $dataset );

            if ( in_array( 'taxonomy', $types, true ) ) {
                $r = self::import_taxonomy( $ds_dir . '/taxonomy.csv', $dataset );
                if ( $r['ok'] ) { $totals['parents'] += $r['summary']['parents']; $totals['terms'] += $r['summary']['terms']; }
                else { $errors[] = "[{$dataset}/taxonomy] " . $r['message']; }
            }

            if ( in_array( 'guides', $types, true ) ) {
                $r = self::import_guides( $ds_dir . '/guides.csv', $dataset );
                if ( $r['ok'] ) { $totals['posts'] += $r['summary']['posts']; }
                else { $errors[] = "[{$dataset}/guides] " . $r['message']; }
            }

            if ( in_array( 'news', $types, true ) ) {
                $r = self::import_news( $ds_dir . '/news.csv', $dataset );
                if ( $r['ok'] ) { $totals['posts'] += $r['summary']['posts']; }
                else { $errors[] = "[{$dataset}/news] " . $r['message']; }
            }

            if ( in_array( 'faqs', $types, true ) ) {
                $r = self::import_faqs( $ds_dir . '/faqs.csv', $dataset );
                if ( $r['ok'] ) { $totals['faqs'] += $r['summary']['faqs']; }
                else { $errors[] = "[{$dataset}/faqs] " . $r['message']; }
            }

            if ( in_array( 'reviews', $types, true ) ) {
                $r = self::import_reviews( $ds_dir . '/reviews.csv', $dataset );
                if ( $r['ok'] ) { $totals['reviews'] += $r['summary']['reviews']; }
                else { $errors[] = "[{$dataset}/reviews] " . $r['message']; }
            }

            if ( in_array( 'members', $types, true ) ) {
                $r = self::import_members( $ds_dir . '/members.csv', $dataset );
                if ( $r['ok'] ) { $totals['members'] += $r['summary']['members']; }
                else { $errors[] = "[{$dataset}/members] " . $r['message']; }
            }

            if ( in_array( 'terms', $types, true ) ) {
                $r = self::import_terms( $ds_dir . '/terms.csv', $dataset );
                if ( $r['ok'] ) { $totals['glossary'] += $r['summary']['glossary']; }
                else { $errors[] = "[{$dataset}/terms] " . $r['message']; }
            }

            if ( in_array( 'banners', $types, true ) ) {
                $r = self::import_banners( $ds_dir . '/banners.csv' );
                if ( $r['ok'] ) { $totals['banners'] += $r['summary']['banners']; }
                else { $errors[] = "[{$dataset}/banners] " . $r['message']; }
            }

            if ( in_array( 'notices', $types, true ) ) {
                $r = self::import_notice( $ds_dir . '/notices.csv' );
                if ( $r['ok'] ) { $totals['notices'] += $r['summary']['notices']; }
                else { $errors[] = "[{$dataset}/notices] " . $r['message']; }
            }
        }

        if ( ! empty( $errors ) ) {
            return array( 'ok' => false, 'message' => implode( ' | ', $errors ) );
        }

        $parts = array();
        $labels = array(
            'parents'  => array( '%d parent term',   '%d parent terms' ),
            'terms'    => array( '%d topic',          '%d topics' ),
            'posts'    => array( '%d post',           '%d posts' ),
            'faqs'     => array( '%d FAQ',            '%d FAQs' ),
            'reviews'  => array( '%d review',         '%d reviews' ),
            'members'  => array( '%d member',         '%d members' ),
            'glossary' => array( '%d glossary term',  '%d glossary terms' ),
            'banners'  => array( '%d banner',         '%d banners' ),
            'notices'  => array( '%d notice',         '%d notices' ),
        );
        foreach ( $labels as $key => $pair ) {
            if ( ! empty( $totals[ $key ] ) ) {
                $parts[] = sprintf( _n( $pair[0], $pair[1], $totals[ $key ], ADN_TEXT_DOMAIN ), $totals[ $key ] );
            }
        }

        $created_str = empty( $parts )
            ? __( 'Nothing new to create - all items already existed.', ADN_TEXT_DOMAIN )
            : implode( ', ', $parts ) . ' ' . __( 'created.', ADN_TEXT_DOMAIN );

        return array( 'ok' => true, 'message' => $created_str . ' ' . __( 'Existing items were left untouched.', ADN_TEXT_DOMAIN ) );
    }

    // ── Entry: remove_all ────────────────────────────────────────────────────────

    /**
     * Called by ADN_Theme_Admin::handle_remove_mock_data().
     * Deletes every item tagged during seed(). Never touches untagged content.
     *
     * @return array{ ok:bool, message:string }
     */
    public static function remove_all() {
        global $wpdb;

        $log    = get_option( self::LOG_OPTION, array() );
        $counts = array();

        // 1. WP posts tagged with _adn_mock_data meta.
        $tagged_ids = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => self::META_KEY,
            'meta_value'     => '1',
        ) );
        foreach ( $tagged_ids as $pid ) {
            wp_delete_post( (int) $pid, true );
        }
        $counts['posts'] = count( $tagged_ids );

        // 2. FAQs logged by ID.
        $faq_ids = isset( $log['ah_faqs'] ) ? (array) $log['ah_faqs'] : array();
        if ( $faq_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $faq_ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}ah_faqs` WHERE id IN ({$placeholders})", ...$faq_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $counts['faqs'] = count( $faq_ids );
        }

        // 3. Reviews logged by ID.
        $review_ids = isset( $log['ah_reviews'] ) ? (array) $log['ah_reviews'] : array();
        if ( $review_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $review_ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}ah_reviews` WHERE id IN ({$placeholders})", ...$review_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $counts['reviews'] = count( $review_ids );
        }

        // 4. Expert profiles logged by slug.
        $expert_slugs = isset( $log['ah_experts'] ) ? (array) $log['ah_experts'] : array();
        if ( $expert_slugs ) {
            foreach ( $expert_slugs as $eslug ) {
                $eslug = sanitize_key( $eslug );
                if ( $eslug ) {
                    $wpdb->delete( $wpdb->prefix . 'ah_experts', array( 'expert_slug' => $eslug ), array( '%s' ) );
                }
            }
            $counts['members'] = count( $expert_slugs );
        }

        // 5. Glossary taxonomy entries logged by ID.
        $glossary_ids = isset( $log['ah_glossary'] ) ? (array) $log['ah_glossary'] : array();
        if ( $glossary_ids ) {
            $placeholders = implode( ',', array_fill( 0, count( $glossary_ids ), '%d' ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}ah_taxonomies` WHERE id IN ({$placeholders})", ...$glossary_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $counts['glossary'] = count( $glossary_ids );
        }

        // 6. Remove expert banner if it was set by mock installer.
        if ( ! empty( $log['adn_expert_banner'] ) ) {
            delete_option( 'adn_expert_banner' );
            $counts['banners'] = 1;
        }

        // 7. Clear the install log.
        delete_option( self::LOG_OPTION );

        $parts = array();
        $map = array( 'posts' => 'posts', 'faqs' => 'FAQs', 'reviews' => 'reviews', 'members' => 'members', 'glossary' => 'glossary terms' );
        foreach ( $map as $key => $label ) {
            if ( ! empty( $counts[ $key ] ) ) {
                $parts[] = $counts[ $key ] . ' ' . $label;
            }
        }
        $msg = empty( $parts )
            ? __( 'No mock data found to remove. If data was installed before this feature existed, tagged posts have been deleted.', ADN_TEXT_DOMAIN )
            : implode( ', ', $parts ) . ' ' . __( 'removed.', ADN_TEXT_DOMAIN );

        return array( 'ok' => true, 'message' => $msg );
    }

    // ── Install log helpers ──────────────────────────────────────────────────────

    /** Append one or more IDs to a sub-key of the install log. */
    private static function log_ids( $sub_key, $ids ) {
        $log = get_option( self::LOG_OPTION, array() );
        if ( ! isset( $log[ $sub_key ] ) ) {
            $log[ $sub_key ] = array();
        }
        foreach ( (array) $ids as $id ) {
            if ( $id && ! in_array( $id, $log[ $sub_key ], true ) ) {
                $log[ $sub_key ][] = $id;
            }
        }
        update_option( self::LOG_OPTION, $log, false );
    }

    // ── CSV helper ───────────────────────────────────────────────────────────────

    /** Parse a CSV file into associative rows. Returns false on failure. */
    private static function read_csv( $path ) {
        $base = realpath( ADN_THEME_DIR . '/data/mockdata' );
        $real = realpath( $path );
        if ( ! $base || ! $real || 0 !== strpos( $real, $base ) || ! is_file( $real ) ) {
            return false;
        }
        $handle = fopen( $real, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
        if ( ! $handle ) { return false; }
        $rows   = array();
        $header = null;
        while ( ( $line = fgetcsv( $handle ) ) !== false ) {
            if ( null === $header ) { $header = array_map( 'trim', $line ); continue; }
            if ( count( $line ) !== count( $header ) ) { continue; }
            $rows[] = array_combine( $header, $line );
        }
        fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
        return $rows;
    }

    // ── Taxonomy importer ────────────────────────────────────────────────────────

    private static function import_taxonomy( $csv_path, $dataset ) {
        global $wpdb;

        if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
            return array( 'ok' => false, 'message' => __( 'CMS plugin not active.', ADN_TEXT_DOMAIN ), 'summary' => array() );
        }

        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        $types_table = $wpdb->prefix . 'ah_taxonomy_types';
        $tax_table   = $wpdb->prefix . 'ah_taxonomies';
        $pt_table    = $wpdb->prefix . 'ah_taxonomy_parent_terms';

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt_table ) ) !== $pt_table ) {
            return array( 'ok' => false, 'message' => __( 'Parent Terms table missing. Open Taxonomies in the CMS plugin first.', ADN_TEXT_DOMAIN ), 'summary' => array() );
        }

        $type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$types_table}` WHERE slug = %s", 'category' ) );
        if ( ! $type_id ) {
            $wpdb->insert( $types_table, array( 'name' => 'Category', 'slug' => 'category', 'description' => 'Content categories' ) );
            $type_id = (int) $wpdb->insert_id;
        }

        $summary    = array( 'parents' => 0, 'terms' => 0 );
        $parent_ids = array();

        foreach ( $rows as $row ) {
            $type   = isset( $row['type'] )       ? trim( $row['type'] )       : '';
            $slug   = isset( $row['slug'] )       ? sanitize_key( trim( $row['slug'] ) )        : '';
            $name   = isset( $row['name'] )       ? sanitize_text_field( trim( $row['name'] ) )  : '';
            $icon   = isset( $row['icon_emoji'] ) ? sanitize_text_field( trim( $row['icon_emoji'] ) ) : '';
            $desc   = isset( $row['description'] ) ? sanitize_text_field( trim( $row['description'] ) ) : '';
            $status = isset( $row['status'] )     ? sanitize_key( trim( $row['status'] ) ) : 'active';
            $sort   = isset( $row['id'] )         ? (int) $row['id'] : 0;

            if ( ! $slug || ! $name ) { continue; }

            if ( 'parent' === $type ) {
                $r = self::ensure_parent_term( $pt_table, $name, $slug, $desc, $icon, $sort, $status );
                $parent_ids[ $slug ] = $r['id'];
                $summary['parents'] += $r['created'] ? 1 : 0;

            } elseif ( 'topic' === $type ) {
                $csv_parent_id = isset( $row['parent_id'] ) ? (int) trim( $row['parent_id'] ) : 0;
                $pid_in_db     = 0;
                foreach ( $rows as $pr ) {
                    if ( 'parent' === trim( $pr['type'] ) && (int) trim( $pr['id'] ) === $csv_parent_id ) {
                        $pslug     = sanitize_key( trim( $pr['slug'] ) );
                        $pid_in_db = isset( $parent_ids[ $pslug ] ) ? $parent_ids[ $pslug ] : 0;
                        break;
                    }
                }
                $r = self::ensure_term( $tax_table, $type_id, $pid_in_db, $name, $slug, $sort, $status );
                $summary['terms'] += $r['created'] ? 1 : 0;
            }
        }

        return array( 'ok' => true, 'message' => '', 'summary' => $summary );
    }

    // ── Guides importer ──────────────────────────────────────────────────────────

    private static function import_guides( $csv_path, $dataset ) {
        global $wpdb;

        if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
            return array( 'ok' => false, 'message' => __( 'CMS plugin not active.', ADN_TEXT_DOMAIN ), 'summary' => array() );
        }

        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        $tax_table = $wpdb->prefix . 'ah_taxonomies';
        $summary   = array( 'posts' => 0 );

        foreach ( $rows as $row ) {
            $title      = isset( $row['title'] )      ? sanitize_text_field( trim( $row['title'] ) )      : '';
            $slug       = isset( $row['slug'] )       ? sanitize_title( trim( $row['slug'] ) )             : '';
            $excerpt    = isset( $row['excerpt'] )    ? sanitize_textarea_field( trim( $row['excerpt'] ) ) : '';
            $content    = isset( $row['content'] )    ? wp_kses_post( trim( $row['content'] ) )            : '';
            $topic_slug = isset( $row['topic_slug'] ) ? sanitize_key( trim( $row['topic_slug'] ) )         : '';
            $featured   = isset( $row['featured'] )   && '1' === trim( $row['featured'] );

            if ( ! $title || ! $slug ) { continue; }

            $term_ids = array();
            if ( $topic_slug ) {
                $term_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$tax_table}` WHERE slug = %s LIMIT 1", $topic_slug ) );
                if ( $term_id ) { $term_ids[] = $term_id; }
            }

            $summary['posts'] += self::ensure_wp_post( $title, $slug, $excerpt, $content, $term_ids, $featured, $dataset );
        }

        return array( 'ok' => true, 'message' => '', 'summary' => $summary );
    }

    // ── News importer ────────────────────────────────────────────────────────────

    private static function import_news( $csv_path, $dataset ) {
        global $wpdb;

        if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
            return array( 'ok' => false, 'message' => __( 'CMS plugin not active.', ADN_TEXT_DOMAIN ), 'summary' => array() );
        }

        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        $types_table = $wpdb->prefix . 'ah_taxonomy_types';
        $tax_table   = $wpdb->prefix . 'ah_taxonomies';
        $pt_table    = $wpdb->prefix . 'ah_taxonomy_parent_terms';

        $type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$types_table}` WHERE slug = %s", 'category' ) );
        if ( ! $type_id ) {
            $wpdb->insert( $types_table, array( 'name' => 'Category', 'slug' => 'category', 'description' => 'Content categories' ) );
            $type_id = (int) $wpdb->insert_id;
        }

        $news_parent  = self::ensure_parent_term( $pt_table, 'News', 'news', 'Latest News', '📰', 9, 'active' );
        $news_term    = self::ensure_term( $tax_table, $type_id, (int) $news_parent['id'], 'Industry News', 'news-industry', 0, 'active' );
        $news_term_id = (int) $news_term['id'];

        $summary = array( 'posts' => 0 );

        foreach ( $rows as $row ) {
            $title    = isset( $row['title'] )   ? sanitize_text_field( trim( $row['title'] ) )      : '';
            $slug     = isset( $row['slug'] )    ? sanitize_title( trim( $row['slug'] ) )             : '';
            $excerpt  = isset( $row['excerpt'] ) ? sanitize_textarea_field( trim( $row['excerpt'] ) ) : '';
            $content  = isset( $row['content'] ) ? wp_kses_post( trim( $row['content'] ) )            : '';
            $featured = isset( $row['featured'] ) && '1' === trim( $row['featured'] );

            if ( ! $title || ! $slug ) { continue; }

            $summary['posts'] += self::ensure_wp_post( $title, $slug, $excerpt, $content, array( $news_term_id ), $featured, $dataset );
        }

        return array( 'ok' => true, 'message' => '', 'summary' => $summary );
    }

    // ── FAQs importer ────────────────────────────────────────────────────────────

    /**
     * Import faqs.csv → wp_ah_faqs table.
     * CSV columns: question, answer, topic_slug, sort_order, status
     */
    private static function import_faqs( $csv_path, $dataset ) {
        global $wpdb;

        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        $table   = $wpdb->prefix . 'ah_faqs';
        $summary = array( 'faqs' => 0 );
        $new_ids = array();

        foreach ( $rows as $row ) {
            $question = isset( $row['question'] ) ? sanitize_text_field( trim( $row['question'] ) ) : '';
            $answer   = isset( $row['answer'] )   ? sanitize_textarea_field( trim( $row['answer'] ) ) : '';
            $status   = isset( $row['status'] )   ? sanitize_key( trim( $row['status'] ) ) : 'active';
            $sort     = isset( $row['sort_order'] ) ? (int) trim( $row['sort_order'] ) : 0;

            if ( ! $question ) { continue; }

            // Idempotent: skip if question already exists.
            $exists = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM `{$table}` WHERE question = %s LIMIT 1",
                $question
            ) );
            if ( $exists ) { continue; }

            $wpdb->insert( $table, array(
                'question'   => $question,
                'answer'     => $answer,
                'link_text'  => '',
                'link_url'   => '',
                'page_id'    => 0,
                'sort_order' => $sort,
                'status'     => $status,
            ), array( '%s', '%s', '%s', '%s', '%d', '%d', '%s' ) );

            $new_id = (int) $wpdb->insert_id;
            if ( $new_id ) {
                $new_ids[]      = $new_id;
                $summary['faqs']++;
            }
        }

        if ( $new_ids ) {
            self::log_ids( 'ah_faqs', $new_ids );
        }

        return array( 'ok' => true, 'message' => '', 'summary' => $summary );
    }

    // ── Reviews importer ─────────────────────────────────────────────────────────

    /**
     * Import reviews.csv → wp_ah_reviews table.
     * CSV columns: reviewer_name, reviewer_title, short_desc, review_text, rating,
     *              source, is_featured, sort_order, status
     */
    private static function import_reviews( $csv_path, $dataset ) {
        global $wpdb;

        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        $table   = $wpdb->prefix . 'ah_reviews';
        $summary = array( 'reviews' => 0 );
        $new_ids = array();

        foreach ( $rows as $row ) {
            $name      = isset( $row['reviewer_name'] )  ? sanitize_text_field( trim( $row['reviewer_name'] ) )  : '';
            $title     = isset( $row['reviewer_title'] ) ? sanitize_text_field( trim( $row['reviewer_title'] ) ) : '';
            $short     = isset( $row['short_desc'] )     ? sanitize_text_field( trim( $row['short_desc'] ) )     : '';
            $text      = isset( $row['review_text'] )    ? sanitize_textarea_field( trim( $row['review_text'] ) ) : '';
            $rating    = isset( $row['rating'] )         ? min( 5, max( 1, (int) trim( $row['rating'] ) ) )      : 5;
            $source    = isset( $row['source'] )         ? sanitize_key( trim( $row['source'] ) )                : 'manual';
            $featured  = isset( $row['is_featured'] )    && '1' === trim( $row['is_featured'] ) ? 1 : 0;
            $sort      = isset( $row['sort_order'] )     ? (int) trim( $row['sort_order'] )                      : 0;
            $status    = isset( $row['status'] )         ? sanitize_key( trim( $row['status'] ) )                : 'active';

            if ( ! $name || ! $text ) { continue; }

            // Idempotent: skip if same reviewer + same short_desc combo already exists.
            $exists = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM `{$table}` WHERE reviewer_name = %s AND short_desc = %s LIMIT 1",
                $name,
                $short
            ) );
            if ( $exists ) { continue; }

            $wpdb->insert( $table, array(
                'reviewer_name'  => $name,
                'reviewer_title' => $title,
                'short_desc'     => $short,
                'review_text'    => $text,
                'rating'         => $rating,
                'source'         => $source,
                'is_featured'    => $featured,
                'status'         => $status,
                'sort_order'     => $sort,
            ), array( '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%d' ) );

            $new_id = (int) $wpdb->insert_id;
            if ( $new_id ) {
                $new_ids[]         = $new_id;
                $summary['reviews']++;
            }
        }

        if ( $new_ids ) {
            self::log_ids( 'ah_reviews', $new_ids );
        }

        return array( 'ok' => true, 'message' => '', 'summary' => $summary );
    }

    // ── Members importer ─────────────────────────────────────────────────────────

    /**
     * Import members.csv → wp_ah_experts via AH_Expert_DB::save().
     * CSV columns: name, slug, title, category, bio, location, status,
     *              rating, reviews_count, bullets (pipe-separated)
     */
    private static function import_members( $csv_path, $dataset ) {
        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        if ( ! class_exists( 'AH_Expert_DB' ) ) {
            return array( 'ok' => false, 'message' => __( 'AH_Expert_DB class not found. Check theme includes.', ADN_TEXT_DOMAIN ), 'summary' => array() );
        }

        global $wpdb;
        $exp_table = $wpdb->prefix . 'ah_experts';
        $summary   = array( 'members' => 0 );
        $new_slugs = array();

        foreach ( $rows as $row ) {
            $slug     = isset( $row['slug'] )          ? sanitize_key( trim( $row['slug'] ) )                  : '';
            $name     = isset( $row['name'] )          ? sanitize_text_field( trim( $row['name'] ) )           : '';
            $xtitle   = isset( $row['title'] )         ? sanitize_text_field( trim( $row['title'] ) )          : '';
            $category = isset( $row['category'] )      ? sanitize_text_field( trim( $row['category'] ) )       : '';
            $bio      = isset( $row['bio'] )           ? sanitize_textarea_field( trim( $row['bio'] ) )        : '';
            $location = isset( $row['location'] )      ? sanitize_text_field( trim( $row['location'] ) )       : '';
            $status   = isset( $row['status'] )        ? sanitize_key( trim( $row['status'] ) )                : 'active';
            $rating   = isset( $row['rating'] )        ? (float) trim( $row['rating'] )                        : 0;
            $rev_cnt  = isset( $row['reviews_count'] ) ? (int) trim( $row['reviews_count'] )                   : 0;
            $bullets  = isset( $row['bullets'] )       ? array_map( 'sanitize_text_field', explode( '|', $row['bullets'] ) ) : array();

            if ( ! $slug || ! $name ) { continue; }

            // Idempotent: skip if this slug already exists.
            $exists = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM `{$exp_table}` WHERE expert_slug = %s LIMIT 1",
                $slug
            ) );
            if ( $exists ) { continue; }

            $result = AH_Expert_DB::save( array(
                'expert_slug'   => $slug,
                'name'          => $name,
                'title'         => $xtitle,
                'category'      => $category,
                'status'        => $status,
                'bio'           => $bio,
                'rating'        => $rating,
                'reviews_count' => $rev_cnt,
                'location'      => $location,
                'bullets'       => $bullets,
                'photo_id'      => 0,
            ) );

            if ( $result ) {
                $new_slugs[]       = $slug;
                $summary['members']++;
            }
        }

        if ( $new_slugs ) {
            self::log_ids( 'ah_experts', $new_slugs );
        }

        return array( 'ok' => true, 'message' => '', 'summary' => $summary );
    }

    // ── Glossary terms importer ──────────────────────────────────────────────────

    /**
     * Import terms.csv → wp_ah_taxonomies with type = 'glossary'.
     * CSV columns: term, slug, definition, related_terms, parent_slug, sort_order, status
     */
    private static function import_terms( $csv_path, $dataset ) {
        global $wpdb;

        if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
            return array( 'ok' => false, 'message' => __( 'CMS plugin not active.', ADN_TEXT_DOMAIN ), 'summary' => array() );
        }

        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        $types_table = $wpdb->prefix . 'ah_taxonomy_types';
        $tax_table   = $wpdb->prefix . 'ah_taxonomies';
        $pt_table    = $wpdb->prefix . 'ah_taxonomy_parent_terms';

        // Ensure "glossary" type exists.
        $type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$types_table}` WHERE slug = %s", 'glossary' ) );
        if ( ! $type_id ) {
            $wpdb->insert( $types_table, array( 'name' => 'Glossary', 'slug' => 'glossary', 'description' => 'Terminology definitions' ) );
            $type_id = (int) $wpdb->insert_id;
        }

        $summary = array( 'glossary' => 0 );
        $new_ids = array();

        foreach ( $rows as $row ) {
            $name        = isset( $row['term'] )        ? sanitize_text_field( trim( $row['term'] ) )        : '';
            $slug        = isset( $row['slug'] )        ? sanitize_key( trim( $row['slug'] ) )               : '';
            $desc        = isset( $row['definition'] )  ? sanitize_textarea_field( trim( $row['definition'] ) ) : '';
            $status      = isset( $row['status'] )      ? sanitize_key( trim( $row['status'] ) )             : 'active';
            $sort        = isset( $row['sort_order'] )  ? (int) trim( $row['sort_order'] )                   : 0;
            $parent_slug = isset( $row['parent_slug'] ) ? sanitize_key( trim( $row['parent_slug'] ) )        : '';

            if ( ! $slug || ! $name ) { continue; }

            // Resolve parent_term_id from the wp_ah_taxonomy_parent_terms table.
            $parent_term_id = 0;
            if ( $parent_slug && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt_table ) ) === $pt_table ) {
                $parent_term_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt_table}` WHERE slug = %s LIMIT 1", $parent_slug ) );
            }

            $r = self::ensure_term( $tax_table, $type_id, $parent_term_id, $name, $slug, $sort, $status );
            if ( $r['created'] ) {
                $new_ids[]           = $r['id'];
                $summary['glossary']++;
            }
        }

        if ( $new_ids ) {
            self::log_ids( 'ah_glossary', $new_ids );
        }

        return array( 'ok' => true, 'message' => '', 'summary' => $summary );
    }

    // ── Banners importer ─────────────────────────────────────────────────────────

    /**
     * Import banners.csv → WordPress options for section banners.
     * CSV columns: section, heading, info, marquee_items
     *   marquee_items format: icon|value|label  separated by  ||
     *   e.g. "🏠|500+|Properties Sold||⭐|4.9/5|Rating"
     *
     * Currently supported sections:
     *   expert → sets adn_expert_banner option
     */
    private static function import_banners( $csv_path ) {
        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        $count = 0;
        $log   = get_option( self::LOG_OPTION, array() );

        foreach ( $rows as $row ) {
            $section = isset( $row['section'] ) ? sanitize_key( trim( $row['section'] ) ) : '';
            $heading = isset( $row['heading'] ) ? sanitize_text_field( trim( $row['heading'] ) ) : '';
            $info    = isset( $row['info'] )    ? sanitize_textarea_field( trim( $row['info'] ) ) : '';
            $raw_mq  = isset( $row['marquee_items'] ) ? trim( $row['marquee_items'] ) : '';

            if ( ! $section || ! $heading ) { continue; }

            // Parse marquee items: each item is "icon|value|label", items separated by "||".
            $marquee = array();
            if ( $raw_mq ) {
                foreach ( explode( '||', $raw_mq ) as $mq_chunk ) {
                    $parts = explode( '|', $mq_chunk, 3 );
                    if ( count( $parts ) === 3 ) {
                        $marquee[] = array(
                            'icon'  => sanitize_text_field( trim( $parts[0] ) ),
                            'value' => sanitize_text_field( trim( $parts[1] ) ),
                            'label' => sanitize_text_field( trim( $parts[2] ) ),
                        );
                    }
                }
            }

            if ( 'expert' === $section ) {
                $existing = get_option( 'adn_expert_banner', array() );
                // Only seed if not already configured (don't overwrite real content).
                if ( empty( $existing['heading'] ) ) {
                    update_option( 'adn_expert_banner', array(
                        'enabled'       => true,
                        'heading'       => $heading,
                        'info'          => $info,
                        'marquee_items' => $marquee,
                    ), false );
                    // Mark in log so remove_all() can clean it up.
                    $log['adn_expert_banner'] = true;
                    update_option( self::LOG_OPTION, $log, false );
                    $count++;
                }
            }
        }

        return array( 'ok' => true, 'message' => '', 'summary' => array( 'banners' => $count ) );
    }

    // ── Notice importer ──────────────────────────────────────────────────────────

    private static function import_notice( $csv_path ) {
        $rows = self::read_csv( $csv_path );
        if ( false === $rows ) {
            return array( 'ok' => false, 'message' => sprintf( __( 'Cannot read: %s', ADN_TEXT_DOMAIN ), basename( $csv_path ) ), 'summary' => array() );
        }

        if ( ! class_exists( 'AH_Notice_Helper' ) ) {
            return array( 'ok' => false, 'message' => __( 'AH_Notice_Helper class not found. Activate the CMS plugin.', ADN_TEXT_DOMAIN ), 'summary' => array() );
        }

        $chosen = null;
        foreach ( $rows as $row ) {
            if ( 'active' !== ( isset( $row['status'] ) ? trim( $row['status'] ) : '' ) ) { continue; }
            if ( null === $chosen || 'high' === ( isset( $row['priority'] ) ? trim( $row['priority'] ) : '' ) ) {
                $chosen = $row;
                if ( 'high' === trim( $row['priority'] ) ) { break; }
            }
        }

        if ( ! $chosen ) {
            return array( 'ok' => true, 'message' => '', 'summary' => array( 'notices' => 0 ) );
        }

        $icon    = isset( $chosen['icon'] )       ? trim( $chosen['icon'] )                            : '';
        $title   = isset( $chosen['title'] )      ? sanitize_text_field( trim( $chosen['title'] ) )   : '';
        $message = isset( $chosen['text'] )       ? sanitize_text_field( trim( $chosen['text'] ) )    : '';
        $btn_url = isset( $chosen['link_url'] )   ? esc_url_raw( trim( $chosen['link_url'] ) )        : '';
        $btn_lbl = isset( $chosen['link_label'] ) ? sanitize_text_field( trim( $chosen['link_label'] ) ) : '';

        AH_Notice_Helper::save_notice( array(
            'enabled'      => true,
            'id'           => sanitize_key( $title ),
            'title'        => ( $icon ? $icon . ' ' : '' ) . $title,
            'message'      => $message,
            'button_label' => $btn_lbl,
            'button_url'   => $btn_url,
        ) );

        return array( 'ok' => true, 'message' => '', 'summary' => array( 'notices' => 1 ) );
    }

    // ── DB helpers ───────────────────────────────────────────────────────────────

    /** @return array{id:int,created:bool} */
    private static function ensure_parent_term( $pt, $name, $slug, $desc, $icon, $sort, $status ) {
        global $wpdb;
        $id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt}` WHERE slug = %s", $slug ) );
        if ( $id ) { return array( 'id' => $id, 'created' => false ); }
        $wpdb->insert( $pt, array(
            'name'        => $name,
            'slug'        => $slug,
            'description' => $desc,
            'icon_emoji'  => $icon ? $icon : null,
            'status'      => $status ? $status : 'active',
            'sort_order'  => (int) $sort,
        ) );
        return array( 'id' => (int) $wpdb->insert_id, 'created' => true );
    }

    /** @return array{id:int,created:bool} */
    private static function ensure_term( $tax, $type_id, $parent_term_id, $name, $slug, $sort, $status ) {
        global $wpdb;
        $id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$tax}` WHERE slug = %s AND type_id = %d", $slug, $type_id ) );
        if ( $id ) { return array( 'id' => $id, 'created' => false ); }
        $wpdb->insert( $tax, array(
            'type_id'        => $type_id,
            'parent_id'      => null,
            'parent_term_id' => $parent_term_id ? $parent_term_id : null,
            'name'           => $name,
            'slug'           => $slug,
            'status'         => $status ? $status : 'active',
            'sort_order'     => (int) $sort,
        ) );
        return array( 'id' => (int) $wpdb->insert_id, 'created' => true );
    }

    /**
     * Create (or find) a WP post, link it to taxonomy term IDs, and tag it as mock data.
     *
     * @return int 1 if created, 0 if already existed.
     */
    private static function ensure_wp_post( $title, $slug, $excerpt, $content, $term_ids, $featured, $dataset ) {
        global $wpdb;

        $existing = get_page_by_path( $slug, OBJECT, 'post' );
        $post_id  = 0;
        $created  = 0;

        if ( $existing instanceof WP_Post ) {
            $post_id = (int) $existing->ID;
        } else {
            $post_id = wp_insert_post( array(
                'post_type'    => 'post',
                'post_status'  => 'publish',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_excerpt' => $excerpt,
                'post_content' => $content ? $content : '<p>' . esc_html( $excerpt ) . '</p>',
            ) );
            if ( $post_id && ! is_wp_error( $post_id ) ) {
                $created = 1;
            }
        }

        if ( $post_id && ! is_wp_error( $post_id ) ) {
            // Tag as mock data for easy cleanup.
            update_post_meta( (int) $post_id, self::META_KEY,    '1' );
            update_post_meta( (int) $post_id, self::META_DS_KEY, sanitize_key( $dataset ) );

            $term_ids = array_values( array_filter( array_map( 'intval', (array) $term_ids ) ) );
            if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
                ( new AH_Content_Taxonomy_Model() )->sync_terms( 'wp_post', (int) $post_id, $term_ids );
            } else {
                $ct = $wpdb->prefix . 'ah_content_taxonomies';
                foreach ( $term_ids as $tid ) {
                    $wpdb->query( $wpdb->prepare(
                        "INSERT IGNORE INTO `{$ct}` (object_type, object_id, taxonomy_id) VALUES ('wp_post', %d, %d)",
                        (int) $post_id, $tid
                    ) );
                }
            }

            update_post_meta( (int) $post_id, '_ah_is_featured', $featured ? '1' : '0' );

            if ( $created ) {
                self::log_ids( 'wp_posts', array( (int) $post_id ) );
            }
        }

        return $created;
    }
}
