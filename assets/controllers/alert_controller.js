import {Controller} from "@hotwired/stimulus";

/**
 * @property {HTMLElement} modalMessageTarget
 * @property {HTMLElement} modalDescriptionTarget
 * @property {HTMLElement} submitBtnTarget
 * @property {HTMLElement} modalInputTarget
 * @property {HTMLElement} cancelBtnTarget
 * @property {HTMLElement} element
 *
 * */
export default class AlertController extends Controller {

    static targets = [
        'modalElement',
        'modalMessage',
        'modalDescription',
        'modalInput',
        'cancelBtn',
        'submitBtn',
    ];

    resolvePromise;
    rejectPromise;

    initialize() {
    }

    alert(message, description = '') {
        this.cancelBtnTarget.style.display = 'none';
        this.modalInputTarget.style.display = 'none';
        this.submitBtnTarget.style.display = 'inline';

        /* fixme rewrite element displaying to class based stuff.  */
        return new Promise((resolve) => {
            this.setModalDetails(message, description);
            this.resolvePromise = () => {
                resolve();
                this.hideModal();
            };

            this.showModal();
        });
    }

    confirm(message, description = '') {
        this.cancelBtnTarget.style.display = 'inline';
        this.modalInputTarget.style.display = 'none';
        this.submitBtnTarget.style.display = 'inline';

        /* fixme rewrite element displaying to class based stuff.  */
        return new Promise((resolve, reject) => {
            this.setModalDetails(message, description);
            this.resolvePromise = () => {
                resolve(true);
                this.hideModal();
            };
            this.rejectPromise = () => {
                resolve(false);
                this.hideModal();
            };
            this.showModal();
        });
    }

    setModalDetails(message, description) {
        this.modalMessageTarget.innerHTML = message.markdowned;
        this.modalDescriptionTarget.innerHTML = description.markdowned;
    }

    ask(message, description = '', answer = '') {
        this.cancelBtnTarget.style.display = 'inline';
        this.submitBtnTarget.style.display = 'inline';
        this.modalInputTarget.style.display = 'block';
        this.modalInputTarget.value = answer;

        return new Promise((resolve, reject) => {
            this.setModalDetails(message, description);
            this.resolvePromise = () => {
                resolve(this.modalInputTarget.value);
                this.hideModal();
            };

            this.rejectPromise = () => {
                reject('Cancelled');
                this.hideModal();
            };

            this.showModal();
            this.modalInputTarget.focus();
        });
    }

    submit(){
        this.resolvePromise();
    }
    cancel(){
        this.rejectPromise();
    }

    showModal() {
        this.element.style.display = 'flex';
    }

    hideModal() {
        this.element.style.display = 'none';
    }
}
