import { initAll } from 'govuk-frontend'
initAll()

document.addEventListener(
  "DOMContentLoaded", () => {

    const ADMIN_TEXT_AREAS = document.getElementsByTagName('textarea')


    Array.from(ADMIN_TEXT_AREAS).forEach(item => {
      if (item.value.length > 0) {
        item.style.minHeight = item.scrollHeight + 'px'
      }
    });
  }
);
