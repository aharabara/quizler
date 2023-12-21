Feature('login');

Scenario('[Auth] Login',  ({ I , loginAs}) => {
  loginAs('user')

  I.waitForVisible('[href="/logout"]');
  I.logout(I)
});
