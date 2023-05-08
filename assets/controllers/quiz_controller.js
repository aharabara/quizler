// assets/controllers/quiz_controller.js
import {Controller} from 'stimulus';
import axios from 'axios';

export default class QuizController extends Controller {
    static targets = [
        'quizList',
        'quizContent',
        'questionContainer',
        'hint',
        'answers',
        'submitBtn',
        'navigation',
        'answer'
    ];

    connect() {
        this.fetchQuizzes();
    }

    async fetchQuizzes() {
        const response = await axios.get('/quizzes');
        this.quizListTarget.innerHTML = Object.entries(response.data)
            .map(([id, name]) => {
                const amountOfAnswers = 1;
                return `<li class="list-group-item d-flex justify-content-between align-items-start" 
                            data-action="
                                click->quiz#loadQuiz
                                mouseover->quiz#activateElement
                                mouseout->quiz#deactivateElement
                            "
                            data-id="${id}">
                    ${name}
                    <span class="badge bg-primary rounded-pill">${amountOfAnswers}</span>
                </li>`
            })
            .join('');

        this.loadLastCheckpoint();
    }

    activateElement(e) {
        e.target.classList.add('list-group-item-primary')
    }

    deactivateElement(e) {
        e.target.classList.remove('list-group-item-primary')
    }

    async loadQuiz(event) {
        this.quizListTarget
            .querySelectorAll('li')
            .forEach((el) => el.classList.remove('active'));

        event.target.classList.add('active')

        const quizId = event.target.dataset.id;

        await this._loadQuizById(quizId);
        this.checkpoint();
    }

    async _loadQuizById(quizId) {
        this.currentQuiz = (await axios.get(`/quiz?qid=${quizId}`)).data;
        this.resetNavigation();
        this.showCurrentQuestion();
    }

    resetNavigation() {
        this.currentPosition = 0;
        this.answeredQuestions = {};
    }

    showCurrentQuestion() {
        const question = this.currentQuiz.questions[this.currentPosition];
        this.checkpoint();

        if (!question) {
            return;
        }

        this.questionContainerTarget.innerHTML =
            `<form data-action="submit->quiz#submitAnswer">
                <p data-quiz-target="hint" class="mb-4 alert alert-info" hidden></p>
                <h5>${question.question}</h5>
                <textarea data-quiz-target="answer" name="userAnswer" class="form-control mb-3" placeholder="Your answer"></textarea>
                <div class="row">
                    <div class="col-4">
                        <button type="submit" 
                             data-quiz-target="submitBtn"
                             class="btn btn-success">Submit</button>
                        <button data-action="click->quiz#showHint" class="btn btn-info">Show Hint</button>
                    </div>
                     <div class="navitation col-8" data-quiz-target="navigation">
                        <div class="float-end">
                             <button type="button" class="btn btn-primary"
                                 data-action="click->quiz#previousQuestion:prevent">
                                    <i class="ri-arrow-left-s-line"></i>
                                </button>
                            <button type="button" class="btn btn-primary"
                                 data-action="click->quiz#nextQuestion:prevent">
                                 <i class="ri-arrow-right-s-line"></i>
                            </button>
                        </div>
                     </div>
                </div>
             </form>` +
            `<div data-quiz-target="answers"></div>`;
        this.quizContentTarget.hidden = false;
        this.hintTarget.hidden = true;
    }

    checkpoint() {
        localStorage.setItem(`quizzler`, JSON.stringify({
            quizId: this.currentQuiz.id,
            questionPosition: this.currentPosition
        }))
    }

    async loadLastCheckpoint() {
        let json = localStorage.getItem(`quizzler`);
        const data = JSON.parse(json);
        if (data && data.quizId) {
            await this._loadQuizById(data.quizId);
            this.currentPosition = data.questionPosition;
            this.showCurrentQuestion();
        }
    }

    async submitAnswer(event) {
        event.preventDefault();

        const userAnswer = event.target.userAnswer.value;
        const question = this.currentQuiz.questions[this.currentPosition];

        console.log(question);
        if (!userAnswer) {
            return;
        }
        this.answerTarget.disabled = true;
        this.submitBtnTarget.disabled = true;

        await axios.post('/answer-to?question_id=' + question.id, {content: userAnswer})
            .then(response => {
                question.answers.push(response.data);
                this.renderAnswersFor(question);
            });

    }

    nextQuestion() {
        this.currentPosition++;
        this.showCurrentQuestion();
    }
    previousQuestion() {
        this.currentPosition--;
        this.showCurrentQuestion();

        this.renderAnswersFor(this.getQuestion());
        this.answerTarget.disabled = true;
        this.submitBtnTarget.disabled = true;
        this.renderAnswersFor(this.getQuestion());
    }

    getQuestion() {
        return this.currentQuiz.questions[this.currentPosition];
    }

    renderAnswersFor(question) {
        const answersHtml = question.answers
            .map((answer) => `<li>${answer.content}${answer.isCorrect ? ' (correct)' : ''}</li>`)
            .join('');

        this.answersTarget.innerHTML = `<h5>Answers:</h5><ul>${answersHtml}</ul>`;
    }

    showHint() {
        this.hintTarget.innerHTML = 'A hint here.'
        this.hintTarget.hidden = false;
    }
}