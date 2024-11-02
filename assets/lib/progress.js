function counter(elements) {
    if (elements.length === 2) {
      elements[0].parentElement.parentElement.style.pointerEvents = 'none';
      elements.forEach(element => {
        if (element.classList.contains('progress-bar')) {
            setTimeout(() => {
              element.style.width = element.getAttribute('data-value') +'%';
              element.style.animation = 'none';
              element.style.animation = 'progressAnimation 2s';
            }, 2000);
          } else {
            setTimeout(() => {
              element.style = '--num: '+element.getAttribute('data-value')+';';
            }, 2000);
          }
      });
      setTimeout(() => {
          elements[0].parentElement.parentElement.style.pointerEvents = 'auto';
      }, 4000);
    } else {
      elements.onmouseover = function() {
      };
      elements.onmouseout = function() {
      };
      elements.style = '--num: '+elements.getAttribute('data-value')+';';
    }
  }
  
  function resetCounter(elements) {
    if (elements.length === 2) {
      elements.forEach(element => {
          element.parentElement.parentElement.style.pointerEvents = 'none';
          if (element.classList.contains('progress-bar')) {
            element.style.width = 0;
            element.style.animation = 'none';
            element.style.animation = 'progressAnimationReverse 2s';
          } else {
            element.style = '--num: 0;';
          }
          setTimeout(() => {
              element.dispatchEvent(new Event('mouseout'));
          }, 2000);
      });
    } else {
      elements.onmouseover = function() {
      };
      elements.onmouseout = function() {
      };
      elements.style = '--num: 0;';
    }
  }