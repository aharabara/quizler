/** @type {CodeceptJS.MainConfig} */
exports.config = {
  tests: './tests/*_test.js',
  output: './tests/output',
  helpers: {
    Playwright: {
      browser: 'chromium',
      url: 'http://localhost',
      show: true,
      ignoreHTTPSErrors: true,
      manualStart: false
    },
    Auth: {
      require: './tests/helpers/auth_helper.js',
    }
  },
  plugins: {
    autoLogin: {
      enabled: true,
      saveToFile: true,
      inject: 'loginAs', // use `loginAs` instead of login
      users: {
        user: {
          login: (I) => {
            I.amOnPage('/');
            I.waitForVisible('#form_username', 3);
            I.fillField('#form_username', 'aharabara')
            I.fillField('#form_password', 'Test1234!')
            I.click('#form_submit')
            I.waitForVisible('[href="/logout"]');
          },
          check: (I) => {
            I.see('[href="/logout"]')
          },
          fetch: () => {}, // empty function
          restore: () => {}, // empty funciton
        },
      }
    }
  },
  include: {
    I: './tests/steps_file.js'
  },
  name: 'quizler'
}
