<?php
// Admin Menu and Pages
add_action('admin_menu', 'job_manager_add_admin_menu', 1);

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
    add_submenu_page(
            'job_manager',
            'Job Applications',
            'Applications',
            'manage_options',
            'job_manager_applications',
            'job_manager_applications_page'
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

function job_manager_applications_page() {
    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    class Job_Applications_List_Table extends WP_List_Table {

        public function __construct() {
            parent::__construct([
                'singular' => 'Application',
                'plural' => 'Applications',
                'ajax' => false,
            ]);
        }

        public function get_columns() {
            return [
                'cb' => '<input type="checkbox" />',
                'applicant_name' => 'Applicant Name',
                'applicant_email' => 'Email',
                'job_title' => 'Job Title',
                'applied_at' => 'Applied On',
                'actions' => 'Actions'
            ];
        }

        protected function column_cb($item) {
            return sprintf('<input type="checkbox" name="application[]" value="%s" />', $item['id']);
        }

        public function column_actions($item) {
            $view_url = admin_url('admin.php?page=job_manager_view_application&id=' . $item['id']);
            $delete_url = admin_url('admin.php?page=job_manager_applications&action=delete&id=' . $item['id']);

            return sprintf(
                    '<a href="%s">View</a> | <a href="%s" onclick="return confirm(\'Are you sure?\');">Delete</a>',
                    esc_url($view_url),
                    esc_url($delete_url)
            );
        }

        public function prepare_items() {
            global $wpdb;
            $per_page = 10;
            $current_page = $this->get_pagenum();
            $table_name = $wpdb->prefix . 'job_applications';
            $jobs_table = $wpdb->prefix . 'jobs';

            $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
            $offset = ($current_page - 1) * $per_page;

            $this->items = $wpdb->get_results($wpdb->prepare(
                            "SELECT a.id, a.applicant_name, a.applicant_email, a.applied_at, j.title AS job_title
             FROM {$table_name} a
             LEFT JOIN {$jobs_table} j ON a.job_id = j.id
             ORDER BY a.applied_at DESC
             LIMIT %d OFFSET %d",
                            $per_page,
                            $offset
                    ), ARRAY_A);
            $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page' => $per_page,
                'total_pages' => ceil($total_items / $per_page),
            ]);
            $this->_column_headers = [
                $this->get_columns(),
                [], // hidden columns
                $this->get_sortable_columns(),
                $this->get_primary_column_name(),
            ];
        }

        public function display_rows() {
            foreach ($this->items as $item) {
                echo '<tr>';

                list($columns, $hidden) = $this->get_column_info();
                foreach ($columns as $column_name => $column_display_name) {
                    $class = "class=\"$column_name column-$column_name\"";
                    $style = in_array($column_name, $hidden) ? ' style="display:none;"' : '';
                    $attributes = "$class$style";

                    // Debug: Log current item and column data
                    error_log("Rendering Row - Column: {$column_name}, Data: " . print_r($item[$column_name] ?? '', true));

                    echo '<td ' . $attributes . '>';
                    echo $this->column_default($item, $column_name);
                    echo '</td>';
                }

                echo '</tr>';
            }
        }

        public function get_sortable_columns() {
            return [
                'applicant_name' => ['applicant_name', false],
                'applied_at' => ['applied_at', false],
            ];
        }

        public function column_default($item, $column_name) {
            switch ($column_name) {
                case 'cb':
                    return $this->column_cb($item);
                case 'applicant_name':
                case 'applicant_email':
                case 'job_title':
                case 'applied_at':
                    return esc_html($item[$column_name]);
                case 'actions':
                    return '<a href="#">View</a> | <a href="#">Delete</a>';
                default:
                    return print_r($item, true); // Debugging
            }
        }
    }

    $applications_table = new Job_Applications_List_Table();
    $applications_table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Job Applications</h1>
        <form method="get">
            <input type="hidden" name="page" value="job_manager_applications">
            <?php $applications_table->search_box('Search Applications', 'application'); ?>
            <?php $applications_table->display(); ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'job_manager_handle_application_deletion');

function job_manager_handle_application_deletion() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_applications';
        $application_id = intval($_GET['id']);

        $wpdb->delete($table_name, ['id' => $application_id]);

        wp_redirect(admin_url('admin.php?page=job_manager_applications'));
        exit;
    }
}
