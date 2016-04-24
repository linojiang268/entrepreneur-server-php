exports.elapsed = function elapsed(time) {
  let diff = ((new Date()).valueOf() - Date.parse(time).valueOf()) / 1000; // diff in seconds
  if (diff < 60) {
      return '刚刚';
  }

  if (diff < 3600) {
      return Math.floor(diff / 60) + '分钟前';
  }

  if (diff < 86400) {
     return Math.floor(diff / 3600) + '小时前';
  }

  return Math.floor(diff / 86400) + '天前';
};