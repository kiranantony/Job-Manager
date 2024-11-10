<?php

// Register custom REST API routes
add_action('rest_api_init', 'job_manager_register_rest_routes');

function job_manager_register_rest_routes() {
    register_rest_route('job_manager/v1', '/jobs', [
        'methods' => 'GET',
        'callback' => 'job_manager_get_jobs',
    ]);

    register_rest_route('job_manager/v1', '/jobs/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'job_manager_get_job_details',
        'args' => [
            'id' => [
                'required' => true,
                'validate_callback' => 'job_manager_is_numeric'
            ]
        ]
    ]);

    register_rest_route('job_manager/v1', '/jobs/(?P<id>\d+)/apply', [
        'methods' => 'POST',
        'callback' => 'job_manager_apply_to_job',
        'args' => [
            'id' => [
                'required' => true,
                'validate_callback' => 'job_manager_is_numeric'
            ],
            'applicant_name' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'applicant_email' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_email'
            ],
            'message' => [
                'required' => false,
                'sanitize_callback' => 'sanitize_textarea_field'
            ]
        ],
        'permission_callback' => '__return_true' // Allow public access
    ]);
}

// Custom validation callback for numeric check
function job_manager_is_numeric($value, $request, $param) {
    return is_numeric($value);
}
// 1. Get list of jobs
function job_manager_get_jobs($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';

    $jobs = $wpdb->get_results("SELECT id, title, location, is_featured FROM $table_name");

    if (empty($jobs)) {
        return new WP_Error('no_jobs', 'No jobs found', ['status' => 404]);
    }

    // Format the data
    $response = [];
    foreach ($jobs as $job) {
        $response[] = [
            'id' => $job->id,
            'title' => $job->title,
            'location' => $job->location,
            'is_featured' => (bool)$job->is_featured,
        ];
    }

    return rest_ensure_response($response);
}

// 2. Get details of a specific job
function job_manager_get_job_details($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    $job_id = $request['id'];

    $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $job_id), ARRAY_A);

    if (empty($job)) {
        return new WP_Error('no_job', 'Job not found', ['status' => 404]);
    }

    // Format the data for response
    $response = [
        'id' => $job['id'],
        'title' => $job['title'],
        'description' => $job['job_description'],
        'location' => $job['location'],
        'is_featured' => (bool)$job['is_featured'],
        'expires' => $job['expiry_date']
    ];

    return rest_ensure_response($response);
}

// 3. Apply to a job
function job_manager_apply_to_job($request) {
    global $wpdb;
    $job_id = $request['id'];
    $applicant_name = $request['applicant_name'];
    $applicant_email = $request['applicant_email'];
    $message = $request['message'];
    $resume_url = $request['resume_url']; // Optional resume URL if provided

    $table_name = $wpdb->prefix . 'jobs';
    $applications_table = $wpdb->prefix . 'job_applications';

    // Check if job exists
    $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $job_id), ARRAY_A);

    if (empty($job)) {
        return new WP_Error('no_job', 'Job not found', ['status' => 404]);
    }

    // Insert application into the job applications table
    $wpdb->insert($applications_table, [
        'job_id' => $job_id,
        'applicant_name' => sanitize_text_field($applicant_name),
        'applicant_email' => sanitize_email($applicant_email),
        'message' => sanitize_textarea_field($message),
        'resume_url' => esc_url_raw($resume_url), // Optional resume URL
        'applied_at' => current_time('mysql')
    ]);

    // Response
    $response = [
        'success' => true,
        'message' => 'Application submitted successfully'
    ];

    return rest_ensure_response($response);
}
