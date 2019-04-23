module.exports = {
  pad2: function (n) {
    return n < 10 ? '0' + n : n
  },

  currentDate: function () {
    let date = new Date()
    return '[' + date.getFullYear().toString() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate())
      + ' ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes()) + ':' + pad2(date.getSeconds()) + ']'
  },

  logToTask: function (task, logLine) {
    task.description += currentDate() + ' ' + logLine + '<br>'
  }
}