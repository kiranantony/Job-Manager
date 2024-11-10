<?php
// Create job table if not exists
function job_manager_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        location VARCHAR(255) NOT NULL,
        salary VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Get all jobs
function job_manager_get_all_jobs($search = '') {
    global $wpdb;
    $query = "SELECT * FROM {$wpdb->prefix}jobs";

    if (!empty($search)) {
        $query .= $wpdb->prepare(" Where (title LIKE %s OR company_name LIKE %s)", '%' . $wpdb->esc_like($search) . '%', '%' . $wpdb->esc_like($search) . '%');
    }

    return $wpdb->get_results($query);
}

function job_manager_count_applications($job_id) {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}job_applications WHERE job_id = %d",
        $job_id
    ));
}

// Create a new job
function job_manager_create_job($title, $description, $location, $salary) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    
    $wpdb->insert(
        $table_name,
        [
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'salary' => $salary
        ]
    );
}

// Get a specific job by ID
function job_manager_get_job($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    
    return $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id");
}

// Update a specific job
function job_manager_update_job($id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    
    $wpdb->update(
        $table_name,
        $data,
        ['id' => $id]
    );
}

// Delete a job by ID
function job_manager_delete_job($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    
    $wpdb->delete($table_name, ['id' => $id]);
}
