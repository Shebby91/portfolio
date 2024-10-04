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
            if (aspectRatio > 1) {
                document.getElementById('imagesModalImage').style.maxWidth = '85%';
                document.getElementById('imagesModalImage').style.maxHeight = '';
                document.getElementById('imagesModalImage').src = event.relatedTarget.querySelector('img').src;
            } else {
                document.getElementById('imagesModalImage').style.width = 'auto';
                document.getElementById('imagesModalImage').style.maxWidth = '';
                document.getElementById('imagesModalImage').style.maxHeight = '95%';
                document.getElementById('imagesModalImage').src = event.relatedTarget.querySelector('img').src;

            }
            
        })
    }
}
