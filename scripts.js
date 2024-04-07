function showLogin() { // Function to show login
    document.getElementById("popup-container").style.display = "none";
    document.getElementById("loginCon").style.display = "block";
}

function changeUi() { // Function to toggle UI
    if (document.forms["form"]["auth"].value == "login") {
        document.getElementById("username").style.display = "block";
        document.getElementById("loc").style.display = "inline";
        document.forms["form"]["auth"].value = "reg";
        document.forms["form"]["sub"].innerHTML = "Register";
        document.querySelector("#login-reg").innerHTML = "Already have an account? Log In...";
    } else if (document.forms["form"]["auth"].value == "reg") {
        document.getElementById("username").style.display = "none";
        document.getElementById("loc").style.display = "none";
        document.forms["form"]["auth"].value = "login";
        document.forms["form"]["sub"].innerHTML = "Log In";
        document.querySelector("#login-reg").innerHTML = "Don't have an account? Register...";
    }
}