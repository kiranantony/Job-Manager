jQuery(document).ready(function ($) {
    $('#job_expires_date').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    $('#job_expires_time').timepicker({
        timeFormat: 'HH:mm',
        stepMinute: 5
    });
});
