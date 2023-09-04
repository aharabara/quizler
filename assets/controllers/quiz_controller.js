// assets/controllers/quiz_controller.js
import {Controller} from '@hotwired/stimulus';
import {log} from "../decorators";
import QuizRepository from "../src/Repository/QuizRepository";

/**
* @property {AlertController} alertOutlet
* @property {HTMLElement} quizListTarget
* @property {HTMLElement} statsTarget
* @property {HTMLElement} quizContentTarget
* @property {HTMLElement} answerTarget
* @property {HTMLElement} answersTarget
* @property {HTMLElement} questionTitleTarget
* @property {HTMLElement} questionContainerTarget
* @property {HTMLElement} quizListTarget
* @property {HTMLElement} element
* @property {HTMLElement} nextBtnTarget
* */
export default class QuizController extends Controller {
    static targets = [
        'quizList',
        'quizContent',
        'questionContainer',
        'stats',
        'questionTitle',
        'answers',
        'navigation',
        'answer',
        'nextBtn', 'previousBtn', 'submitBtn',
    ];


    static outlets = [
        'alert'
    ];

    /** @type {Quiz[]} */
    quizzes;

    /** @type {Quiz} */
    currentQuiz;

    connect() {

        this.quizRepository = new QuizRepository();

        this.quizRepository.findAll().then((quizzes) => {
            this.quizzes = quizzes;
            this.currentPosition = 0;
            this.renderQuizList()
            this.loadLastCheckpoint().then();
        });
    }
    renderQuizList() {
        this.quizListTarget.innerHTML = Object.entries(this.quizzes)
            .map(([_, quiz], index) => {
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
                    ${quiz.value}
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
        const quizId = event.target.dataset.id;
        await this._loadQuizById(quizId);
        this.checkpoint();
    }

    #selectItemMenuByQuizID(quizId) {
        this.quizListTarget
            .querySelectorAll('li')
            .forEach((el) => {
                el.classList.remove('active');
                if (parseInt(el.dataset.id) === parseInt(quizId)) {
                    el.classList.add('active');
                }
            });
    }

    @log(' - Load quiz by ID')
    async _loadQuizById(quizId) {
        this.#selectItemMenuByQuizID(quizId);
        try {
            this.currentQuiz = await this.quizRepository.findById(quizId);
        } catch (e) {
            alert('Cannot obtain the quiz.');
        }
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
    }

    @log(' - Show quizzes')
    showCurrentQuestion() {
        const question = this.currentQuiz.questions[this.currentPosition];
        this.checkpoint();

        this.showAnsweredQuestions();

        if (!question) {
            this.currentPosition--;
            this.showMsg('Quiz was successfully done.', 'success');
            return;
        }
        console.log(" - - Only question.")


        this.questionContainerTarget.hidden = false;

        this.questionTitleTarget.innerText = question.value;

        this.answerTarget.value = '';
        this.answersTarget.innerHTML = '';
        this.answerTarget.disabled = false;
        this.submitBtnTarget.disabled = false;
        this.quizContentTarget.hidden = false;


        if (question.answers.length > 0) {
            this.answerTarget.value = question.answers.map((a) => a.value).join("\n===========\n");
            this.answerTarget.disabled = true;
            this.submitBtnTarget.disabled = true;
        } else {
            this.answerTarget.focus();
        }
    }

    /** @param {Answer[]} answers */
    showQuizAnswers(answers) {
        this.statsTarget.innerHTML = answers
            .map((answer) => `<div class="question-item">`
                    + `<b>${answer.questionText}</b><br/>`
                    + `<small>`
                        + `<pre style="white-space: pre-wrap" class="text-secondary">${answer.value.textFromHTML()}</pre>`
                    + `</small>`
                + `</div>`
            )
            .reverse()
            .join("<br/>");
    }

    @log(' - checkpoint')
    checkpoint() {
        localStorage.setItem(`quizzler`, JSON.stringify({
            quizId: this.currentQuiz.id,
        }))
    }

    @log(' - Load last checkpoint')
    async loadLastCheckpoint() {
        let json = localStorage.getItem(`quizzler`);
        const data = JSON.parse(json);
        if (data && data.quizId) {
            await this._loadQuizById(data.quizId);
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
        // this.renderQuizList();
        this.quizRepository
            .answerQuestion(question.id, userAnswer)
            .then(answer => {
                this.currentQuiz
                    .questions[this.currentPosition]
                    .answers
                    .push(answer);

                this.showAnsweredQuestions();
                this.nextBtnTarget.click();
                this.renderQuizList()
            });
    }

    async askQuestion() {
        let question = await this.alertOutlet.ask('Write your question');

        this.quizRepository
            .createQuestion(this.currentQuiz.id, question)
            .then(question => {
                this.currentQuiz.total++;
                this.currentQuiz.questions.push(question);
                this.answerTarget.focus();
                this.renderQuizList();
                this.showAnsweredQuestions()
            });

    }

    async showAnsweredQuestions() {
        this.showQuizAnswers(await this.quizRepository.fetchAllAnswers(this.currentQuiz.id));
    }

    nextQuestion() {
        if (this.currentPosition === (this.currentQuiz.questions.length - 1)) return;
        this.currentPosition++;
        this.showCurrentQuestion();
    }

    previousQuestion(e) {
        if (this.currentPosition === 0) return;
        this.currentPosition--;
        this.showCurrentQuestion();
    }
}
