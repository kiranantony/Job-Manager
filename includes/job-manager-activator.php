<?php

function job_manager_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Jobs table with additional fields: company name, featured, job type, and category
    $jobs_table = $wpdb->prefix . 'jobs';
    $sql_jobs = "CREATE TABLE $jobs_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        job_description longtext NOT NULL,  -- Detailed job description
        location varchar(100) NOT NULL,
        company_name varchar(255),         -- New field for company name
        is_featured boolean DEFAULT 0,      -- New field for featured job status
        job_type varchar(100),             -- New field for job type (e.g., full-time, part-time)
        category varchar(100),             -- New field for job category (e.g., IT, Marketing)
        company_logo varchar(255),         -- Company logo URL (image)
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        expiry_date datetime NOT NULL,     -- Job expiry date
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Applications table with additional fields for message and attachment
    $applications_table = $wpdb->prefix . 'job_applications';
    $sql_applications = "CREATE TABLE $applications_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        job_id mediumint(9) NOT NULL,
        applicant_name varchar(255) NOT NULL,
        applicant_email varchar(100) NOT NULL,
        message text,                       -- Applicant's message
        resume_url varchar(255),            -- URL of the uploaded resume (optional)
        attachment varchar(255),            -- URL of any media attachment
        applied_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (job_id) REFERENCES $jobs_table(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_jobs);  // Create or update jobs table
    dbDelta($sql_applications);  // Create or update job applications table
}

