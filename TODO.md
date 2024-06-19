Planned changes
- [ ] Merge answer and ask UI's. Use a small accordion to display answers within a question if requested. 
- [ ] Make UI more compact. Remove redundant space between elements.
- [ ] Add possibility to setup OpenAI keys for a specific account. Encrypt it with a password on save and decrypt it to session on login, so if th DB is stolen nobody can use the keys.
- [ ] Simpplify the UI you don't need that much control over it from backend. KISS 
- [ ] Intorduce some Gamification process with experience bar, sound and stats (ex: learning with increasing amount of hours would give you agility, being consistent would give you vitality).
    - [ ] Timesessions and metrics like question per session, question per minute, answers per minute and answers per session.
- [ ] Interviews based on ChatGPT-4o checks and annotations.
- [ ] find a way to integrate notifications (maybe through htmx pooling?) 

### Before 12.06.2024
- [x] add possibility to analyze symfony bundle configurations (yaml keys)
- [x] add possibility to add questions
- [x] add possibility to analyze packages
- [ ] Answers
  - [x] list answers from API
  - [x] extract answers to a templated-list
  - [ ] implement answers pagination by extending templated-list with paginated-list
  - [ ] implement answers search
- [x] split main quiz controller into subcomponents
  - [x] quiz-list
  - [x] quiz-form
- [ ] Add an `overlay` component to handle loading.
- [x] Quiz delete 
- [x] Quizzes for typescript 

- [ ] Refactor
  - [x] move to turbo
    - [x] list of quizzes
    - [x] list of answers
    - [x] lazy load answers
    - [x] answer form
    - [x] add possibility to create questions
- [ ] turbo improvements
  - [x] add possibility to toggle answer correctness
  - [ ] order questions by (id, desc)
  - [ ] answer search
- [ ] introduce @turboFrame block that renders content manually through render call if it is not turbo context
- [ ] Replace alerts with toasts to not shake the page with alerts.

- [ ] add a quiz for graphQL
- [ ] Add possibility to ask questions from openAI by clicking hint button
- [ ] add a question timer and add a pomodoro functionality to have more focus.
- [ ] Add a quiz for SPL classes.


- [ ] Add possibility to self-register
