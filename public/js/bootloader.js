const app = new Application();

keyboardJS.bind('enter', app.onSubmit);
keyboardJS.bind('num4', app.onPrevious);
keyboardJS.bind('num6', app.onNext);
keyboardJS.bind('num0', app.onNewQuestion);
