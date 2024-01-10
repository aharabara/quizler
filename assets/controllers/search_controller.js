import { Controller } from "@hotwired/stimulus"

/**
 * @property {HTMLElement} element
 * @property {HTMLElement[]} itemTargets
 * */
export default class extends Controller {

    static targets = [
        'item'
    ];
    connect() {
        super.connect();
    }

    search(e){
        const value = e.target.value.toLowerCase();
        if (!this.targets.has('item')){
            console.log("There are no items to search through.");
            return;
        }
        this.itemTargets.forEach(el => {
            if (!el.innerText.toLowerCase().includes(value)){
                el.classList.add('d-none');
            } else {
                el.classList.remove('d-none');
            }
        })
    }
}
