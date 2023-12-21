const Helper = require('@codeceptjs/helper');

class Auth extends Helper {

  // before/after hooks
  /**
   * @protected
   */
  _before() {
    // remove if not used
  }

  /**
   * @protected
   */
  _after() {
    // remove if not used
  }

  // add custom methods here
  // If you need to access other helpers
  // use: this.helpers['helperName']

  logout(/** @type {CodeceptJS.I} */ I){
    I.click('[href="/logout"]')
  }

}

module.exports = Auth;
