<?php
// Register Meta Boxes
add_action('admin_init', 'job_manager_register_meta_boxes');
function job_manager_register_meta_boxes() {
    add_meta_box('job_details', 'Job Details', 'job_manager_job_details_meta_box', 'job_manager', 'normal', 'high');
    add_meta_box('company_info', 'Company Information', 'job_manager_company_info_meta_box', 'job_manager', 'normal', 'high');
    add_meta_box('location_meta', 'Location', 'job_manager_location_meta_box', 'job_manager', 'normal', 'high');
    add_meta_box('publish_meta', 'Publish Settings', 'job_manager_publish_meta_box', 'job_manager', 'side', 'high');
}


// Callback functions for each meta box
function job_manager_job_details_meta_box($post) {
    global $wpdb;
    $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $job = $job_id ? job_manager_get_job($job_id) : null;

    $title = $job ? $job->title : '';
    $description = $job ? $job->job_description : '';
    $job_type = $job ? $job->job_type : '';
    $category = $job ? $job->category : '';

    ?>
    <p><label for="job_title">Title</label>
        <input type="text" name="job_title" id="job_title" value="<?php echo esc_attr($title); ?>" class="widefat"></p>
    <p><label for="job_description">Description</label>
        <textarea name="job_description" id="job_description" class="widefat"><?php echo esc_textarea($description); ?></textarea></p>
    <p><label for="job_type">Job Type</label>
        <input type="text" name="job_type" id="job_type" value="<?php echo esc_attr($job_type); ?>" class="widefat"></p>
    <p><label for="job_category">Category</label>
        <input type="text" name="job_category" id="job_category" value="<?php echo esc_attr($category); ?>" class="widefat"></p>
    <?php
}

function job_manager_company_info_meta_box($post) {
    global $wpdb;
    $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $job = $job_id ? job_manager_get_job($job_id) : null;

    // Get the company name and logo media ID
    $company_name = $job ? $job->company_name : '';
    $company_logo_id = $job ? $job->company_logo : '';

    // Get the image URL based on the media ID (if available)
    $company_logo_url = $company_logo_id ? wp_get_attachment_image_url($company_logo_id, 'thumbnail') : '';
    ?>
    <p><label for="company_name">Company Name</label>
        <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr($company_name); ?>" class="widefat"></p>

    <p><label>Company Logo</label></p>
    
    <div id="company-logo-container">
        <?php if ($company_logo_url): ?>
            <img id="company-logo-preview" src="<?php echo esc_url($company_logo_url); ?>" style="max-width: 150px; display: block; margin-bottom: 10px;">
        <?php else: ?>
            <img id="company-logo-preview" src="" style="max-width: 150px; display: none; margin-bottom: 10px;">
        <?php endif; ?>
        
        <input type="hidden" name="company_logo_id" id="company_logo_id" value="<?php echo esc_attr($company_logo_id); ?>">
        
        <button type="button" class="button" id="upload-company-logo"><?php echo $company_logo_url ? 'Change Logo' : 'Upload Logo'; ?></button>
        <button type="button" class="button" id="remove-company-logo" style="<?php echo $company_logo_url ? '' : 'display: none;'; ?>">Remove Logo</button>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Ensure wp.media is available
        if (typeof wp.media !== 'undefined') {
            var mediaUploader;

            $('#upload-company-logo').click(function(e) {
                e.preventDefault();

                // If the uploader object has already been created, reopen the dialog
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                // Create the media uploader
                mediaUploader = wp.media({
                    title: 'Select or Upload Company Logo',
                    button: { text: 'Use this logo' },
                    multiple: false
                });

                // When a file is selected, update the preview and hidden field
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#company_logo_id').val(attachment.id);
                    $('#company-logo-preview').attr('src', attachment.url).show();
                    $('#upload-company-logo').text('Change Logo');
                    $('#remove-company-logo').show();
                });

                // Open the uploader dialog
                mediaUploader.open();
            });

            // Remove the selected logo
            $('#remove-company-logo').click(function(e) {
                e.preventDefault();
                $('#company_logo_id').val('');
                $('#company-logo-preview').hide();
                $(this).hide();
                $('#upload-company-logo').text('Upload Logo');
            });
        } else {
            console.error('Media uploader is not available.');
        }
    });
    </script>
    <?php
}

function job_manager_location_meta_box($post) {
    global $wpdb;
    $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $job = $job_id ? job_manager_get_job($job_id) : null;

    $location = $job ? $job->location : '';
    ?>
    <p><label for="job_location">Location</label>
        <input type="text" name="job_location" id="job_location" value="<?php echo esc_attr($location); ?>" class="widefat"></p>
    <?php
}

function job_manager_publish_meta_box($post) {
    global $wpdb;
    $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $job = $job_id ? job_manager_get_job($job_id) : null;

    // Get and format expiry date; default to tomorrow if empty
    $expires = $job && !empty($job->expiry_date) 
        ? date('Y-m-d H:i:s', strtotime($job->expiry_date)) 
        : date('Y-m-d H:i:s', strtotime('+1 day'));

    $is_featured = $job ? $job->is_featured : 0;

    // Split the date and time parts
    $date_part = date('Y-m-d', strtotime($expires));
    $time_part = date('H:i', strtotime($expires));
    ?>
    
    <p><label for="job_expires_date">Expires On</label></p>
    <input type="text" name="job_expires_date" id="job_expires_date" value="<?php echo esc_attr($date_part); ?>" class="widefat" placeholder="YYYY-MM-DD">
    
    <p><label for="job_expires_time">Time</label></p>
    <input type="text" name="job_expires_time" id="job_expires_time" value="<?php echo esc_attr($time_part); ?>" class="widefat" placeholder="HH:MM">

    <p><label for="job_is_featured">
        <input type="checkbox" name="job_is_featured" id="job_is_featured" <?php checked($is_featured, 1); ?>> Featured Job
    </label></p>
    
    <?php
    submit_button($post->ID ? 'Update Job' : 'Add Job');
}


