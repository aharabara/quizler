// assets/controllers/quiz_controller.js
import {Controller} from '@hotwired/stimulus';
import {log} from "../decorators";
import QuizRepository from "../src/Repository/QuizRepository";

/**
 * @property {AlertController} alertOutlet
 * @property {HTMLElement} statsTarget
 * @property {HTMLElement} quizContentTarget
 * @property {HTMLElement} answerTarget
 * @property {HTMLElement} questionTitleTarget
 * @property {HTMLElement} questionContainerTarget
 * @property {HTMLElement} element
 * @property {HTMLElement} nextBtnTarget
 * @property {ItemListController} itemListOutlet
 * @property {TemplatedListController} templatedListOutlet
 * */
export default class QuizController extends Controller {
    static targets = [
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
        'alert',
        'item-list',
        'templated-list'
    ];

    /** @type {Quiz[]} */
    quizzes;

    /** @type {Quiz} */
    currentQuiz;

    connect() {
        this.quizRepository = new QuizRepository();
    }

    itemListOutletConnected() {
        this.itemListOutlet.setOptions({
            badgeResolver: (/** @type {Quiz} */ item) => `${item.answered}/${item.total}`,
            badgeTypeResolver: (/** @type {Quiz} */ item) => {
                if (item.answered > item.total - 1) return 'bg-success';
                if (item.answered === 0)  return 'bg-secondary';
                return 'bg-primary';
            },
            labelResolver: (/** @type {Quiz} */ item) => item.value,
            idResolver: (/** @type {Quiz} */ item) => item.id,
        })

        this.quizRepository.findAll().then((quizzes) => {
            this.quizzes = quizzes;
            this.currentPosition = 0;
            this.itemListOutlet.setItems(this.quizzes)
            this.loadLastCheckpoint().then();
        });

        this.itemListOutlet.onSelection(this.onQuizSelection);
    }

    @log(' - Load quiz by ID')
    async _loadQuizById(quizId) {
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
            console.log('Quiz was successfully done.');
            // this.showMsg('Quiz was successfully done.', 'success');
            return;
        }
        console.log(" - - Only question.")


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
    prepareAnswers(answers) {
        return answers.map((item) => {
            item.value = item.value.textFromHTML();
            return item;
        });
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
            this.itemListOutlet.selectItem(`${data.quizId}`.toInt())
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
                this.itemListOutlet.setItems(this.quizzes)
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
                this.itemListOutlet.setItems(this.quizzes)
                this.showAnsweredQuestions()
            });

    }

    async showAnsweredQuestions() {
        let answers = this.prepareAnswers(
            await this.quizRepository.fetchAllAnswers(this.currentQuiz.id)
        );

        this.templatedListOutlet.setItems(answers);
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

    async onQuizSelection(item) {
        await this._loadQuizById(item.id);
        this.checkpoint();
    }
}
