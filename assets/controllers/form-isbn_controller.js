import { Controller } from "@hotwired/stimulus";

export default class FormIsbnController extends Controller {
    static targets = ['isbnSubmit', 'isbnForm', 'isbnResults'];

    connect() {
        console.log('form-isbn-controller');
    }

    async submitForm(event) {
        event.preventDefault();
        const action = event.target.action;
        const isbn = event.target.querySelector('input[name="form[isbn]"]').value;

        const url = action + '?isbn=' + isbn;

        await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then((response) => {
            return response.text();
        }).then((html) => {
            this.isbnResultsTarget.innerHTML = html;
        });
    }
}