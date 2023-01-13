/**
 * Adds character counting to text areas using the maxlength attribute on the text area.
 */
const infoText = document.querySelector('.character-count-area-js');
let counter = document.querySelector('.info-text__character-count-js');
const maxLength = parseInt(infoText.getAttribute('maxlength'));
counter.textContent = parseInt(maxLength);

infoText.addEventListener('input', event => {
    updateCharacterCount(event);
} );

function updateCharacterCount(event) {
    const target = event.currentTarget;

    const currentLength = parseInt(target.value.length);

    if (currentLength > maxLength) {
        target.textContent = target.substring(0, maxLength);
    }

    counter.textContent = maxLength - currentLength;
}
