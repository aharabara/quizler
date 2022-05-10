class ElementContainer {
    constructor(document) {
        this.questionText = document.getElementById('question-text');
        this.answer = document.getElementById('answer');
        this.latestAnswers = document.getElementById('latest-answers');
        this.answerForm = document.getElementById('answer-form');
        this.newQuestionForm = document.getElementById('question-form');
        this.newQuestion = document.getElementById('new-question');
    }

    /**
     * @param {string} text
     **/
    setQuestionText(text){
        this.questionText.innerText = text;
        return this;
    }

    /**
     * @param {string[]} answers
     * */
    setLatestAnswers(answers){
        this.latestAnswers.innerHTML = answers.map((s) => `<p>${s}</p>`).join("\n");
        return this;
    }
}