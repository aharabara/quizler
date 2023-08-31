import {Controller} from "@hotwired/stimulus";

/**
 * @property {HTMLElement} modalMessageTarget
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
        'modalInput',
        'cancelBtn',
        'submitBtn',
    ];

    resolvePromise;
    rejectPromise;

    initialize() {
    }

    alert(message) {
        this.cancelBtnTarget.style.display = 'none';
        this.modalInputTarget.style.display = 'none';
        this.submitBtnTarget.style.display = 'inline';

        /* fixme rewrite element displaying to class based stuff.  */
        return new Promise((resolve) => {
            this.modalMessageTarget.innerText = message;

            this.resolvePromise = () => {
                resolve();
                this.hideModal();
            };

            this.showModal();
        });
    }

    ask(message) {
        this.cancelBtnTarget.style.display = 'inline';
        this.submitBtnTarget.style.display = 'inline';
        this.modalInputTarget.style.display = 'block';
        this.modalInputTarget.value = '';

        return new Promise((resolve, reject) => {
            this.modalMessageTarget.textContent = message;

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
