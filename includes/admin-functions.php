<?php
// Admin Menu and Pages
add_action('admin_menu', 'job_manager_add_admin_menu');

function job_manager_add_admin_menu() {
    add_menu_page(
        'Job Manager',
        'Job Manager',
        'manage_options',
        'job_manager',
        'job_manager_jobs_page',
        'dashicons-businessman',
        6
    );

    add_submenu_page(
        'job_manager',
        'Add New Job',
        'Add New Job',
        'manage_options',
        'job_manager_add_job',
        'job_manager_add_job_page'
    );

    add_submenu_page(
        null,
        'Edit Job',
        'Edit Job',
        'manage_options',
        'job_manager_edit_job',
        'job_manager_edit_job_page'
    );
}

// Enqueue the media uploader script on specific admin pages
add_action('admin_enqueue_scripts', 'job_manager_enqueue_media_uploader');
function job_manager_enqueue_media_uploader() {
    // Check if we are on the Job Manager "Add New Job" or "Edit Job" page
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';
    if ($current_page === 'job_manager_add_job' || $current_page === 'job_manager_edit_job') {
        wp_enqueue_media(); // Enqueue WordPress media uploader scripts
    }
}

add_action('admin_enqueue_scripts', 'job_manager_enqueue_date_time_picker');
function job_manager_enqueue_date_time_picker($hook_suffix) {
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';

    if ($current_page === 'job_manager_add_job' || $current_page === 'job_manager_edit_job') {
        // Enqueue WordPress DatePicker
        wp_enqueue_script('jquery-ui-datepicker');
        
        // Enqueue the Timepicker Addon
        wp_enqueue_script('jquery-timepicker-addon', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js', ['jquery', 'jquery-ui-datepicker'], null, true);
        
        // Enqueue Styles for DatePicker and Timepicker
        wp_enqueue_style('jquery-ui-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css');
        wp_enqueue_style('jquery-timepicker-addon-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css');

        // Custom script to initialize DatePicker and Timepicker
        wp_enqueue_script('job_manager_datetime_picker', plugins_url('js/job-manager-datetime-picker.js', __FILE__), ['jquery', 'jquery-ui-datepicker', 'jquery-timepicker-addon'], null, true);
    }
}



// Job Listings Page
function job_manager_jobs_page() {
    global $wpdb;
    
    // Handle deletion of job if 'delete' action is triggered
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        job_manager_delete_job(intval($_GET['id']));
        echo '<div class="notice notice-success is-dismissible"><p>Job deleted successfully.</p></div>';
    }

    // Get search term if it exists
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    
    // Fetch jobs based on search query
    $jobs = job_manager_get_all_jobs($search_query);

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Job Listings</h1>
        <a href="<?php echo admin_url('admin.php?page=job_manager_add_job'); ?>" class="page-title-action">Add New Job</a>

        <!-- Search form -->
        <form method="get" style="float: right; margin-bottom: 10px;">
            <input type="hidden" name="page" value="job_manager">
            <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Search Jobs">
            <button type="submit" class="button">Search Jobs</button>
        </form>

        <hr class="wp-header-end">

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Company</th>
                    <th>Job Type</th>
                    <th>Category</th>
                    <th>Expires</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jobs): ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><?php echo esc_html($job->title); ?></td>
                            <td><?php echo esc_html($job->company_name); ?></td>
                            <td><?php echo esc_html($job->job_type); ?></td>
                            <td><?php echo esc_html($job->category); ?></td>
                            <td><?php echo esc_html($job->expiry_date); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=job_manager_edit_job&id=' . $job->id); ?>">Edit</a> |
                                <a href="<?php echo admin_url('admin.php?page=job_manager&action=delete&id=' . $job->id); ?>" onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No jobs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Render the Add/Edit Job Page
function job_manager_add_job_page() {
    $post = (object) ['ID' => 0]; // Mock post object for compatibility
    job_manager_render_meta_boxes($post);
}

function job_manager_edit_job_page() {
    $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $post = (object) ['ID' => $job_id];
    job_manager_render_meta_boxes($post);
}

function job_manager_render_meta_boxes($post) {
    ?>
    <div class="wrap">
        <h1><?php echo $post->ID ? 'Edit Job' : 'Add New Job'; ?></h1>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="save_job">
            <?php if ($post->ID) : ?>
                <input type="hidden" name="job_id" value="<?php echo $post->ID; ?>">
            <?php endif; ?>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php do_meta_boxes('job_manager', 'normal', $post); ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <?php do_meta_boxes('job_manager', 'side', $post); ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

// Save the job data when form is submitted
add_action('admin_post_save_job', 'job_manager_save_job');
function job_manager_save_job() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    $expires_date = sanitize_text_field($_POST['job_expires_date']);
    $expires_time = sanitize_text_field($_POST['job_expires_time']);
    $expires = date('Y-m-d H:i:s', strtotime("$expires_date $expires_time"));

    $data = [
        'title' => sanitize_text_field($_POST['job_title']),
        'job_description' => sanitize_textarea_field($_POST['job_description']),
        'job_type' => sanitize_text_field($_POST['job_type']),
        'category' => sanitize_text_field($_POST['job_category']),
        'company_name' => sanitize_text_field($_POST['company_name']),
        'company_logo' => intval($_POST['company_logo_id']),
        'location' => sanitize_text_field($_POST['job_location']),
        'expiry_date' => $expires, // Save combined datetime
        'is_featured' => isset($_POST['job_is_featured']) ? 1 : 0
    ];

    $table_name = $wpdb->prefix . 'jobs';
    if (!empty($_POST['job_id'])) {
        $wpdb->update($table_name, $data, ['id' => intval($_POST['job_id'])]);
    } else {
        $wpdb->insert($table_name, $data);
    }

    wp_redirect(admin_url('admin.php?page=job_manager'));
    exit;
}
