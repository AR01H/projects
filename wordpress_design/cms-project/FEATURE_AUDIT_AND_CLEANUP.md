# Feature Audit and Cleanup Review

## Purpose
This document gives a complete end-to-end feature and usage list for the project so it can be shown to a client, used for quoting, or reviewed for cleanup.

## 1. Plugin-Level Features and Usage
These are the features implemented in the plugin layer and the business purpose they serve.

### 1.1 CMS Admin Panel
- Purpose: Full backend management system for the website
- Usage: Used to manage content, site sections, and admin operations
- Main files: [plugins/cms-plugin/ah-cms.php](plugins/cms-plugin/ah-cms.php), [plugins/cms-plugin/admin/class-admin-bootstrap.php](plugins/cms-plugin/admin/class-admin-bootstrap.php)

### 1.2 Custom Database System
- Purpose: Stores content outside standard WordPress posts/pages where needed
- Usage: Powers pages, sections, posts, services, FAQs, reviews, media, settings, and other structured modules
- Main files: [plugins/cms-plugin/database/class-db-schema.php](plugins/cms-plugin/database/class-db-schema.php), [plugins/cms-plugin/database/class-db-installer.php](plugins/cms-plugin/database/class-db-installer.php)

### 1.3 Form Builder
- Purpose: Create custom forms and collect submissions
- Usage: Used for contact forms, enquiry forms, and other interaction forms
- Main files: [plugins/cms-plugin/inc/class-form-builder.php](plugins/cms-plugin/inc/class-form-builder.php)

### 1.4 Workflow / Rules Engine
- Purpose: Automates business logic through rules and actions
- Usage: Used for conditional workflows, scheduled actions, tracking, and automated processing
- Main files: [plugins/cms-plugin/inc/class-workflow-manager.php](plugins/cms-plugin/inc/class-workflow-manager.php)

### 1.5 Static Page System
- Purpose: Create custom static pages and render them through the plugin
- Usage: Used for content pages that need controlled HTML output
- Main files: [plugins/cms-plugin/template-static-page.php](plugins/cms-plugin/template-static-page.php)

### 1.6 Shortcodes and Content Components
- Purpose: Embed reusable content blocks into pages and posts
- Usage: Used for forms, static pages, resources, and related links
- Main files: [plugins/cms-plugin/ah-cms.php](plugins/cms-plugin/ah-cms.php)

### 1.7 AJAX and REST Features
- Purpose: Support front-end interactions and API-based actions
- Usage: Used for form submission, settings save, tracking, and dynamic actions
- Main files: [plugins/cms-plugin/api/class-rest-routes.php](plugins/cms-plugin/api/class-rest-routes.php), [plugins/cms-plugin/admin/ajax/class-ajax-handlers.php](plugins/cms-plugin/admin/ajax/class-ajax-handlers.php)

### 1.8 Cron and Scheduled Actions
- Purpose: Run background or delayed processes
- Usage: Used for automation, deferred actions, and recurring logic
- Main files: [plugins/cms-plugin/ah-cms.php](plugins/cms-plugin/ah-cms.php)

## 2. Theme-Level Features and Usage
These are the features implemented in the front-end theme layer and how they are used.

### 2.1 Theme Bootstrap and Setup
- Purpose: Initialize the theme, menus, theme support, and assets
- Usage: Used on every front-end page load
- Main files: [themes/advaithhomes_new/functions.php](themes/advaithhomes_new/functions.php)

### 2.2 Custom Routing
- Purpose: Create custom URLs and virtual page behavior
- Usage: Used for category-based pages, topic-based pages, and custom route handling
- Main file: [themes/advaithhomes_new/includes/core_routing.php](themes/advaithhomes_new/includes/core_routing.php)

### 2.3 Page Templates
- Purpose: Render custom page layouts for the public website
- Usage: Used for home, contact, FAQs, tools, guidance, news, and other special pages
- Main folder: [themes/advaithhomes_new/pages](themes/advaithhomes_new/pages)

### 2.4 Calculators
- Purpose: Provide interactive calculators on the website
- Usage: Used as tool-based content modules for user interaction
- Main files: [themes/advaithhomes_new/includes/class-calculator-db.php](themes/advaithhomes_new/includes/class-calculator-db.php)

### 2.5 Category and Topic Management
- Purpose: Organize content by category and topic
- Usage: Used for guide/category browsing and CMS-driven content grouping
- Main files: [themes/advaithhomes_new/includes/class-category-settings.php](themes/advaithhomes_new/includes/class-category-settings.php), [themes/advaithhomes_new/apis/services_cms.php](themes/advaithhomes_new/apis/services_cms.php)

### 2.6 Experts / Team Profiles
- Purpose: Display expert or team member information
- Usage: Used for profiles, listings, and detailed profile pages
- Main file: [themes/advaithhomes_new/includes/class-expert-db.php](themes/advaithhomes_new/includes/class-expert-db.php)

### 2.7 Enquiry and Lead Management
- Purpose: Capture enquiries from the front end and store them for review
- Usage: Used for contact and enquiry forms
- Main file: [themes/advaithhomes_new/includes/class-adn-enquiry.php](themes/advaithhomes_new/includes/class-adn-enquiry.php)

### 2.8 Comments and Interaction Features
- Purpose: Allow comments, moderation, and load-more behavior
- Usage: Used on content pages and posts
- Main file: [themes/advaithhomes_new/includes/comment-callbacks.php](themes/advaithhomes_new/includes/comment-callbacks.php)

### 2.9 SEO and Notice Features
- Purpose: Improve site presentation and content visibility
- Usage: Used for site-wide notice popups, SEO hooks, and structured front-end behavior
- Main files: [themes/advaithhomes_new/includes/seo.php](themes/advaithhomes_new/includes/seo.php)

## 3. End-to-End Feature List for Client Discussion
This is the simplified full-scope list you can show to a client or use for pricing.

### Core CMS
- Custom admin panel
- Custom database-driven content management
- Dynamic page and section management
- Static page management
- Custom URL routing

### Content Modules
- Calculators
- Categories
- Topics
- Guides and articles
- News bar / news ticker
- Services
- Reviews / testimonials
- FAQs
- Team / expert profiles
- Related links and resources

### Business Logic and Automation
- Rules engine
- Conditional logic
- Workflow actions
- Scheduled processing
- Activity and action tracking

### Forms and Leads
- Contact forms
- Enquiry forms
- Form submission storage
- Admin review of incoming leads

### Front-End Experience
- Custom theme design integration
- Category/topic-based templates
- SEO support
- Comments and interaction features
- Media and resource display

### Admin and Maintenance
- Database installation and upgrades
- Audit logging
- Admin actions for data rebuild and cleanup
- Content management utilities

## 4. Cleanup and Scope Review
The project is rich and feature-complete, but some parts may be older, experimental, or less actively used. The main cleanup focus should be:
- Keep the active plugin and active theme as the main production scope
- Archive or clearly separate older theme variants
- Review any feature that is coded but not currently connected to public templates or admin flows
- Keep one clear source of truth for documentation and future quoting

## 5. Suggested Quotation Categories
If you want to ask for the amount, you can present the scope in these buckets:
1. Core CMS development
2. Content modules and custom database architecture
3. Forms, leads, and automation
4. Front-end theme and custom routing
5. Admin features and maintenance tools
