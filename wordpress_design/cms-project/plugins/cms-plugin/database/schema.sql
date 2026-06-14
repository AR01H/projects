-- ============================================================
--  WEBSITE CMS DATABASE SCHEMA
--  MySQL 8.0+
--  Covers: All pages, Admin Portal, Sections, Blogs/News,
--          Reviews, FAQ, Team, Services, Contact, Media, etc.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- 1. TAXONOMY (Tags, Categories, Subtags)
-- ============================================================

CREATE TABLE taxonomy_types (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,           -- e.g. 'category', 'tag', 'subtag', 'service_type'
    slug        VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Defines the kinds of taxonomy available (categories, tags, etc.)';

CREATE TABLE taxonomies (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_id         INT UNSIGNED NOT NULL,
    parent_id       INT UNSIGNED DEFAULT NULL,   -- for hierarchical categories/subcategories
    name            VARCHAR(200) NOT NULL,
    slug            VARCHAR(220) NOT NULL,
    description     TEXT,
    meta_title      VARCHAR(255),
    meta_description TEXT,
    status          ENUM('active','inactive') DEFAULT 'active',
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_taxonomy_slug (type_id, slug),
    FOREIGN KEY (type_id)    REFERENCES taxonomy_types(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id)  REFERENCES taxonomies(id)     ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='All tags, categories, subtags unified. Self-referencing for hierarchy.';


-- ============================================================
-- 2. MEDIA LIBRARY
-- ============================================================

CREATE TABLE media (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_name   VARCHAR(255) NOT NULL,
    file_path   VARCHAR(500) NOT NULL,           -- relative path in uploads dir
    file_url    VARCHAR(500) NOT NULL,
    mime_type   VARCHAR(100),
    file_size   INT UNSIGNED,                    -- bytes
    width       SMALLINT UNSIGNED,              -- for images
    height      SMALLINT UNSIGNED,
    alt_text    VARCHAR(255),
    caption     TEXT,
    uploaded_by INT UNSIGNED,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Central media library. All images/files reference this table.';


-- ============================================================
-- 3. USERS & ADMIN
-- ============================================================

CREATE TABLE admin_roles (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL UNIQUE,     -- 'super_admin', 'editor', 'author'
    permissions JSON,                            -- JSON array of permission keys
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Role definitions for admin portal access control.';

CREATE TABLE admin_users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id         INT UNSIGNED NOT NULL,
    full_name       VARCHAR(150) NOT NULL,
    email           VARCHAR(200) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    avatar_id       INT UNSIGNED,
    status          ENUM('active','inactive','suspended') DEFAULT 'active',
    last_login_at   TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id)   REFERENCES admin_roles(id),
    FOREIGN KEY (avatar_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Admin portal users with role-based access.';


-- ============================================================
-- 4. SITE SETTINGS (Global Config)
-- ============================================================

CREATE TABLE site_settings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(150) NOT NULL UNIQUE,
    setting_val TEXT,
    field_type  ENUM('text','textarea','image','color','url','email','phone','toggle','json') DEFAULT 'text',
    group_name  VARCHAR(100),                    -- 'general', 'social', 'seo', 'contact'
    label       VARCHAR(200),
    is_visible  TINYINT(1) DEFAULT 1,
    updated_by  INT UNSIGNED,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Key-value store for global site config: logo, colors, phone, social links, etc.';

-- Sample keys: site_logo, site_name, whatsapp_number, footer_tagline,
--              facebook_url, twitter_url, linkedin_url, contact_email,
--              contact_phone, google_maps_embed, primary_color, etc.


-- ============================================================
-- 5. NAVIGATION MENUS
-- ============================================================

CREATE TABLE nav_menus (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,           -- 'primary', 'footer'
    slug        VARCHAR(120) NOT NULL UNIQUE,
    status      ENUM('active','inactive') DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Named navigation menus (header, footer, etc.)';

CREATE TABLE nav_menu_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id     INT UNSIGNED NOT NULL,
    parent_id   INT UNSIGNED DEFAULT NULL,       -- for submenus
    label       VARCHAR(150) NOT NULL,
    url         VARCHAR(500),                    -- custom URL or internal path
    page_slug   VARCHAR(200),                    -- link to a managed page
    target      ENUM('_self','_blank') DEFAULT '_self',
    icon_class  VARCHAR(100),
    sort_order  INT DEFAULT 0,
    status      ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (menu_id)  REFERENCES nav_menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES nav_menu_items(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Menu items with parent for dropdown/submenu support.';


-- ============================================================
-- 6. PAGES (Static page registry)
-- ============================================================

CREATE TABLE pages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL UNIQUE,
    page_type       ENUM('home','services','contact','client_stories','blog_listing','news_listing','custom') DEFAULT 'custom',
    meta_title      VARCHAR(255),
    meta_description TEXT,
    meta_keywords   TEXT,
    og_image_id     INT UNSIGNED,
    status          ENUM('active','inactive','draft') DEFAULT 'active',
    created_by      INT UNSIGNED,
    updated_by      INT UNSIGNED,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (og_image_id) REFERENCES media(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES admin_users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by)  REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Top-level page registry. Each page slug maps to front-end routes.';

-- ============================================================
-- 8. NEWS BAR (Scrolling ticker)
-- ============================================================

CREATE TABLE news_bar_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    text        VARCHAR(500) NOT NULL,
    link_url    VARCHAR(500),
    link_target ENUM('_self','_blank') DEFAULT '_self',
    status      ENUM('active','inactive') DEFAULT 'active',
    sort_order  INT DEFAULT 0,
    start_date  DATE,
    end_date    DATE,
    created_by  INT UNSIGNED,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Scrolling news ticker items shown in the top news bar on all pages.';

-- ============================================================
-- 10. REVIEWS / TESTIMONIALS
-- ============================================================

CREATE TABLE reviews (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reviewer_name   VARCHAR(200) NOT NULL,
    reviewer_title  VARCHAR(200),                -- job title / company
    reviewer_image_id INT UNSIGNED,
    review_text     TEXT NOT NULL,
    rating          TINYINT UNSIGNED DEFAULT 5,  -- 1–5
    source          ENUM('manual','google','facebook','other') DEFAULT 'manual',
    is_featured     TINYINT(1) DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',
    sort_order      INT DEFAULT 0,
    created_by      INT UNSIGNED,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewer_image_id) REFERENCES media(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)        REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='All reviews/testimonials. Used in homepage slider and Client Stories page.';

CREATE TABLE section_reviews_header (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id         INT UNSIGNED NOT NULL,
    heading         VARCHAR(255),
    description     TEXT,
    more_link_text  VARCHAR(100),
    more_link_url   VARCHAR(500),
    is_visible      TINYINT(1) DEFAULT 1,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Header config for the Reviews section on each page.';


-- ============================================================
-- 11. FAQ
-- ============================================================

CREATE TABLE faqs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question    TEXT NOT NULL,
    answer      TEXT NOT NULL,
    link_text   VARCHAR(150),
    link_url    VARCHAR(500),
    page_id     INT UNSIGNED,                    -- if attached to specific page, else global
    sort_order  INT DEFAULT 0,
    status      ENUM('active','inactive') DEFAULT 'active',
    created_by  INT UNSIGNED,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id)    REFERENCES pages(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='FAQ items. Can be page-specific or global (page_id NULL = global).';

CREATE TABLE section_faq_header (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id     INT UNSIGNED NOT NULL UNIQUE,
    heading     VARCHAR(255),
    description TEXT,
    is_visible  TINYINT(1) DEFAULT 1,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='FAQ section heading per page.';

-- ============================================================
-- 14. BLOGS / ARTICLES / NEWSLETTERS / NEWS
-- ============================================================

CREATE TABLE posts (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_type           ENUM('blog','article','news','newsletter','guide') NOT NULL DEFAULT 'blog',
    title               VARCHAR(400) NOT NULL,
    slug                VARCHAR(420) NOT NULL,
    excerpt             TEXT,
    content             LONGTEXT,
    featured_image_id   INT UNSIGNED,
    banner_image_id     INT UNSIGNED,
    author_id           INT UNSIGNED,
    status              ENUM('active','inactive','draft','scheduled') DEFAULT 'draft',
    is_featured         TINYINT(1) DEFAULT 0,
    published_at        TIMESTAMP NULL,
    scheduled_at        TIMESTAMP NULL,
    meta_title          VARCHAR(255),
    meta_description    TEXT,
    meta_keywords       TEXT,
    view_count          INT UNSIGNED DEFAULT 0,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_post_slug (post_type, slug),
    FOREIGN KEY (featured_image_id) REFERENCES media(id) ON DELETE SET NULL,
    FOREIGN KEY (banner_image_id)   REFERENCES media(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id)         REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Unified content table for blogs, articles, news, newsletters, guides. post_type determines which listing page it appears on.';

CREATE TABLE post_taxonomies (
    post_id     INT UNSIGNED NOT NULL,
    taxonomy_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, taxonomy_id),
    FOREIGN KEY (post_id)    REFERENCES posts(id)      ON DELETE CASCADE,
    FOREIGN KEY (taxonomy_id) REFERENCES taxonomies(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Many-to-many: posts ↔ tags/categories/subtags.';

-- Additional structured content blocks inside a blog post
CREATE TABLE post_table_blocks (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id     INT UNSIGNED NOT NULL,
    heading     VARCHAR(255),
    table_data  JSON NOT NULL,                   -- [{col1:'', col2:'', ...}, ...]
    sort_order  INT DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Tabular data blocks within a post/blog body.';

CREATE TABLE post_links (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(255),
    url         VARCHAR(500) NOT NULL,
    link_type   ENUM('official','reference','related','cta') DEFAULT 'reference',
    sort_order  INT DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Multiple official/reference links attached to a post.';

CREATE TABLE post_stack_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id     INT UNSIGNED NOT NULL,
    name        VARCHAR(150),
    icon_id     INT UNSIGNED,
    link_url    VARCHAR(500),
    sort_order  INT DEFAULT 0,
    FOREIGN KEY (post_id)  REFERENCES posts(id)   ON DELETE CASCADE,
    FOREIGN KEY (icon_id)  REFERENCES media(id)   ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Stack/tool details associated with a specific blog/article.';


-- ============================================================
-- 15. BLOG / NEWS LISTING PAGE SECTIONS
-- ============================================================

CREATE TABLE post_listing_page_header (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id         INT UNSIGNED NOT NULL UNIQUE,
    main_heading    VARCHAR(255),
    description     TEXT,
    is_visible      TINYINT(1) DEFAULT 1,
    updated_by      INT UNSIGNED,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id)    REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Header section for blog/news listing pages.';


-- ============================================================
-- 16. CLIENT STORIES PAGE
-- ============================================================

CREATE TABLE client_stories_header (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id         INT UNSIGNED NOT NULL UNIQUE,
    heading         VARCHAR(255),
    information     TEXT,
    is_visible      TINYINT(1) DEFAULT 1,
    updated_by      INT UNSIGNED,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id)    REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Header for Client Stories page.';

CREATE TABLE client_story_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id     INT UNSIGNED NOT NULL,
    image_id    INT UNSIGNED NOT NULL,
    review_text TEXT,
    sort_order  INT DEFAULT 0,
    status      ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (page_id)  REFERENCES pages(id)  ON DELETE CASCADE,
    FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Multiple images each with a user review quote in Client Stories header.';

CREATE TABLE client_users_journey (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id         INT UNSIGNED NOT NULL,
    heading         VARCHAR(255),
    basic_info      TEXT,
    image_id        INT UNSIGNED,
    user_name       VARCHAR(200),
    user_info       TEXT,
    sort_order      INT DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (page_id)  REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='User journey profiles: image + basic info, on Client Stories page.';

CREATE TABLE client_gallery (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id     INT UNSIGNED NOT NULL,
    image_id    INT UNSIGNED NOT NULL,
    width_class ENUM('small','medium','large','full') DEFAULT 'medium',
    sort_order  INT DEFAULT 0,
    status      ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (page_id)  REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Gallery images (different sizes) on Client Stories page.';

CREATE TABLE client_video_links (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id     INT UNSIGNED NOT NULL,
    heading     VARCHAR(255),
    video_url   VARCHAR(500) NOT NULL,           -- YouTube/Vimeo
    thumbnail_id INT UNSIGNED,
    sort_order  INT DEFAULT 0,
    status      ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (page_id)      REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (thumbnail_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Video links with individual headings on Client Stories page.';


-- ============================================================
-- 17. CONTACT PAGE
-- ============================================================

CREATE TABLE contact_page_config (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id         INT UNSIGNED NOT NULL UNIQUE,
    heading         VARCHAR(255),
    basic_info      TEXT,
    email           VARCHAR(200),
    whatsapp_number VARCHAR(30),
    phone_number    VARCHAR(30),
    maps_embed_url  TEXT,                        -- Google Maps iframe src
    is_visible      TINYINT(1) DEFAULT 1,
    updated_by      INT UNSIGNED,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id)    REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Right-side contact details for Contact page: email, WhatsApp, phone, map.';

CREATE TABLE contact_form_submissions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(200) NOT NULL,
    email           VARCHAR(200) NOT NULL,
    phone           VARCHAR(30),
    subject         VARCHAR(300),
    message         TEXT NOT NULL,
    ip_address      VARCHAR(45),
    is_read         TINYINT(1) DEFAULT 0,
    status          ENUM('new','in_progress','resolved','spam') DEFAULT 'new',
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Stores all Contact page form submissions for admin review.';


-- ============================================================
-- 18. FOOTER SECTIONS
-- ============================================================

CREATE TABLE footer_config (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    logo_id         INT UNSIGNED,
    site_name       VARCHAR(150),
    tagline         TEXT,
    copyright_text  VARCHAR(300),
    get_in_touch_heading VARCHAR(150),
    is_visible      TINYINT(1) DEFAULT 1,
    updated_by      INT UNSIGNED,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (logo_id)    REFERENCES media(id)      ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Footer global config: logo, name, tagline, copyright text.';

CREATE TABLE footer_contact_links (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label       VARCHAR(150),
    value       VARCHAR(300) NOT NULL,           -- phone number / email / address
    link_url    VARCHAR(500),
    icon_class  VARCHAR(100),
    sort_order  INT DEFAULT 0,
    status      ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB COMMENT='Get In Touch contact items shown in footer.';

CREATE TABLE footer_social_links (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    platform    VARCHAR(80) NOT NULL,            -- 'facebook','instagram', etc.
    url         VARCHAR(500) NOT NULL,
    icon_class  VARCHAR(100),
    sort_order  INT DEFAULT 0,
    status      ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB COMMENT='Social media icon links in footer.';


-- ============================================================
-- 19. RANDOM BLOG CARDS (Cross-page scroll widgets)
-- ============================================================

CREATE TABLE random_blog_card_configs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id         INT UNSIGNED NOT NULL,
    heading         VARCHAR(255),
    more_link_text  VARCHAR(100),
    more_link_url   VARCHAR(500),
    post_type_filter SET('blog','article','news','newsletter','guide') DEFAULT 'blog',
    max_cards       TINYINT DEFAULT 3,
    is_visible      TINYINT(1) DEFAULT 1,
    sort_position   INT DEFAULT 0,               -- which scroll position on the page
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Config for randomly placed scrolling blog card widgets on any page.';


-- ============================================================
-- 20. NEWS DETAIL PAGE - Big Cards & Related Posts
-- ============================================================

CREATE TABLE news_detail_big_cards (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id         INT UNSIGNED NOT NULL,        -- the news post this card belongs to
    heading         VARCHAR(255),
    information     TEXT,
    sort_order      INT DEFAULT 0,
    status          ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Big info cards on a news detail page (multiple per post).';

CREATE TABLE news_detail_card_links (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    card_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(255),
    url         VARCHAR(500) NOT NULL,
    sort_order  INT DEFAULT 0,
    FOREIGN KEY (card_id) REFERENCES news_detail_big_cards(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Multiple links inside each big news detail card.';

CREATE TABLE related_posts (
    post_id         INT UNSIGNED NOT NULL,
    related_post_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, related_post_id),
    FOREIGN KEY (post_id)         REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (related_post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Manual or auto related posts linking for blog/news detail pages.';


-- ============================================================
-- 21. FLOATING WIDGETS CONFIG
-- ============================================================

CREATE TABLE floating_widgets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    widget_type     ENUM('whatsapp','contact_us','chat','custom') NOT NULL,
    label           VARCHAR(150),
    link_url        VARCHAR(500),
    icon_id         INT UNSIGNED,
    bg_color        VARCHAR(20),
    position        ENUM('bottom_right','bottom_left','top_right','top_left') DEFAULT 'bottom_right',
    is_visible      TINYINT(1) DEFAULT 1,
    exclude_pages   JSON,                        -- array of page slugs to hide on
    FOREIGN KEY (icon_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Config for floating action widgets like WhatsApp icon and Contact Us button.';


-- ============================================================
-- 22. AUDIT LOG
-- ============================================================

CREATE TABLE audit_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED,
    action      VARCHAR(100) NOT NULL,           -- 'create','update','delete','login'
    table_name  VARCHAR(100),
    record_id   INT UNSIGNED,
    old_values  JSON,
    new_values  JSON,
    ip_address  VARCHAR(45),
    user_agent  VARCHAR(500),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Audit trail for all admin actions: who changed what and when.';


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- ============================================================