import { Controller } from "@hotwired/stimulus"

/**
 * @property {HTMLElement} element
 * */
export default class extends Controller {
    static targets = [
    ]

    connect() {
        super.connect();
        if (this.element.tagName !== 'HTML'){
            throw new Error('Application controller can be used only on <html/> tag.')
        }
        this.setCurrentTheme(this.getCurrentTheme())
    }

    /**
     * @return String
     **/
    getCurrentTheme() {
        return localStorage.getItem('app.theme.current', 'dark');
    }

    /**
     * @return void
     **/
    changeTheme(){
        let currentTheme = this.element.getAttribute('data-bs-theme');

        let newTheme = currentTheme === 'light' ? 'dark' : 'light';

        this.setCurrentTheme(newTheme);
    }

    /**
     * @param {String} newTheme
     * @return void
     **/
    setCurrentTheme(newTheme) {
        localStorage.setItem('app.theme.current', newTheme);
        this.element
            .setAttribute('data-bs-theme', newTheme);
    }
}
