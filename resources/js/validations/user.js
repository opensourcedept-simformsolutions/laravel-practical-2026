$(function () {
    if (!$("#userForm").length) {
        return;
    }
    const isEdit = $('input[name="_method"]').val() === "PUT";

    $("#userForm").validate({
        ignore: [],

        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 100,
                pattern: /^[A-Za-z0-9\s.'-]+$/,
            },

            email: {
                required: true,
                email: true,
            },

            phone: {
                required: true,
                minlength: 7,
                maxlength: 20,
                pattern: /^[0-9+\-\s()]+$/,
            },

            role_id: {
                required: true,
            },

            society_id: {
                required: $("#society_id").length > 0,
            },

            password: {
                required: !isEdit,
                minlength: isEdit ? 0 : 1,
            },

            password_confirmation: {
                equalTo: "#password",
            },
        },

        messages: {
            name: {
                required: "Please enter name.",
                minlength: "Name must be at least 3 characters.",
                maxlength: "Name cannot exceed 100 characters.",
                pattern:
                    "Only letters, spaces, apostrophe, dot and hyphen are allowed.",
            },

            email: {
                required: "Please enter email.",
                email: "Please enter a valid email.",
            },

            phone: {
                required: "Please enter phone number.",
                minlength: "Phone must be at least 7 digits.",
                maxlength: "Phone cannot exceed 20 characters.",
                pattern: "Invalid phone number.",
            },

            role_id: {
                required: "Please select role.",
            },

            society_id: {
                required: "Please select society.",
            },

            password: {
                required: "Please enter password.",
                minlength: "Password must contain at least 8 characters.",
            },

            password_confirmation: {
                equalTo: "Passwords do not match.",
            },
        },

        errorElement: "div",

        errorClass: "invalid-feedback",

        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },

        unhighlight: function (element) {
            $(element).removeClass("is-invalid").addClass("is-valid");
        },

        errorPlacement: function (error, element) {
            if (element.hasClass("form-select")) {
                error.insertAfter(element);
            } else {
                error.insertAfter(element);
            }
        },
    });

});
