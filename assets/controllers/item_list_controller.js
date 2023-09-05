import {Controller} from '@hotwired/stimulus';

/** @typedef ItemListOptions
 *  @property {Function} badgeResolver
 *  @property {Function} labelResolver
 *  @property {Function} badgeTypeResolver
 *  @property {Function} idResolver
 **/
/**
 * @property {AlertController} alertOutlet
 * @property {HTMLElement} element
 * @property {HTMLElement} listTarget
 * @property {HTMLElement} itemTarget
 * @property {HTMLElement[]} itemTargets
 * */
export default class ItemListController extends Controller {
    static targets = [
        'item',
    ];

    /** @type {Object[]} items*/
    #items;

    /** @type {Object} selected*/
    #selected;

    /** @type {Function[]} */
    #onSelectionCallbacks = [];

    /** @type {ItemListOptions} */
    #options;

    /**
     *  @param {Object[]} items
     *  @return ItemListController
     **/
    setItems(items) {
        this.#items = items;
        this.render()

        return this;
    }

    /**
     *  @param {Function} callback
     *  @return ItemListController
     **/
    onSelection(callback) {
        this.#onSelectionCallbacks.push(callback);

        return this;
    }

    onItemClick(e) {
        const itemId = e.target.dataset.id.toInt();

        this.selectItem(itemId);
    }

    /** @param {Number} itemId */
    selectItem(itemId) {
        this.itemTargets.forEach((el) => el.classList.remove('active'));

        /** @var {HTMLElement} */
        const target = this.findItemElementById(itemId);
        target.classList.add('active');


        this.#selected = this.findItemById(itemId);
        this.#onSelectionCallbacks.map((callback) => callback(this.#selected));
    }

    /** @param {Number} itemId */
    findItemById(itemId) {
        let items = this.#items
            .filter((value, index) => {
                return this.#options.idResolver(value) === itemId
            });

        if (items.length === 0) return null;

        return items[0];
    }

    /** @param {Number} itemId */
    findItemElementById(itemId) {
        let elements = this.itemTargets.filter((el) => el.dataset.id.toInt() === itemId);
        if (elements.length === 0) return null;

        return elements[0];
    }

    render() {
        const controller = this.identifier;
        this.element.innerHTML = Object.entries(this.#items)
            .map(([_, item], index) => {

                let active = (item.id === this.#selected?.id) ? 'active' : '';

                let badgeValue = this.#options.badgeResolver(item);
                let itemText = this.#options.labelResolver(item);
                let badgeType = this.#options.badgeTypeResolver(item);
                let id = this.#options.idResolver(item);

                return `<li class="${active} list-group-item d-flex justify-content-between align-items-start"
                            data-item-list-target="item"
                            data-action="
                                click->${controller}#onItemClick:self
                                mouseover->${controller}#activateItem
                                mouseout->${controller}#deactivateItem
                            "
                            data-id="${id}">
                    ${itemText}
                    <span class="${badgeType} badge rounded-pill">${badgeValue}</span>
                </li>`
            })
            .join('');
    }

    activateItem(e) {
        e.target.classList.add('list-group-item-primary')
    }

    deactivateItem(e) {
        e.target.classList.remove('list-group-item-primary')
    }

    /** @param {ItemListOptions|object} options*/
    setOptions(options) {
        options.badgeResolver ??= (item) => '';
        options.badgeTypeResolver ??= (item) => '';
        options.labelResolver ??= (item) => item.value;
        options.idResolver ??= (item) => item.id;

        this.#options = options;
    }
}
