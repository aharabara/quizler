// assets/controllers/quiz_controller.js
import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import {log} from "../decorators";

export default class QuizController extends Controller {
    static targets = [
        'quizList',
        'quizContent',
        'questionContainer',
        'stats',
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

    @log('Fetch quizzes')
    async fetchQuizzes() {
        const response = await axios.get('/api/quizzes.json');

        this.quizzes = response.data;
        this.renderQuizList();
        this.loadLastCheckpoint().then();

    }

    renderQuizList() {
        this.quizListTarget.innerHTML = Object.entries(this.quizzes)
            .map(([_, quiz], index) => {
                let type = 'bg-primary';
                console.log(quiz);
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
        this.quizListTarget
            .querySelectorAll('li')
            .forEach((el) => el.classList.remove('active'));

        event.target.classList.add('active')

        const quizId = event.target.dataset.id;

        await this._loadQuizById(quizId);
        this.checkpoint();
    }

    @log(' - Load quiz by ID')
    async _loadQuizById(quizId) {
        this.currentQuiz = (await axios.get(`/api/quizzes/${quizId}.json`)).data;
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

    @log(' - Show quizes')
    showCurrentQuestion() {
        const question = this.currentQuiz.questions[this.currentPosition];
        this.checkpoint();

        this.showQuizAnswers(this.currentQuiz.questions.filter((q) => q.answers.length > 0));

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
        this.hintTarget.hidden = true;
        this.answerTarget.disabled = false;
        this.submitBtnTarget.disabled = false;
        this.quizContentTarget.hidden = false;


        if (question.answers.length > 0) {
            this.answerTarget.disabled = true;
            this.submitBtnTarget.disabled = true;
        } else {
            this.answerTarget.focus();
        }
    }

    showQuizAnswers(questions) {
        // this.questionContainerTarget.hidden = true;
        this.statsTarget.innerHTML = questions
            .reverse()
            .map((q) => `<div class="question-item">`
                + `<b>${q.value}</b><br/>`
                + q.answers.map((a) => `<small class="text-secondary"> - ${a.value}</small>`).join('<br/>')
                + `</div>`
            )
            .join("<br/>");
    }

    showMsg(text, type) {
        this.hintTarget.hidden = false
        this.hintTarget.innerHTML = `<div class="alert alert-${type}">${text}</div>`
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
        await axios.post('/api/answers', {
            value: userAnswer,
            question: `/api/questions/${question.id}`,
            correct: true
        })
            .then(response => {
                this.currentQuiz
                    .questions[this.currentPosition]
                    .answers
                    .push(response.data);

                console.log(this.currentQuiz
                    .questions[this.currentPosition]
                    .answers);

                this.showAnsweredQuestions();
                this.nextBtnTarget.focus();
            });
    }

    showAnsweredQuestions() {
        console.log(this.currentQuiz.questions.filter((q) => q.answers.length > 0));

        this.showQuizAnswers(
            this.currentQuiz
                .questions
                .filter((q) => q.answers.length > 0)
        );
    }

    nextQuestion() {
        this.currentPosition++;
        this.showCurrentQuestion();
    }

    previousQuestion() {
        this.currentPosition--;
        this.showCurrentQuestion();
    }

    showHint() {
        this.showMsg('A hint.', 'info');
    }
}