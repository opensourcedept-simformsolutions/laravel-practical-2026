$(function () {
    const $form = $("#deliveryForm");

    if (!$form.length) {
        return;
    }

    const hasStatus = $("#status").length !== 0;

    $form.validate({
        ignore: ":hidden:not(.select2-hidden-accessible)",

        rules: {
            resident_id: {
                required: true,
            },
            vendor: {
                required: true,
                maxlength: 255,
                pattern: /^[A-Za-z0-9\s.'&()-]+$/,
            },
            package_details: {
                required: true,
                minlength: 3,
                maxlength: 1000,
            },
            status: {
                required: hasStatus,
            },
        },

        messages: {
            resident_id: {
                required: "Please select a resident.",
            },
            vendor: {
                required: "Please enter vendor name.",
                maxlength: "Vendor name cannot exceed 255 characters.",
                pattern: "Vendor name contains invalid characters.",
            },
            package_details: {
                required: "Please enter package details.",
                minlength: "Package details must be at least 3 characters.",
                maxlength: "Package details cannot exceed 1000 characters.",
            },
            status: {
                required: "Please select delivery status.",
            },
        },

        errorElement: "div",
        errorClass: "invalid-feedback",

        highlight(element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },

        unhighlight(element) {
            $(element).removeClass("is-invalid").addClass("is-valid");
        },

        errorPlacement(error, element) {
            if (element.hasClass("select2-hidden-accessible")) {
                error.insertAfter(element.next(".select2"));
                return;
            }

            error.insertAfter(element);
        },
    });

    $("#resident_id, #status").on("change", function () {
        $(this).valid();
    });
});
