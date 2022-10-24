import { Controller } from '@hotwired/stimulus';
import { Modal } from "bootstrap";

export default class FormModalFormController extends Controller {
    static targets = ['modal', 'modalBody', 'modalTitle'];

    formUrl = null;
    modal = null;

    async openModal(event) {
        this.modalTitleTarget.innerHTML = event.target.dataset.modalTitle;
        this.formUrl = event.target.dataset.modalUrl;
        let response = await fetch(this.formUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        let data = await response.text();
        this.modal = new Modal(this.modalTarget);
        this.modalBodyTarget.innerHTML = data;
        this.modal.show();
    }

    async submitForm(event) {
        event.preventDefault();
        const form = this.modalBodyTarget.getElementsByTagName('form')[0];

        await fetch(this.formUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        }).then((response) => {
            if (response.ok) {
                this.modal.hide();
            } else {
                return response.text();
            }
        }).then((html) => {
            this.modalBodyTarget.innerHTML = html;
        });
    }
}
