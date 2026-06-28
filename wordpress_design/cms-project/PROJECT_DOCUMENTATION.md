# CMS Project Documentation

## Overview
This repository contains a custom WordPress implementation split into a CMS-focused plugin and a theme that consumes the plugin data layer. The plugin acts as the system of record for structured content, admin management, forms, automation, and database-backed page building. The theme provides the public-facing presentation layer, custom routing, page templates, and front-end interactions.

This document is based on source inspection of the current workspace and does not modify the underlying code.

## Overall Features

### Plugin Features
- Custom admin panel
- Custom CMS database
- Dynamic page management
- Static page builder
- Form builder
- Rules engine
- Taxonomy management
- Site notice manager
- HTML styling editor
- Reference notes manager
- AJAX and REST APIs

### Theme Features
- Custom front-end design
- Category and topic pages
- Calculators
- Expert profiles
- Enquiry forms
- News bar
- Services and reviews
- FAQs and resources
- Guide pages
- Contact Form
- Guidence Form
- Post-level comments
- SEO and page builder

## 1. Architecture Summary

### Plugin layer
Primary entry point: [plugins/cms-plugin/ah-cms.php](plugins/cms-plugin/ah-cms.php)

The plugin is a full CMS engine rather than a small utility. Its responsibilities include:
- Bootstrapping the plugin, autoloading classes, and registering core hooks
- Providing an admin portal with menus, navigation, and administrative pages
- Creating and maintaining many custom database tables under the wp_ah_ namespace
- Implementing a form builder with forms, fields, and submissions
- Implementing an automation/workflow engine with conditions, actions, cron-based processing, logging, and retries
- Exposing public AJAX endpoints and shortcodes for front-end use
- Supporting static page rendering and custom template routing

### Theme layer
Primary entry point: [themes/advaithhomes_new/functions.php](themes/advaithhomes_new/functions.php)

The theme is mainly a presentation layer and front-end integration layer. It handles:
- Theme setup, menu registration, and asset loading
- Custom routing for CMS-driven URLs and virtual templates
- Page templates under [themes/advaithhomes_new/pages](themes/advaithhomes_new/pages)
- DB-backed features such as category settings, calculators, experts, and enquiry records
- Comment handling, form submission hooks, and AJAX-based interactions
- Theme-level helpers and integrations that read from the plugin tables

## 2. Implemented Feature Inventory

### A. CMS plugin features
- Admin portal and menu-driven CMS backend
- Custom database schema and installer/migration system
- Form builder with submissions
- Workflow / automation rules engine
- Static page rendering via shortcode and template interception
- Related links and resources shortcodes
- REST API registrations for visitor tracking and other data endpoints
- AJAX handlers for plugin-level settings and custom code injection
- Cron and scheduled automation support

### B. Theme features
- Custom routing for page definitions and taxonomy-driven templates
- Dynamic page templates for virtual routes and CMS-managed content
- Theme asset loader and template-specific asset injection
- Category settings database model for section-based configuration
- Calculator database model for admin-managed calculators
- Expert/profile database model for team profiles
- Enquiry form database storage and status tracking
- Comment submission, moderation, and load-more pagination
- SEO and site notice helpers
- Front-end API/data access layer for plugin-backed content

## 3. Active vs Likely Legacy / Unused Areas

### Confirmed active areas
These areas are wired through the main entry points and are actively referenced by the current implementation:
- Plugin bootstrap and admin bootstrap
- Custom DB schema and installer paths
- Form builder and workflow engine
- Static page rendering and shortcode helpers
- Theme routing and page-template system
- Calculator, category settings, expert, and enquiry models
- Comment and form AJAX flows

### Likely legacy or partially inactive areas
These areas are present in the repository but should be treated as candidates for cleanup or archival until runtime verification confirms they are still used:
- Multiple older theme folders under [themes](themes) such as [themes/ah_canehouse](themes/ah_canehouse) and [themes/new_theme_template](themes/new_theme_template) are present, but the active implementation in this review is centered on [themes/advaithhomes_new](themes/advaithhomes_new)
- Some plugin documentation and internal notes appear to reflect a larger feature surface than the current active frontend wiring suggests, especially around advanced admin features and older data structures
- A number of database table definitions and helper classes exist in the plugin and theme, but some may represent earlier experimentation or incomplete migrations rather than current user-facing functionality

### Recommendation
Treat the following as “needs verification” rather than “confirmed dead”:
- Older theme variants
- Legacy admin pages or table-driven modules that are not referenced by the current theme templates
- Large sections of documentation that may describe future or historical functionality

## 4. Project Readiness Notes
- The plugin/theme split is coherent and modular
- The plugin serves as the data and administration backbone
- The theme relies on the plugin for structured content and routing
- Documentation already exists in [plugins/cms-plugin/docs](plugins/cms-plugin/docs), but a concise top-level summary is still useful for clients and portfolio review

## 5. Suggested Next Steps
1. Keep the current plugin-theme architecture as the canonical implementation
2. Archive or clearly label older theme variants that are no longer active
3. Add a simple deployment note for the plugin/theme pair
4. Maintain a single authoritative feature inventory for future handoff
