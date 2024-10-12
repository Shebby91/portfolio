import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
        const imgModal = document.getElementById('imagesModal')
        imgModal.addEventListener('show.bs.modal', event => {
            const aspectRatio = event.relatedTarget.querySelector('img').naturalWidth / event.relatedTarget.querySelector('img').naturalHeight;
            const img = document.getElementById('imagesModalImage');
            if (aspectRatio > 1) {
                img.style.maxWidth = '75%';
                img.style.maxHeight = '';
                img.src = event.relatedTarget.querySelector('img').src;
            } else {
                img.style.width = 'auto';
                img.style.maxWidth = '';
                img.style.maxHeight = '95%';
                img.src = event.relatedTarget.querySelector('img').src;
            }
        })
    }
}
