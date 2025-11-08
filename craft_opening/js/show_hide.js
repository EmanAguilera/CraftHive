function updateFileName() {
    var input = document.getElementById('img');
    var label = document.getElementById('fileLabel');

    if (input.files.length > 0) {
        var fileName = input.files[0].name;
        label.innerHTML = fileName; // Display the file name in the label
    } else {
        label.innerHTML = "Select Upload Pic"; // Reset label if no file is selected
    }
}


// JavaScript to toggle password visibility
const pswrdField = document.querySelector(".password-container input[name='password']");
const toggleBtn = document.querySelector(".password-toggle");

toggleBtn.onclick = () => {
    if (pswrdField.type === "password") {
        pswrdField.type = "text";
        toggleBtn.classList.remove("fa-eye-slash");
        toggleBtn.classList.add("fa-eye");
    } else {
        pswrdField.type = "password";
        toggleBtn.classList.remove("fa-eye");
        toggleBtn.classList.add("fa-eye-slash");
    }
};

// Ensure the DOM is fully loaded before attaching event listeners
document.addEventListener("DOMContentLoaded", function() {
    var input = document.getElementById('img');
    input.addEventListener('change', updateFileName);
});