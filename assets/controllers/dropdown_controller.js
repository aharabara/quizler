import {Controller} from "@hotwired/stimulus"
import Dropdown from "bootstrap/js/src/dropdown";

/**
 * @property {HTMLElement} element
 * */
export default class extends Controller {
  component;
  connect() {
    super.connect();
    this.component = new Dropdown(this.element, {})
  }
}
