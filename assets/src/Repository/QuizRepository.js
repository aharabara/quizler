import axios from "axios";
/**
 * @typedef IRI
 * @extends String
 **/

/**
 * @typedef Answer
 * @property {Number} id
 * @property {String} value
 * @property {String} questionText
 * @property {String} correct
 **/
/**
 * @typedef Question
 * @property {Number} id
 * @property {IRI} quiz
 * @property {String} value
 * @property {Answer[]} answers
 **/
/**
 * @typedef Quiz
 * @property {Number} id
 * @property {String} value
 * @property {Number} total
 * @property {Number} answered
 * @property {Number} version
 * @property {Question[]} questions
 * */

export default class QuizRepository {

    /** @return {Promise<Quiz[]>} */
    async findAll() {
        const response = await axios.get('/api/quizzes.json');

        return response.data;
    }

    /**
     * @param {Number} id
     * @return {Promise<Quiz>}
     **/
    async findById(id) {
        return (await axios.get(`/api/quizzes/${id}.json`)).data
    }

    /**
     * @param {Number} id
     * @param {String} value
     * @return {Promise<Answer>}
     **/
    async answerQuestion(id, value) {
        return (await axios.post('/api/answers', {
            value: value,
            question: `/api/questions/${id}`,
            author : this.getCurrentUserIRI(),
            correct: true
        })).data
    }

    /**
     * @param {Number} id
     * @param {String} value
     * @return {Promise<Question>}
     **/
    async createQuestion(id, value) {
        /** @todo use a @notifyAboutViolationsOnError decorator */
        return await axios.post('/api/questions', {
            value: value,
            author : this.getCurrentUserIRI(),
            quiz: `/api/quizzes/${id}`,
        });
    }

    getCurrentUserIRI() {
        // todo implement
        return "/api/users/1";
    }

    /**
     * @param {Number} quizId
     * @return {Promise<Answer[]>}
     **/
    async fetchAllAnswers(quizId) {
        return (await axios.get(`/api/answers.json?question.quiz=${quizId}`)).data;

    }

    /**
     * @param {Number} quizId
     * @return {Promise<Boolean>}
     **/
    async deleteQuiz(quizId) {
        return (await axios.delete(`/api/quizzes/${quizId}.json`)).status === 204;
    }
}
