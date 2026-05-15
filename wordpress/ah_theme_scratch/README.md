# Advaith Homes: Elite Admin Portal & Page Builder

A premium, data-driven administrative infrastructure for WordPress, designed to manage high-fidelity property portals, dynamic articles, and complex navigation systems with zero coding requirement.

## 🚀 Key Features

### 1. Unified Portal Dashboard
- **Centralized Hub**: A single "Advaith Homes" admin menu that consolidates all platform management.
- **Card-Based Interface**: A high-fidelity grid dashboard that provides instant access to all content departments (Articles, Projects, Leads, etc.).
- **System Status**: Real-time indicators and professional branding throughout the backend.

### 2. Infinite Visual Page Builder
- **JSON-Driven Core**: Every detail page is powered by a structured JSON object, ensuring consistency and performance.
- **Visual Admin Interface**: A form-based builder that automatically generates JSON in real-time.
- **Premium Components**: Native support for:
  - **Hero & Banner**: With native WordPress Image Upload support.
  - **Comparison Tables**: A visual checkbox-driven builder for service tiers and feature lists.
  - **Process Timelines**: Automated rendering of step-by-step methodologies.
  - **Nested Repeaters**: Manage complex lists like "Phases", "Stats", "FAQs", and "Testimonials" effortlessly.

### 3. Menu Navigator (Mega Menu)
- **Visual Nav Builder**: Manage the high-fidelity Mega Menu dropdowns directly from the admin.
- **Detailed Items**: Support for icons, primary titles, and descriptive subtitles for every menu link.
- **Column Management**: Group links into logical columns (e.g., "Market Trends", "Buyer Guides").

### 4. Command Center
- **Tabular Overviews**: Dedicated management views for all custom post types.
- **SQL Reporting**: Integrated tool for running custom SQL queries and visualizing results directly in the dashboard.

## 📁 Directory Structure

```
/ah_theme_scratch/
├── /function-helpers/
│   ├── cpt.php             # Custom Post Type & Taxonomy registrations
│   ├── meta-boxes.php      # The Infinite Visual Builder logic & UI
│   ├── helpers.php         # Frontend rendering logic for JSON components
│   ├── theme-settings.php  # Admin Dashboard & Menu Navigator logic
│   └── ajax-handlers.php   # Backend processing for reports and portals
├── /database/
│   └── seeder.php          # Sample data & "Feature Showcase" pre-fills
└── functions.php           # Main theme bootstrap
```

## 🛠️ Technology Stack
- **Backend**: WordPress (PHP 8+).
- **Architecture**: Modular "Function Helper" pattern to keep `functions.php` clean and scalable.
- **Data Storage**: Native WP Metadata + JSON Objects for complex page structures.
- **Admin UI**: Vanilla CSS + jQuery for real-time form-to-JSON synchronization.

## 📖 How to Use
1. **Initialize Data**: Run the `ah_theme_seed_proper_data` function (via the Seeder) to populate the portal with sample articles and the "Feature Showcase".
2. **Build a Page**: Go to any Article or Project, expand the **"🧩 Visual Page Builder"** section, and fill out the forms. Watch the JSON sync in real-time at the bottom.
3. **Manage Navigation**: Use the **"Menu Navigator"** to build out the high-fidelity dropdowns seen on the frontend.

---
*Developed by Antigravity for Advaith Homes.*
