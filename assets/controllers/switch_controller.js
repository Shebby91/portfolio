import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
  connect() {
    function fadeTo(element, toValue = 0, duration = 500) {

      const fromValue = parseFloat(element.style.opacity) || 1;

      const startTime = Date.now();

      const framerate = 1000 / 60;

      let interval = setInterval(() => {
        const currentTime = Date.now();
        const timeDiff = (currentTime - startTime) / duration;

        const value = fromValue - (fromValue - toValue) * timeDiff;

        if (timeDiff >= 1) {
          clearInterval(interval);
          interval = 0;
          if (toValue == 0) {
            element.remove();
          }
        }

        element.style.opacity = value.toString();
      }, framerate)
    }

    const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
    const appendAlert = (message, type) => {
      const wrapper = document.createElement('div')
      wrapper.innerHTML = [
        `<div class="alert alert-${type} alert-dismissible mx-4" role="alert">`,
        `   <div>${message}</div>`,
        '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
        '</div>'
      ].join('')

      alertPlaceholder.append(wrapper)
      setTimeout(() => {
        fadeTo(wrapper)
      }, 2200);
    }

    async function unset(item) {
      const itemId = item.getAttribute('data-id');
      const itemType = item.getAttribute('data-switch');

      if (!itemId) {
        return;
      }

      if (!itemType) {
        return;
      }

      const data = { id: itemId };

      const isVerifiedUrl = '/admin/endpoint/reset/verified';
      const twoFactorUrl = '/admin/endpoint/reset/2fa';
      let url = isVerifiedUrl;

      if (itemType == 2) {
        url = twoFactorUrl;
      }
      const csrfToken = document.getElementById('csrf_token').value;

      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken, // CSRF-Token im Header mitschicken
          },
          body: JSON.stringify(data),
        });

        if (response.ok) {
          const jsonResponse = await response.json();
          item.classList.remove('text-bg-success', 'text-success');
          item.classList.add('text-bg-danger', 'text-danger');
          item.disabled = true;
          appendAlert(jsonResponse.message, 'success')
        } else {
          appendAlert(response.status, 'error')
        }
      } catch (error) {
        console.error('Request failed', error);
        appendAlert('Request failed: ' + error, 'error');
      }
    }

    const switches = document.querySelectorAll('[data-switch]')
    switches.forEach(item => {
      item.addEventListener('input', event => {
        if (!item.checked) {
          unset(item);
        }
      })
    })
  }
}
