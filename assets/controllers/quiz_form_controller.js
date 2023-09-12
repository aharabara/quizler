// assets/controllers/quiz_controller.js
import {Controller} from '@hotwired/stimulus';
import QuizRepository from "../src/Repository/QuizRepository";

/**
 * @property {AlertController} alertOutlet
 * @property {HTMLElement} quizContentTarget
 * @property {HTMLElement} answerTarget
 * @property {HTMLElement} questionTitleTarget
 * @property {HTMLElement} questionContainerTarget
 * @property {HTMLElement} element
 * @property {HTMLElement} nextBtnTarget
 * */
export default class QuizFormController extends Controller {
    static targets = [
        'quizContent',
        'questionContainer',
        'questionTitle',
        'answers',
        'answer',
        'submitBtn',
    ];


    /** @type {Function[]} */
    #callbacks = [];

    /** @type {Question} */
    #currentQuestion

    connect() {
        this.quizRepository = new QuizRepository();
    }


    showCurrentQuestion(question) {
        this.#currentQuestion = question;
        this.questionContainerTarget.hidden = false;

        this.questionTitleTarget.innerText = question.value;

        this.answerTarget.value = '';
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

    async submitAnswer(event) {
        if (this.submitBtnTarget.disabled) {
            return;
        }
        const userAnswer = this.answerTarget.value;
        if (!userAnswer) {
            return;
        }
        this.answerTarget.disabled = true;
        this.submitBtnTarget.disabled = true;

        this.quizRepository
            .answerQuestion(this.#currentQuestion.id, userAnswer)
            .then(answer => {
                this.#callbacks.forEach((callback) => callback(answer))
            });
    }

    onAnswerSubmit(callback){
        this.#callbacks.push(callback);
    }
}
