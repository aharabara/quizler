import axios from "axios";

/**
 * @typedef Answer
 * @property {Number} id
 * @property {String} value
 * @property {String} correct
 **/
/**
 * @typedef Question
 * @property {Number} id
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
        return await axios.post('/api/answers', {
            value: value,
            question: `/api/questions/${id}`,
            correct: true
        })
    }

    /**
     * @param {Number} id
     * @param {String} value
     * @return {Promise<Question>}
     **/
    async createQuestion(id, value) {
        return await axios.post('/api/questions', {
            value: value,
            quiz: `/api/quizzes/${id}`,
        });
    }
}
