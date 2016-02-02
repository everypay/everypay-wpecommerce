var $EVERYPAY_FORM, $SUBMIT_BUTTON;

jQuery(document).ready(function ($) {
    $EVERYPAY_FORM = $('.wpsc_checkout_forms').first();
    if (!$EVERYPAY_FORM.length) {
        return;
    }
    $SUBMIT_BUTTON = $EVERYPAY_FORM.find('.wpsc_buy_button');
    var original_value = $SUBMIT_BUTTON.val();
    $EVERYPAY_FORM.append('<div class="button-holder" style="display:none !important"></div>');

    $EVERYPAY_FORM.bind('submit', function (e) {
        var $gateway = $('input[name=custom_gateway]:checked', $EVERYPAY_FORM).val();
        if ($gateway != 'Everypay' || $('[name=everypayToken]', $EVERYPAY_FORM).length) {
            $EVERYPAY_FORM.unbind('submit');
            $EVERYPAY_FORM.submit();
            return;
        }

        if ($('#agree').length && !$('#agree').is(':checked')) {
            var $blink = $('#agree').parent();
            $blink.fadeOut('fast', function () {
                $blink.fadeIn('fast', function () {
                    $blink.fadeOut('fast', function () {
                        $blink.fadeIn('fast');
                    });
                });
            });
            e.preventDefault();
            return;
        }
        
        e.preventDefault();

        //send ajax to get the init data back
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: wpsc_ajax.ajaxurl,
            data: {wpsc_action: 'everypay_get_button'},
            beforeSend: function () {
                $SUBMIT_BUTTON.attr('disabled', 'disabled').addClass('disabled').css('opacity', '0.8');
                $SUBMIT_BUTTON.val(TXT_WPEC_PLEASE_WAIT + ' ...');
            },
            complete: function () {
                reEnable();
            },
            success: function (response) {
                everypay_init(response);
            },
            error: function (result) {
                reEnable();
                alert(TXT_WPEC_OOPS);
            }
        });
    })

    var reEnable = function () {
        setTimeout(function () {
            $SUBMIT_BUTTON.removeAttr('disabled')
                    .removeClass('disabled')
                    .css('opacity', '1')
                    .val(original_value);
        }, 1500);
    }

    var everypay_init = function (response) {
        var loadButton = setInterval(function () {
            try {
                EverypayButton.jsonInit(response, $EVERYPAY_FORM);
                var triggerButton = setInterval(function () {
                    try {
                        $('.everypay-button').first().trigger('click');
                        clearInterval(triggerButton);
                    } catch (err) {
                        //console.log(err);
                    }
                }, 301);
                clearInterval(loadButton);
            } catch (err) {
                //console.log(err);
            }
        }, 301);
    };
});

function handleEverypayToken(message) {
    $EVERYPAY_FORM.append('<input type="hidden" value="' + message.token + '" name="everypayToken">');
    $SUBMIT_BUTTON.attr('disabled', 'disabled').addClass('disabled').css('opacity', '0.8');
    $SUBMIT_BUTTON.val(TXT_WPEC_PLEASE_WAIT + ' ...');
    $EVERYPAY_FORM.submit();
}


