// assets/controllers/quiz_controller.js
import {Controller} from '@hotwired/stimulus';
import axios from 'axios';

export default class QuizController extends Controller {
    static targets = [
        'quizList',
        'quizContent',
        'questionContainer',
        'questionTitle',
        'hint',
        'answers',
        'navigation',
        'answer',
        'nextBtn', 'previousBtn', 'submitBtn',
    ];

    connect() {
        this.fetchQuizzes();
        this.currentPosition = 0;
    }

    quizzes;

    async fetchQuizzes() {
        await this.loadLastCheckpoint();
        const response = await axios.get('/quizzes');

        this.quizzes = response.data;
        this.renderQuizList();

    }

    renderQuizList() {
        this.quizListTarget.innerHTML = Object.entries(this.quizzes)
            .map(([id, quiz], index) => {
                let type = 'bg-primary';
                if (quiz.answered > quiz.total - 1) {
                    type = 'bg-success';
                }
                if (quiz.answered === 0) {
                    type = 'bg-secondary';
                }

                let active = (quiz.id === this.currentQuiz?.id) ? 'active' : '';

                return `<li class="${active} list-group-item d-flex justify-content-between align-items-start" 
                            data-action="
                                click->quiz#loadQuiz
                                mouseover->quiz#activateElement
                                mouseout->quiz#deactivateElement
                            "
                            data-id="${quiz.id}">
                    ${quiz.name}
                    <span class="badge ${type} rounded-pill">${quiz.answered}/${quiz.total}</span>
                </li>`
            })
            .join('');
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
        let question;
        this.currentPosition = 0;
        for (question of this.currentQuiz.questions) {
            if (question.answers.length === 0) break;
            this.currentPosition++;
        }
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
            this.showMsg('Quiz was successfully done.', 'info');
            return;
        }


        this.questionTitleTarget.innerText = question.question;

        this.answerTarget.value = '';
        this.answersTarget.innerHTML = '';
        this.hintTarget.hidden = true;
        this.answerTarget.disabled = false;
        this.submitBtnTarget.disabled = false;
        this.quizContentTarget.hidden = false;


        if (question.answers.length > 0) {
            this.renderAnswersFor(this.getQuestion());
            this.answerTarget.disabled = true;
            this.submitBtnTarget.disabled = true;
            this.renderAnswersFor(this.getQuestion());
        } else {
            this.answerTarget.focus();
        }
    }

    showMsg(text, type) {
        this.hintTarget.hidden = false
        this.hintTarget.innerHTML = `<div class="alert alert-${type}">${text}</div>`
    }

    checkpoint() {
        localStorage.setItem(`quizzler`, JSON.stringify({
            quizId: this.currentQuiz.id,
        }))
    }

    async loadLastCheckpoint() {
        let json = localStorage.getItem(`quizzler`);
        const data = JSON.parse(json);
        if (data && data.quizId) {
            await this._loadQuizById(data.quizId);
            this.showCurrentQuestion();
        }
    }

    async submitAnswer(event) {
        if (this.submitBtnTarget.disabled) {
            return;
        }
        const userAnswer = this.answerTarget.value;
        const question = this.currentQuiz.questions[this.currentPosition];

        if (!userAnswer) {
            return;
        }
        this.answerTarget.disabled = true;
        this.submitBtnTarget.disabled = true;

        this.quizzes.forEach((quiz) => {
            if (quiz.id === this.currentQuiz.id) {
                quiz.answered++;
            }
        });
        this.renderQuizList();
        await axios.post('/answer-to?question_id=' + question.id, {content: userAnswer})
            .then(response => {
                question.answers.push(response.data);
                this.renderAnswersFor(question);
                this.nextBtnTarget.focus();
            });
    }

    nextQuestion() {
        this.currentPosition++;
        this.showCurrentQuestion();
    }

    previousQuestion() {
        this.currentPosition--;
        this.showCurrentQuestion();
    }

    getQuestion() {
        return this.currentQuiz.questions[this.currentPosition];
    }

    toggleAnswerCorrectness(e) {
        console.log(e.target);
        // todo add a call to PATCH /question/{id}/answer/{id}
    }

    renderAnswersFor(question) {
        let wasAnswered = '';
        if (question.answers.length > 0) {
            wasAnswered = `<small class="text-secondary">This question was answered ${question.answers.length} time(s)</small>`
        }
        this.answersTarget.innerHTML = wasAnswered;

        const answersHtml = question.answers
            .map((answer) => `<li data-action="click->quiz#toggleAnswerCorrectness" class="${answer.isCorrect ? "text-success" : "text-danger"}">`
                + (answer.isCorrect ? `<i class="ri-check-line"></i>` : `<i class="ri-close-line"></i>`)
                + ` ${answer.content}${answer.isCorrect ? ' (correct)' : ''}`
                + `</li>`)
            .join('');

        this.answersTarget.innerHTML = `${wasAnswered} <h5>Answers:</h5><ul>${answersHtml}</ul>`;
    }

    showHint() {
        this.showMsg('A hint.', 'info');
    }
}