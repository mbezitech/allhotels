jQuery(document).ready(function ($) {

    // Handle the booking form submission
    $('.lhb-booking-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $form.find('.lhb-form-message');

        $btn.prop('disabled', true).text('Processing...');
        $msg.hide().removeClass('success error');

        var formData = $form.serializeArray();

        // Prepare data to send to admin-ajax.php
        var ajaxData = {
            action: 'lhb_submit_booking',
            security: lhb_ajax.nonce
        };

        // Append form data to ajaxData
        $.each(formData, function (i, field) {
            ajaxData[field.name] = field.value;
        });

        $.ajax({
            url: lhb_ajax.ajax_url,
            method: 'POST',
            data: ajaxData,
            success: function (response) {
                if (response.success) {
                    $msg.addClass('success').text(response.data).show();
                    $form[0].reset();
                } else {
                    $msg.addClass('error').text(response.data || 'An error occurred during booking.').show();
                }
            },
            error: function (xhr, status, error) {
                $msg.addClass('error').text('Connection error. Please try again.').show();
            },
            complete: function () {
                $btn.prop('disabled', false).text('Complete Booking');
            }
        });
    });

});
