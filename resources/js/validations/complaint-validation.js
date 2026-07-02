$(function () {
    if (!$("#complaintForm").length) {
        return;
    }

    $("#complaintForm").validate({
        ignore: [],

        rules: {
            category: {
                required: $("#category").length > 0,
            },

            description: {
                required: $("#description").length > 0,
                minlength: 10,
                maxlength: 1000,
            },

            status: {
                required: $("#status").length > 0,
            },

            admin_notes: {
                maxlength: 1000,
            },
        },

        messages: {
            category: {
                required: "Please select a complaint category.",
            },

            description: {
                required: "Please enter complaint description.",
                minlength: "Description must be at least 10 characters.",
                maxlength: "Description cannot exceed 1000 characters.",
            },

            status: {
                required: "Please select complaint status.",
            },

            admin_notes: {
                maxlength: "Admin notes cannot exceed 1000 characters.",
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
            error.insertAfter(element);
        },
    });
});
