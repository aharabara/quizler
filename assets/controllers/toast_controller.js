import { Controller } from "@hotwired/stimulus"
import {Toast} from "bootstrap";

/**
 * @property {HTMLElement} element
 * */
export default class extends Controller {
    connect() {
        super.connect();
        let toast = new Toast(this.element, {});
        toast.show();
    }
}
