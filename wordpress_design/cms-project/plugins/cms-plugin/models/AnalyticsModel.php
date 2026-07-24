<?php
defined( 'ABSPATH' ) || exit;

/**
 * @deprecated Use AH_Analytics_Report_Model (AnalyticsReportModel.php)
 *             and AH_Analytics_Result_Model (AnalyticsResultModel.php)
 *
 * This file is kept for backward compatibility only.
 * Both classes have been split into their own files.
 */

if ( ! class_exists( 'AH_Analytics_Report_Model' ) ) {
	require_once __DIR__ . '/AnalyticsReportModel.php';
}

if ( ! class_exists( 'AH_Analytics_Result_Model' ) ) {
	require_once __DIR__ . '/AnalyticsResultModel.php';
}
