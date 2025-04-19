        window.onload = function () {
            google.accounts.id.initialize({
                client_id: '170029422952-vklbgsc1bt4g4b0od4m1bs26h8hp3a72.apps.googleusercontent.com', // Replace with your Client ID
                callback: handleCredentialResponse
            });

            // Render the Google Sign-In button
            google.accounts.id.renderButton(
                document.getElementById("googlelogin"), { theme: "outline", size: "large" }
            );

            // Optional: Display One Tap prompt
            google.accounts.id.prompt();
        }

function handleCredentialResponse(response) {
            //Decode the JWT token to extract user details
            const data = JSON.parse(atob(response.credential.split('.')[1]));
            console.log("User Details:");
            console.log("Name: " + data.name);
            console.log("Email: " + data.email);
            console.log("Picture: " + data.picture);
            var xhttp = new XMLHttpRequest();
            xhttp.open("POST", "login.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("googleloginresponse="+response.credential);
            xhttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
            try {
                
                const serverResponse = JSON.parse(this.responseText);
                console.log("Server Response:", serverResponse);

                if (serverResponse.status === "success") {
                    // Redirect to the URL provided by the server
                    window.location.href = serverResponse.redirect;
                } else if (serverResponse.status === "error") {
                    // Display error message (optional)
                    alert(serverResponse.message);
                    if (serverResponse.redirect) {
                        window.location.href = serverResponse.redirect;
                    }
                }
            } catch (e) {
                console.error("Failed to parse server response:", e);
            }
        }
    };
}

function openBarnatechLogin() {
    
}