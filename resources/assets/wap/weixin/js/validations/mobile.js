module.exports = function isMobile(mobile) {
    return /^1\d{10}$/.test(mobile);
};