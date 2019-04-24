module.exports = {
  pad2: function (n) {
    return n < 10 ? '0' + n : n
  },

  currentDate: function () {
    let date = new Date()
    return '[' + date.getFullYear().toString() + '-' + this.pad2(date.getMonth() + 1) + '-' + this.pad2(date.getDate())
      + ' ' + this.pad2(date.getHours()) + ':' + this.pad2(date.getMinutes()) + ':' + this.pad2(date.getSeconds()) + ']'
  },

  logToTask: function (task, logLine) {
    task.description += this.currentDate() + ' ' + logLine + '<br>'
  }
}