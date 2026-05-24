<?php
defined( 'ABSPATH' ) || exit;

handle_defined( 'CLIENT_PRIMARY_TITLE', 'Advaith' );
handle_defined( 'CLIENT_SECONDARY_TITLE', 'Homes' );
handle_defined( 'CLIENT_FULL_TITLE',CLIENT_PRIMARY_TITLE . ' ' . CLIENT_SECONDARY_TITLE);
handle_defined( 'CLIENT_SHORT_TITLE', 'AH' );
handle_defined(	'CLIENT_ENQUIRY_SUBJECT_PREFIX','[' . CLIENT_FULL_TITLE . ' Enquiry]');

// Centralized Terminology Constants — everything is a Post
handle_defined( 'AH_TERM_SINGULAR',       'Post' );
handle_defined( 'AH_TERM_PLURAL',         'Posts' );
handle_defined( 'AH_TERM_LOWER',          'post' );
handle_defined( 'AH_TERM_LOWER_PLURAL',   'posts' );

// Primary Contact Info Constants
handle_defined( 'CLIENT_PHONE', '+44 7747 223762' );
handle_defined( 'CLIENT_EMAIL', 'contact@advaithhomes.co.uk' );
handle_defined( 'CLIENT_ADDRESS', 'London & Nationwide' );

// Email Routing Constants
handle_defined( 'EMAIL_GENERAL', 'general@advaithhomes.com' );
handle_defined( 'EMAIL_COMPLAINT', 'complaint@advaithhomes.com' );
handle_defined( 'EMAIL_SALES', 'sales@advaithhomes.com' );
handle_defined( 'EMAIL_SUPPORT', 'support@advaithhomes.com' );
handle_defined( 'EMAIL_MEDIA', 'media@advaithhomes.com' );
handle_defined( 'EMAIL_OTHER', 'contact@advaithhomes.com' );