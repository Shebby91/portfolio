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
        const profilePicture = document.querySelector("#profile-img");
        // Event-Listener für Änderungen am Input-Element
        const fileInput = document.querySelector("#edit_profile_form_image");
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
        
            if (file) {
                // Überprüfen, ob die Datei ein Bild ist
                if (file.type.startsWith('image/')) {
                    // Erstelle ein URL-Objekt für die Bilddatei
                    const imageUrl = URL.createObjectURL(file);
                
                    // Aktualisiere das `src`-Attribut des Bild-Tags
                    profilePicture.src = imageUrl;
                
                    // Optional: URL wieder freigeben, wenn das Bild nicht mehr gebraucht wird
                    // URL.revokeObjectURL(imageUrl);
                } else {
                    alert('Bitte ein gültiges Bild hochladen.');
                }
            }
        });
    }
}
