/**
 * Adds the functionality to replace content editable text with its underlying key.
 * This can be triggered via a shortcut and also a toggle button in the admin bar.
 *
 * See: editableText() twig function
 */

const elements = document.querySelectorAll("[data-key]");
const body = document.querySelector('body');
const toggleButton = document.getElementById("js-toggle-cms");
const showAllClass = "cms-show-all";
const shortcutKey = "Control";
const currentPath = window.document.URL;

document.addEventListener(
  "DOMContentLoaded", () => {
    initializeEditableText();
  },
);

function initializeEditableText() {
  Array.from(elements).forEach(el => {
    el.onclick = () => {
      if (body.classList.contains(showAllClass)) {
        window.open(`/admin/translation/edit?key=${el.dataset.key}&destination=${currentPath}`, '_blank');
      }
    }
  })
  setTimeout(() => {
    document.onkeydown = (event) => handleShortcuts('keydown', event);
    document.onkeyup = (event) => handleShortcuts('keyup', event);
  }, 1000);

  toggleButton.onclick = () => { toggleEditable(); return false; };
}

function handleShortcuts(type, event) {
  if (event.key == shortcutKey) {
    if (type === 'keydown') {
      showEditable();
    } else if (type === 'keyup') {
      hideEditable();
    }
  }
}

function toggleEditable() {
  if (body.classList.contains(showAllClass)) {
    hideEditable();
  } else {
    showEditable();
  }
}

function showEditable() {
  body.classList.add(showAllClass);
  Array.from(elements).forEach(el => {
    let content = el.innerHTML;
    let key = el.dataset.key;

    el.innerHTML = key;
    el.dataset.content = content;
  })
}

function hideEditable() {
  body.classList.remove(showAllClass);
  Array.from(elements).forEach(el => {
    el.innerHTML = el.dataset.content;
  })
}
