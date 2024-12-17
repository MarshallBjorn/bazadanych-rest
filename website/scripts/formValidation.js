function loginFormValidation() {
    let validate = true;
    let inputs = document.getElementsByClassName("form-inputs");
    let error = document.getElementById("error");
    let form = document.getElementById("login-form");

    error.innerHTML = "";
    
    for(let i=0; i<inputs.length; i++) {
        if(inputs[i].value == "") {
            inputs[i].style.borderColor = "red";
            error.innerHTML = "Pola nie mogą być puste";
            validate = false;
        }
        else {
            inputs[i].style.borderColor = "black";
        }
    }

    if(validate == true) {
        form.submit();
    }

}

function dishFormValidation() {
    
}