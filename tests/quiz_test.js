Feature('quiz');

Scenario('[Quiz] Create',  ({ I , loginAs}) => {
  loginAs('user')
  // go to quizzes
  I.amOnPage('/');
  I.click('[href="/app/quiz/list"]');

  // click create quiz
  I.waitForVisible('[href="/app/quiz/create"]');
  I.click('[href="/app/quiz/create"]');
  I.waitForVisible('form[action="/app/quiz/create"]');

  let title = '[Test] quiz ' + (new Date().getTime());
  I.fillField('#quiz_value', title);
  I.fillField('#quiz_source', 'source from somewhere');

  // submit
  I.click('#quiz_submit');

  I.waitForVisible('#list-quiz');
  I.waitForText(title);
});
