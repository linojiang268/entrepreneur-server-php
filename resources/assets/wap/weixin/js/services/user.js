import ajax from '../http/ajax'

exports.login = function (account, password, openId, callback) {
    ajax.post('/api/login', {
        data: {
            mobile: account,
            password,
            remember: true,
            open_id: openId
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};

exports.register = function (account, password, business, name, callback) {
    ajax.post('/api/register', {
        data: {
            mobile: account,
            password,
            name,
            business
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};

exports.changePassword = function (oldPassword, newPassword, callback) {
    ajax.post('/api/password/change', {
        data: {
            original_password: oldPassword,
            new_password: newPassword
        },
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};

exports.logout = function (callback) {
    ajax.get('/api/logout', {
        success: callback.success,
        fail: callback.fail,
        always: callback.always
    });
};