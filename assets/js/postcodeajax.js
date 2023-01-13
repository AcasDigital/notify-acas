const ajaxButton = document.getElementById("postcode-ajax");
const addressOptions = document.getElementById('postcode_list');

ajaxButton.onclick = (e) => {
    let postcode = document.getElementById('form_postcode');
    let postcodeValue = postcode.value
    postData(postcode.getAttribute('data-url'), {postcode: postcodeValue})
        .then(data => {
            if (data.list) {
                let addressResults = document.getElementById('address_results');
                let errorMsg = document.getElementById('form_postcode-error');
                if (errorMsg !== undefined && errorMsg !== null) {
                    postcode.parentElement.classList.remove('govuk-form-group--error');
                    errorMsg.innerHTML = "";
                }
                addressResults.style.display = "block";

                while (addressOptions.children.length > 1) {
                    addressOptions.removeChild(addressOptions.lastChild);
                }

                for (let i = 0; i < data.list.length; i++) {
                    var option = document.createElement("option");
                    option.text = data.list[i].Address;
                    option.value = data.list[i].Address;
                    addressOptions.appendChild(option);
                }
            }
        }).catch((e) => {


        if(document.getElementById("form_postcode-error")) {
            document.getElementById("form_postcode-error").remove();
        }
        postcode.parentElement.classList.add("govuk-form-group--error");

        const elem = document.createElement('p');

        elem.innerText = e;
        elem.id = "form_postcode-error";
        elem.className = "govuk-error-message";
        elem.setAttribute("role", "alert");
        elem.setAttribute("aria-live", "polite");
        postcode.parentNode.insertBefore(elem, postcode);

    });


};

addressOptions.onchange = function (e) {
    let value = addressOptions.options[addressOptions.selectedIndex].value
    if (value !== 0) {
        let splitArray = value.split(', ');

        document.getElementById('form_address_town').value = splitArray[splitArray.length - 3];
        document.getElementById('form_address_postcode').value = splitArray[splitArray.length - 1];
        document.getElementById('form_address_addressFirstLine').value = splitArray[0];
        splitArray.splice(splitArray.length - 3, 1);
        splitArray.splice(splitArray.length - 2, 1);
        splitArray.splice(splitArray.length - 1, 1);
        splitArray.splice(0, 1);
        let addressSecondLine = "";
        for (let i = 0; i < splitArray.length; i++) {
            if (addressSecondLine.length > 0) {
                addressSecondLine = addressSecondLine + ", ";
            }
            addressSecondLine = addressSecondLine + splitArray[i];
        }
        document.getElementById('form_address_addressSecondLine').value = addressSecondLine;
    }
};

async function postData(url = '', data = {}) {
    // Default options are marked with *
    const response = await fetch(url, {
        method: 'POST', // *GET, POST, PUT, DELETE, etc.
        body: JSON.stringify(data) // body data type must match "Content-Type" header
    })
    if (response.status !== 200) {
        let error = await response.json();
        throw Error(error.error);
    }

    return response.json(); // parses JSON response into native JavaScript objects
}