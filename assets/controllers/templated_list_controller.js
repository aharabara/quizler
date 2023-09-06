import {Controller} from '@hotwired/stimulus';

/**
 * @property {HTMLElement} element
 * @property {HTMLElement} templateTarget
 * */
export default class TemplatedListController extends Controller {
    static targets = [
        'template',
        'item',
    ];

    /** @type {Object[]} items*/
    #items;

    /** @type {String} */
    #template;

    /**
     *  @param {Object[]} items
     *  @return TemplatedListController
     **/
    setItems(items) {
        this.#items = items;
        this.render()

        return this;
    }


    render() {
        const controller = this.identifier;
        const template = this.getTemplate();

        this.element.innerHTML = Object.entries(this.#items)
            .map(([_, item], index) => {
                return template.supplant({
                    item: item,
                    controller: controller
                });
            })
            .join('');
    }

    getTemplate() {
        if (!this.#template) {
            this.#template = this.templateTarget.innerHTML;
        }

        return this.#template;
    }
}
