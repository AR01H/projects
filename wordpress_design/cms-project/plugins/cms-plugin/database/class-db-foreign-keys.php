<?php
defined( 'ABSPATH' ) || exit;

/**
 * Foreign key constraints - applied after all tables exist.
 * Each constraint is checked before adding (idempotent).
 * Add new constraints here. Never put CREATE TABLE or data here.
 */
class AH_DB_Foreign_Keys {

	public static function apply(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		$fks = array(
			"ALTER TABLE {$p}ah_taxonomies
				ADD CONSTRAINT fk_tax_type   FOREIGN KEY (type_id)   REFERENCES {$p}ah_taxonomy_types(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_tax_parent FOREIGN KEY (parent_id) REFERENCES {$p}ah_taxonomies(id)     ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_admin_users
				ADD CONSTRAINT fk_au_role   FOREIGN KEY (role_id)   REFERENCES {$p}ah_admin_roles(id),
				ADD CONSTRAINT fk_au_avatar FOREIGN KEY (avatar_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_site_settings
				ADD CONSTRAINT fk_ss_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_pages
				ADD CONSTRAINT fk_pg_img  FOREIGN KEY (og_image_id) REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_pg_cr   FOREIGN KEY (created_by)  REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL,
				ADD CONSTRAINT fk_pg_up   FOREIGN KEY (updated_by)  REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_page_sections
				ADD CONSTRAINT fk_ps_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_ps_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_news_bar_items
				ADD CONSTRAINT fk_nbi_user FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_hero
				ADD CONSTRAINT fk_hero_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_hero_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_hero_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_highlights
				ADD CONSTRAINT fk_hl_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_hl_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_why_us
				ADD CONSTRAINT fk_wu_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_wu_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_why_us_cards
				ADD CONSTRAINT fk_wuc_wu  FOREIGN KEY (why_us_id) REFERENCES {$p}ah_section_why_us(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_wuc_img FOREIGN KEY (image_id)  REFERENCES {$p}ah_media(id)          ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_guide_through
				ADD CONSTRAINT fk_gt_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_gt_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_gt_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_guide_through_points
				ADD CONSTRAINT fk_gtp_guide FOREIGN KEY (guide_id) REFERENCES {$p}ah_section_guide_through(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_section_stack_items
				ADD CONSTRAINT fk_si_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_si_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_difference
				ADD CONSTRAINT fk_diff_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_diff_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_difference_table
				ADD CONSTRAINT fk_dt_diff FOREIGN KEY (difference_id) REFERENCES {$p}ah_section_difference(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_section_featured_properties
				ADD CONSTRAINT fk_fp_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_fp_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_featured_properties_items
				ADD CONSTRAINT fk_fpi_sec FOREIGN KEY (section_id) REFERENCES {$p}ah_section_featured_properties(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_fpi_img FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_experience
				ADD CONSTRAINT fk_exp_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_exp_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_experience_cards
				ADD CONSTRAINT fk_ec_sec FOREIGN KEY (section_id) REFERENCES {$p}ah_section_experience(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_ec_img FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_why_required
				ADD CONSTRAINT fk_wr_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_wr_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_why_required_cards
				ADD CONSTRAINT fk_wrc_sec FOREIGN KEY (section_id) REFERENCES {$p}ah_section_why_required(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_reviews
				ADD CONSTRAINT fk_rv_img  FOREIGN KEY (reviewer_image_id) REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_rv_user FOREIGN KEY (created_by)        REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_reviews_header
				ADD CONSTRAINT fk_srh_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_faqs
				ADD CONSTRAINT fk_faq_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_faq_user FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_section_faq_header
				ADD CONSTRAINT fk_sfh_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_services
				ADD CONSTRAINT fk_svc_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_svc_user FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_service_bullet_points
				ADD CONSTRAINT fk_sbp_svc FOREIGN KEY (service_id) REFERENCES {$p}ah_services(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_services_page_header
				ADD CONSTRAINT fk_sph_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_sph_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_service_taxonomies
				ADD CONSTRAINT fk_stax_svc FOREIGN KEY (service_id)  REFERENCES {$p}ah_services(id)  ON DELETE CASCADE,
				ADD CONSTRAINT fk_stax_tax FOREIGN KEY (taxonomy_id) REFERENCES {$p}ah_taxonomies(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_about_page_header
				ADD CONSTRAINT fk_aph_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_aph_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_about_story
				ADD CONSTRAINT fk_ast_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_ast_img  FOREIGN KEY (image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_ast_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_about_story_points
				ADD CONSTRAINT fk_asp_story FOREIGN KEY (story_id) REFERENCES {$p}ah_about_story(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_team_members
				ADD CONSTRAINT fk_tm_photo FOREIGN KEY (photo_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_tm_user  FOREIGN KEY (created_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_about_values
				ADD CONSTRAINT fk_av_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_av_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_posts
				ADD CONSTRAINT fk_pt_feat   FOREIGN KEY (featured_image_id) REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_pt_banner FOREIGN KEY (banner_image_id)   REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_pt_author FOREIGN KEY (author_id)         REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_post_taxonomies
				ADD CONSTRAINT fk_ptax_post FOREIGN KEY (post_id)     REFERENCES {$p}ah_posts(id)      ON DELETE CASCADE,
				ADD CONSTRAINT fk_ptax_tax  FOREIGN KEY (taxonomy_id) REFERENCES {$p}ah_taxonomies(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_post_table_blocks
				ADD CONSTRAINT fk_ptb_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_post_links
				ADD CONSTRAINT fk_pl_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_post_stack_items
				ADD CONSTRAINT fk_psi_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_psi_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_post_listing_page_header
				ADD CONSTRAINT fk_plph_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_plph_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_client_stories_header
				ADD CONSTRAINT fk_csh_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_csh_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_client_story_images
				ADD CONSTRAINT fk_csi_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_csi_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_client_users_journey
				ADD CONSTRAINT fk_cuj_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_cuj_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_client_gallery
				ADD CONSTRAINT fk_cg_page FOREIGN KEY (page_id)  REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_cg_img  FOREIGN KEY (image_id) REFERENCES {$p}ah_media(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_client_video_links
				ADD CONSTRAINT fk_cvl_page  FOREIGN KEY (page_id)      REFERENCES {$p}ah_pages(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_cvl_thumb FOREIGN KEY (thumbnail_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_contact_page_config
				ADD CONSTRAINT fk_cpc_page FOREIGN KEY (page_id)    REFERENCES {$p}ah_pages(id)       ON DELETE CASCADE,
				ADD CONSTRAINT fk_cpc_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_footer_config
				ADD CONSTRAINT fk_fc_logo FOREIGN KEY (logo_id)    REFERENCES {$p}ah_media(id)       ON DELETE SET NULL,
				ADD CONSTRAINT fk_fc_user FOREIGN KEY (updated_by) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_floating_widgets
				ADD CONSTRAINT fk_fw_icon FOREIGN KEY (icon_id) REFERENCES {$p}ah_media(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_news_detail_big_cards
				ADD CONSTRAINT fk_ndbc_post FOREIGN KEY (post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_news_detail_card_links
				ADD CONSTRAINT fk_ndcl_card FOREIGN KEY (card_id) REFERENCES {$p}ah_news_detail_big_cards(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_related_posts
				ADD CONSTRAINT fk_rp_post    FOREIGN KEY (post_id)         REFERENCES {$p}ah_posts(id) ON DELETE CASCADE,
				ADD CONSTRAINT fk_rp_related FOREIGN KEY (related_post_id) REFERENCES {$p}ah_posts(id) ON DELETE CASCADE",

			"ALTER TABLE {$p}ah_audit_logs
				ADD CONSTRAINT fk_al_user FOREIGN KEY (user_id) REFERENCES {$p}ah_admin_users(id) ON DELETE SET NULL",

			"ALTER TABLE {$p}ah_random_blog_card_configs
				ADD CONSTRAINT fk_rbc_page FOREIGN KEY (page_id) REFERENCES {$p}ah_pages(id) ON DELETE CASCADE",
		);

		foreach ( $fks as $sql ) {
			preg_match_all( '/ADD CONSTRAINT (\w+)/', $sql, $matches );
			$already_exists = false;
			foreach ( $matches[1] as $name ) {
				if ( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME = %s",
					$name
				) ) ) {
					$already_exists = true;
					break;
				}
			}
			if ( ! $already_exists ) {
				$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
		}
	}

	/**
	 * Drop FK constraints that reference ah_admin_users / ah_media via WP IDs.
	 * Uses INFORMATION_SCHEMA so it only drops what actually exists.
	 */
	public static function drop_broken(): void {
		global $wpdb;

		$target_names = array(
			'fk_ss_user','fk_ps_user','fk_pg_cr','fk_pg_up','fk_nbi_user',
			'fk_hero_user','fk_wu_user','fk_gt_user','fk_diff_user',
			'fk_fp_user','fk_exp_user','fk_wr_user','fk_svc_user',
			'fk_sph_user','fk_aph_user','fk_ast_user','fk_tm_user',
			'fk_rv_user','fk_faq_user','fk_pt_author','fk_plph_user',
			'fk_csh_user','fk_cpc_user','fk_fc_user','fk_al_user',
			'fk_rv_img','fk_svc_img','fk_tm_photo','fk_ast_img',
			'fk_av_img','fk_hero_img','fk_gt_img','fk_wuc_img',
			'fk_fpi_img','fk_ec_img','fk_hl_icon','fk_si_icon',
			'fk_csi_img','fk_cuj_img','fk_cg_img','fk_cvl_thumb',
			'fk_fc_logo','fk_pg_img','fk_psi_icon','fk_fw_icon','fk_au_avatar',
		);

		$placeholders = implode( ',', array_fill( 0, count( $target_names ), '%s' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$existing = $wpdb->get_results( $wpdb->prepare(
			"SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
			 WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = 'FOREIGN KEY'
			 AND CONSTRAINT_NAME IN ({$placeholders})",
			...$target_names
		) );

		if ( empty( $existing ) ) return;

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );
		foreach ( $existing as $row ) {
			$wpdb->query( "ALTER TABLE `{$row->TABLE_NAME}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );
	}
}
