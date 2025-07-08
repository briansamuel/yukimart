"use strict";
var KTSigninGeneral = (function() {
    var t, e, i;
    return {
        init: function() {
            (t = document.querySelector("#kt_sign_in_form")),
            (e = document.querySelector("#kt_sign_in_submit")),
            (i = FormValidation.formValidation(t, {
                fields: {
                    email: { validators: { notEmpty: { message: "Bắt buộc nhập email" }, emailAddress: { message: "Email không hợp lệ" } } },
                    password: { validators: { notEmpty: { message: "Bắt buộc nhập mật khẩu" } } },
                },
                plugins: { trigger: new FormValidation.plugins.Trigger(), bootstrap: new FormValidation.plugins.Bootstrap5({ rowSelector: ".fv-row" }) },
            })),
            e.addEventListener("click", function(n) {
                n.preventDefault();

                i.validate().then(function(i) {
                    "Valid" == i
                        ?
                        (e.setAttribute("data-kt-indicator", "on"),
                            (e.disabled = !0),
                            $.ajax({

                                url: "/login",
                                method: 'POST',
                                dataType: 'json',
                                data: {
                                    email: $("#email").val(),
                                    password: $("#password").val(),
                                    remember: $("#remember").is(':checked') ? 1 : 0,
                                },
                                success: function(data) {
                                    if (data.status === true) {

                                        window.location = admin_url + data.url;
                                    } else {
                                        setTimeout((function () {
                                            Swal.fire({
                                                text: data.msg,
                                                icon: "error",
                                                buttonsStyling: !1,
                                                confirmButtonText: "Xác nhận",
                                                customClass: { confirmButton: "btn btn-primary" },
                                            });

                                        }), 15);
                                        e.setAttribute("data-kt-indicator", "off"),
                                        (e.disabled=0);
                                    }

                                }, error: function (xhr, status, error) {
                                    let data = JSON.parse(xhr.responseText);
                                    c(),
                                    b.release();
                                    Swal.fire({
                                        text: data.error.message,
                                        icon: "error",
                                        buttonsStyling: !1,
                                        confirmButtonText: lang.ok_got_it,
                                        customClass: { confirmButton: "btn btn-primary" },
                                    });
                                    e.setAttribute("data-kt-indicator", "off"),
                                    (e.disabled=0);
                                },
                            })) :
                        Swal.fire({
                            text: "Sorry, looks like there are some errors detected, please try again.",
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: { confirmButton: "btn btn-primary" },
                        });
                });
            });
        },
    };
})();
KTUtil.onDOMContentLoaded(function() {
    KTSigninGeneral.init();
});
