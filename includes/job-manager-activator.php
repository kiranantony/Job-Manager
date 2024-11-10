<?php

function job_manager_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Jobs table
    $jobs_table = $wpdb->prefix . 'jobs';
    $sql_jobs = "CREATE TABLE $jobs_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        location varchar(100) NOT NULL,
        salary varchar(50),
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Applications table
    $applications_table = $wpdb->prefix . 'job_applications';
    $sql_applications = "CREATE TABLE $applications_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        job_id mediumint(9) NOT NULL,
        applicant_name varchar(255) NOT NULL,
        applicant_email varchar(100) NOT NULL,
        resume_url varchar(255),
        applied_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (job_id) REFERENCES $jobs_table(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_jobs);
    dbDelta($sql_applications);
}
