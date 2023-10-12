// assets/controllers/quiz_controller.js
import {Controller} from '@hotwired/stimulus';
import {log} from "../decorators";
import QuizRepository from "../src/Repository/QuizRepository";

/**
 * @property {AlertController} alertOutlet
 * @property {HTMLElement} element
 * @property {ItemListController} itemListOutlet
 * @property {TemplatedListController} templatedListOutlet
 * @property {QuizFormController} quizFormOutlet
 * */
export default class QuizController extends Controller {
    static targets = [
    ];


    static outlets = [
        'alert',
        'item-list',
        'templated-list',
        'quiz-form'
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
            deletable: true,
            badgeResolver: (/** @type {Quiz} */ item) => `${item.answered}/${item.total}`,
            badgeTypeResolver: (/** @type {Quiz} */ item) => {
                if (item.answered > item.total - 1) return 'bg-success';
                if (item.answered === 0)  return 'bg-secondary';
                return 'bg-primary';
            },
            labelResolver: (/** @type {Quiz} */ item) => item.value,
            idResolver: (/** @type {Quiz} */ item) => item.id,
        })
        this.loadQuizzes().then();

        this.itemListOutlet.onSelection(this.onQuizSelection.bind(this));
        this.itemListOutlet.onDelete(this.onQuizDelete.bind(this));
    }

    async loadQuizzes() {
        this.quizzes = await this.quizRepository.findAll();
        this.currentPosition = 0;
        this.itemListOutlet.setItems(this.quizzes)
        this.loadLastCheckpoint().then();
    }

    quizFormOutletConnected(){
        const that = this;
        this.quizFormOutlet.onAnswerSubmit((answer) => {
            that.currentQuiz
                .questions[that.currentPosition]
                .answers
                .push(answer);

            that.itemListOutlet.setItems(that.quizzes)

            that.showAnsweredQuestions().then(r => null);

            that.quizzes.forEach((quiz) => {
                if (quiz.id === that.currentQuiz.id) {
                    quiz.answered++;
                }
            });
            this.nextQuestion();

        });
    }

    @log(' - Load quiz by ID')
    async _loadQuizById(quizId) {
        try {
            this.currentQuiz = await this.quizRepository.findById(quizId);
        } catch (e) {
            // replace with a toast
            this.alertOutlet.alert('Quiz cannot be loaded.')
            return;
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

        if (!question) {
            this.previousQuestion();
            console.log('Quiz was successfully done.');
            // this.showMsg('Quiz was successfully done.', 'success');
            return;
        }

        this.quizFormOutlet.showCurrentQuestion(question)
        this.showAnsweredQuestions().then(r => null);

        this.checkpoint();
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

    async askQuestion() {
        let question = await this.alertOutlet
            .ask('What is your question?', `This question is going to be added at the end of the \`${this.currentQuiz.value}\` quiz.`);

        this.quizRepository
            .createQuestion(this.currentQuiz.id, question)
            .then(question => {
                this.currentQuiz.total++;
                this.currentQuiz.questions.push(question);
                this.itemListOutlet.setItems(this.quizzes)
                this.showAnsweredQuestions()
            });

    }

    async showAnsweredQuestions() {
        this.templatedListOutlet
            .setItems(await this.quizRepository.fetchAllAnswers(this.currentQuiz.id));
    }

    async onQuizSelection(item) {
        await this._loadQuizById(item.id);
        this.checkpoint();
    }
    async onQuizDelete(item) {
        const quizName = await this
            .alertOutlet
            .ask(`Do you want to delete \`${item.value}\`?`, 'Introduce **quiz name** to confirm.');

        if (quizName === item.value) {
            await this.quizRepository.deleteQuiz(item.id);
            await this.loadQuizzes();
            const firstQuiz = this.quizzes.at(0);
            this.itemListOutlet.selectItem(firstQuiz.id);
            this.checkpoint();
            await this.alertOutlet
                .alert(`Quiz '${item.value}' deleted successfully.`);

        } else {
            await this.alertOutlet
                .alert(`Wrong quiz name.`, `Expected: \`${item.value}\`, got \`${quizName}.\``);
        }
    }

    nextQuestion() {
        if (this.currentPosition === (this.currentQuiz.questions.length - 1)) return;
        this.currentPosition++;
        this.showCurrentQuestion();

    }

    previousQuestion() {
        if (this.currentPosition === 0) return;
        this.currentPosition--;
        this.showCurrentQuestion();
    }
}
