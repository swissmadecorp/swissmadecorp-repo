<script type='text/javascript'>
    function verifyLoggedIn() {
        // if you want to display a message.
        var msgElem = document.getElementById("login-state-msg");

        var options = {
            scope: "profile postal_code payments:widget payments:shipping_address",
            popup: true,
            interactive: 'never'
        };

        // check if we are logged in
        authRequest = amazon.Login.authorize (options, function(response) {
            // this code is executed ASYNCHRONOUSLY

            if ( response.error ) {
                // USER NOT LOGGED IN
                console.log("verifyLoggedIn() - SESSION NOT ACTIVE - " + new Date());
                msgElem.innerHTML = "NOT logged in yet. Click 'Amazon Pay' or Go to Login screen.";
            } else {
                // USER IS LOGGED IN
                console.log("verifyLoggedIn() - SESSION ACTIVE - " + new Date());

                // optionally, get the profile info
                console.dir('access_token= ' + response.access_token);

                amazon.Login.retrieveProfile(function (response) {
                    // Display profile information.
                    console.dir('CustomerId= ' + response.profile.CustomerId);
                    console.dir('Name= ' + response.profile.Name);
                    console.dir('PostalCode= ' + response.profile.PostalCode);
                    console.dir('PrimaryEmail= ' + response.profile.PrimaryEmail);

                    msgElem.innerHTML = "Hello " + response.profile.Name + "! Just click Pay!";
                });
            }
        });
    }
</script>