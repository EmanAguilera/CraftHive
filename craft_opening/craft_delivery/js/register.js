document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("signupForm"),
        continueBtn = form.querySelector("button[type='submit']"),
        errorText = form.querySelector(".error-txt");

    form.onsubmit = (e) => {
        e.preventDefault(); // Preventing form from submitting
    }

    continueBtn.onclick = () => {
        // Let's start Ajax
        let xhr = new XMLHttpRequest(); // Creating XML object
        xhr.open("POST", "php/register.php", true);
        xhr.onload = () => {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response;
                    if(data.trim() === "success"){
                        location.href = "login.php";
                    }else{
                        errorText.textContent = data;
                        errorText.style.display = "block";
                    }
                }
            }       
        }
        let formData = new FormData(form);
        xhr.send(formData); // Send form data
    }
});