"use strict";
var KTPostsAddPost = (function () {
    const e = document.getElementById("kt_add_news_form");


    $("#kt_news_created_at").flatpickr({
        enableTime: true,
        altInput: true,
        altFormat: "d/m/Y H:i:s",
        dateFormat: "Y-m-d H:i:s",

        defaultDate: `today`,
    });
    return {
        init: function () {
            (() => {

                var o = FormValidation.formValidation(e, {
                    fields: {
                        post_title: { validators: { notEmpty: { message: lang.page_title_required } } },
                        post_description: {
                            validators: {
                                callback: {
                                    message: lang.page_description_required,
                                    callback: function (value, validator, $field) {
                                        // Determine the numbers which are generated in captchaOperation
                                        var text = tinyMCE.get('post_description').getContent({
                                            format: 'text'
                                        });

                                        return text.length >= 5 && text.length <= 200;
                                    }
                                }
                            }
                        },
                        post_content: {
                            validators: {
                                callback: {
                                    message: lang.page_content_required,
                                    callback: function (value, validator, $field) {
                                        // Determine the numbers which are generated in captchaOperation
                                        var text = tinyMCE.get('post_content').getContent({
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


                const i = e.querySelector('[data-kt-news-action="submit"]');
                i.addEventListener("click", (t) => {
                    t.preventDefault(),
                        o &&
                        o.validate().then(function (t) {
                            console.log("validated!"),

                                "Valid" == t ?
                                    (i.setAttribute("data-kt-indicator", "on"),
                                        (i.disabled = !0),
                                        $.ajax({
                                            url: admin_url + "news/add",
                                            method: 'POST',
                                            dataType: 'json',
                                            data: $('#kt_add_news_form').serialize(),
                                            success: function (data) {
                                                i.disabled = !1, i.setAttribute("data-kt-indicator", "off");
                                                console.log(data);
                                                if(data.success === true) {
                                                    // window.location = admin_url + data.url;

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
                                            },
                                            error: function (xhr, status, error) {

                                                let data = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                                                let message = data != null && data.error.message ? data.error.message : lang.sorry_looks_like;
                                                i.removeAttribute("data-kt-indicator"),
                                                    (i.disabled = !1),
                                                    Swal.fire({
                                                        text: message,
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
    KTPostsAddPost.init();
});
