import ajax from '../http/ajax'

// list all my requirements
exports.mylist = function (page, size, callback) {
    ajax.get('/api/application/mylist', {
        data: {
            page,
            size
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};
