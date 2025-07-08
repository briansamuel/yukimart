"use strict";
var KTPostsAddCategory = (function () {
    const e = document.getElementById("kt_add_category_form");
    return {
        init: function () {
            (() => {

                var o = FormValidation.formValidation(e, {
                    fields: {
                        category_name: { validators: { notEmpty: { message: lang.category_name_required } } },
                        category_description: {
                            validators: {
                                callback: {
                                    message: lang.category_description_required,
                                    callback: function (value, validator, $field) {
                                        // Determine the numbers which are generated in captchaOperation
                                        var text = tinyMCE.get('category_description').getContent({
                                            format: 'text'
                                        });

                                        return text.length >= 5 && text.length <= 200;
                                    }
                                }
                            }
                        },


                    },
                    plugins: { trigger: new FormValidation.plugins.Trigger(), bootstrap: new FormValidation.plugins.Bootstrap5({ rowSelector: ".fv-row", eleInvalidClass: "", eleValidClass: "" }) },
                });


                const i = e.querySelector('[data-kt-category-action="submit"]');
                i.addEventListener("click", (t) => {
                    t.preventDefault(),
                        o &&
                        o.validate().then(function (t) {
                            console.log("validated!"),

                                "Valid" == t ?
                                    (i.setAttribute("data-kt-indicator", "on"),
                                        (i.disabled = !0),
                                        $.ajax({
                                            url: admin_url + "category/add",
                                            method: 'POST',
                                            dataType: 'json',
                                            data: $('#kt_add_category_form').serialize(),
                                            success: function (data) {
                                                i.disabled = !1, i.setAttribute("data-kt-indicator", "off");
                                                console.log(data);
                                                if(data.success === true) {
                                                    // window.location = admin_url + data.url;
                                                    KTPostList.reload();
                                                    i.removeAttribute("data-kt-indicator"),
                                                        (i.disabled = !1),
                                                        Swal.fire({ text: data.message ?? lang.form_sucess_submit, icon: "success", buttonsStyling: !1, confirmButtonText: lang.ok_got_it, customClass: { confirmButton: "btn btn-primary" } }).then(
                                                            function (t) {
                                                                t.isConfirmed;
                                                                if(data.redirect_url) {
                                                                    window.location.href = data.redirect_url;
                                                                }
                                                            }
                                                        );
                                                } else {
                                                    i.removeAttribute("data-kt-indicator"),
                                                        (i.disabled = !1),
                                                        Swal.fire({
                                                            text: lang.sorry_looks_like,
                                                            icon: "error",
                                                            buttonsStyling: !1,
                                                            confirmButtonText: lang.ok_got_it,
                                                            customClass: { confirmButton: "btn btn-primary" },
                                                        });
                                                }
                                                i.disabled = !1;
                                                i.removeAttribute("data-kt-indicator");
                                            },
                                            error: function (xhr, status, error) {

                                                let data = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                                                let message = data != null && data.error.message ? data.error.message : lang.sorry_looks_like;
                                                let errors = data != null && data.error.errors ? data.error.errors.join('<br>') : '';
                                                i.removeAttribute("data-kt-indicator"),
                                                    (i.disabled = !1),
                                                    Swal.fire({
                                                        titleText: message,
                                                        text: errors,
                                                        icon: "error",
                                                        buttonsStyling: !1,
                                                        confirmButtonText: lang.ok_got_it,
                                                        customClass: { confirmButton: "btn btn-primary" },
                                                    });
                                            }
                                        })) :
                                    Swal.fire({
                                        text: lang.sorry_looks_like,
                                        icon: "error",
                                        buttonsStyling: !1,
                                        confirmButtonText: lang.ok_got_it,
                                        customClass: { confirmButton: "btn btn-primary" },
                                    }).then(function (t) {
                                        t.isConfirmed;
                                        i.disabled = !1;
                                        i.setAttribute("data-kt-indicator", "off");
                                    }
                                    );
                        });
                });
            })();
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTPostsAddCategory.init();
});
