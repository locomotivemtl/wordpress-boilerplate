import { ComponentElement } from '@locomotivemtl/component-manager';

class Example extends HTMLElement {
    constructor() {
        super();
    }

    connectedCallback() {}

    disconnectedCallback() {}
}

customElements.define('c-example', ComponentElement(Example, 'Example'));
