jQuery(document).ready(function ($) {
    const loadingOverlay = $('<div id="loading-overlay">در حال استعلام...</div>').hide();
    $('body').append(loadingOverlay);

    $('#inquiry-check-button').on('click', function () {
        const data = {
            action: 'inquiry_check',
        };

        $('#inquiry-check-button').prop('disabled', true).text('در حال استعلام...');
        $('#inquiry-result').empty();
        loadingOverlay.show();

        $.post(inquiry_ajax.ajax_url, data, function (response) {
            loadingOverlay.hide();
            $('#inquiry-check-button').prop('disabled', false).text('استعلام');

            if (response.success) {
                const message = response.data.message || 'استعلام موفق';
                const allocation = response.data.data?.Allocation ?? 'نامشخص';

                const resultMessage = `
                    <div class="success-message">
                        <p>${message}</p>
                        <p>مقدار سهمیه: ${allocation}</p>
                    </div>
                `;
                $('#inquiry-result').html(resultMessage);
                console.log('نتیجه استعلام (موفق):', response);

                // فعال کردن دکمه پرداخت
                $('#place_order').prop('disabled', false).css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
            } else {
                const errorMessage = `
                    <div class="error-message">
                        <p>${response.data?.message || 'خطا در استعلام'}</p>
                    </div>
                `;
                $('#inquiry-result').html(errorMessage);
                console.error('نتیجه استعلام (خطا):', response);

                // غیرفعال کردن دکمه پرداخت
                $('#place_order').prop('disabled', true).css({
                    'opacity': '0.5',
                    'pointer-events': 'none'
                });
            }
        }).fail(function () {
            loadingOverlay.hide();
            $('#inquiry-check-button').prop('disabled', false).text('استعلام');
            const networkError = `
                <div class="error-message">
                    <p>خطای شبکه! لطفاً دوباره تلاش کنید.</p>
                </div>
            `;
            $('#inquiry-result').html(networkError);
            console.error('خطای شبکه: درخواست AJAX انجام نشد.');
        });
    });
});
