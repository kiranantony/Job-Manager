<?php
/*
Plugin Name: Job Manager Plugin
Description: A plugin to manage jobs and applications with REST API support.
Version: 1.0
Author: Kiran Antony
*/

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/job-manager-activator.php';

// Register activation hook for creating tables
register_activation_hook(__FILE__, 'job_manager_create_tables');
