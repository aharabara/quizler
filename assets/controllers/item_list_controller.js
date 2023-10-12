import {Controller} from '@hotwired/stimulus';

/** @typedef ItemListOptions
 *  @property {Function} badgeResolver
 *  @property {Function} labelResolver
 *  @property {Function} badgeTypeResolver
 *  @property {Function} idResolver
 *  @property {Boolean} deletable
 **/
/**
 * @property {AlertController} alertOutlet
 * @property {HTMLElement} element
 * @property {HTMLElement} listTarget
 * @property {HTMLElement} itemTarget
 * @property {HTMLElement[]} itemTargets
 * */
export default class ItemListController extends Controller {
    static get EVENT_SELECT_ITEM() {
        return 'list.select-item';
    };

    static get EVENT_DELETE_ITEM() {
        return 'list.delete-item';
    };


    static targets = [
        'item',
    ];

    /** @type {Object[]} items*/
    #items;

    /** @type {Object} selected*/
    #selected;

    /** @type {Object.<String, Function[]>} */
    #listeners = {};

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
        this.addEventListener(ItemListController.EVENT_SELECT_ITEM, callback);

        return this;
    }

    addEventListener(event, callback) {
        if (!this.#listeners[event]) {
            this.#listeners[event] = [];
        }
        this.#listeners[event].push(callback);
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
        if (!target) {
            console.log(`Cannot find item list element by ID ${itemId}.`)
            return;
        }
        target.classList.add('active');


        this.#selected = this.findItemById(itemId);
        this.getEventListeners(ItemListController.EVENT_SELECT_ITEM)
            .map((callback) => callback(this.#selected));
    }

    /** @param {String} event*/
    getEventListeners(event) {
        return this.#listeners[event];
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
                let actions = '';
                if (this.#options.deletable) {
                    actions += `contextmenu->${controller}#deleteItem:prevent`;
                }

                return `<li class="${active} list-group-item d-flex justify-content-between align-items-start"
                            data-item-list-target="item"
                            data-action="
                                click->${controller}#onItemClick:self
                                mouseover->${controller}#activateItem
                                mouseout->${controller}#deactivateItem
                                ${actions}
                            "
                            data-id="${id}">
                    ${itemText}
                    <span class="${badgeType} badge rounded-pill">${badgeValue}</span>
                </li>`
            })
            .join('');
    }

    deleteItem(e) {
        let itemId = e.target.dataset.id.toInt();
        let item = this.findItemById(itemId);
        if (!item) {
            throw Error(`Cannot find the item by id ${itemId}.`);
        }
        this.getEventListeners(ItemListController.EVENT_DELETE_ITEM).map((callback) => callback(item))
    }

    /** @param {Function} callback*/
    onDelete(callback) {
        this.addEventListener(ItemListController.EVENT_DELETE_ITEM, callback);

        return this;
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
        options.deletable ??= false;

        this.#options = options;
    }
}
