function attachHandlers(xhr, settings) {
    // success handling
    settings.success &&  xhr.done((data) => {
        settings.success(saneSuccessResponse(data));
    })

    // failure handling
    xhr.fail(function (xhr) {
        if (!settings.fail) {
            // alert the failure if client not handle this
            return function (xhr) {
                alert(translateError(xhr));
            }
        }

        return settings.fail(translateError(xhr), xhr);
    });

    settings.always && xhr.always(settings.always);
}

function translateError(xhr) {
    if (xhr.status == 500) { // convert Server Internal Error
        return '服务器忙,请稍后重试';
    }

    return xhr.statusText;
}

function saneSuccessResponse(resp) {
    // server may not comply with the contract sometimes
    // and in that case, shelter the underlying error
    if (typeof resp.code != 'number') {
        return {
            code: 10000,
            message: '服务器忙,请稍后重试。'
        };
    }

    return resp;
}


// globally setup ajax request header to include the CSRF token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

exports.post = function ajax(url, settings) {
    settings = settings || {};
    attachHandlers($.post(url, settings.data, settings.dataType), settings);
};


exports.get = function ajax(url, settings) {
    settings = settings || {};
    attachHandlers($.get(url, settings.data, settings.dataType), settings);
};