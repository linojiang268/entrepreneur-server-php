import ajax from '../http/ajax'

exports.publish = function (title, description,
                            startDate, endDate,
                            mobile, contact, callback) {

    ajax.post('/api/requirement/create', {
        data: {
            title,
            intro: description,
            begin_time: startDate,
            end_time: endDate,
            mobile,
            contacts: contact
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};

// list all requirements
exports.list = function (page, size, callback) {
    ajax.get('/api/requirement/list', {
        data: {
            page,
            size
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};

exports.apply = function (requirementId, mobile, contact, description, callback) {
    ajax.post('/api/application/create', {
        data: {
            req_id: requirementId,
            contacts: contact,
            mobile,
            intro: description
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};


// list my requirements
exports.mylist = function (page, size, callback) {
    ajax.get('/api/requirement/mylist', {
        data: {
            page,
            size
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};