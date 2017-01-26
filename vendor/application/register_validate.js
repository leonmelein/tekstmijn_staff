// Wait for the DOM to be ready
$(function() {
    // Initialize form validation on the registration form.
    // It has the name attribute "registration"
    $("form[id='register']").validate({
        // Specify validation rules
        rules: {
            password: {
                required: true,
                minlength: 5
            },
            password_confirm: {
                equalTo: "#password"
            }
        },
        // Specify validation error messages
        messages: {
            password: {
                required: "Voer een wachtwoord in.",
                minlength: "Uw wachtwoord moet minstens vijf tekens lang zijn."
            },
            password_confirm: {
                required: "Herhaal je wachtwoord.",
                equalTo: "Uw wachtwoorden komen niet overeen."
            }

        },
        // Make sure the form is submitted to the destination defined
        // in the "action" attribute of the form when valid
        submitHandler: function(form) {
            form.submit();
        }
    });
});