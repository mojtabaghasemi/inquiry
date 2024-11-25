jQuery(document).ready(function ($) {
    $(document).on('click', '.view-log', function () {
        const logId = $(this).data('log-id');
        const logData = $(`#log-result-${logId}`).text();

        try {
            const formattedData = JSON.stringify(JSON.parse(logData), null, 2);
            $('#modal-body').text(formattedData);
        } catch (e) {
            $('#modal-body').text('خطا: داده JSON معتبر نیست.');
        }

        $('#logModal, #modalOverlay').fadeIn();
    });

    $(document).on('click', '.close-modal, #modalOverlay', function () {
        $('#logModal, #modalOverlay').fadeOut();
    });
});
