"use strict";
var KTUsersAddRole = (function() {
    const t = document.getElementById("kt_modal_add_role"),
        e = t.querySelector("#kt_modal_add_role_form"),
        n = new bootstrap.Modal(t);

    return {
        init: function() {
            (() => {
                var o = FormValidation.formValidation(e, {
                    fields: {
                        group_name: { validators: { notEmpty: { message: lang.role_name_is_required } } },
                        short_description: { validators: { notEmpty: { message: lang.short_description } } }
                    },
                    plugins: { trigger: new FormValidation.plugins.Trigger(), bootstrap: new FormValidation.plugins.Bootstrap5({ rowSelector: ".fv-row", eleInvalidClass: "", eleValidClass: "" }) },
                });
                t.querySelector('[data-kt-roles-modal-action="close"]').addEventListener("click", (t) => {
                        t.preventDefault(),
                            Swal.fire({
                                text: lang.are_you_sure_close,
                                icon: "warning",
                                showCancelButton: !0,
                                buttonsStyling: !1,
                                confirmButtonText: lang.yes_close_it,
                                cancelButtonText: lang.no_return,
                                customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-white" },
                            }).then(function(t) {
                                t.value && n.hide();
                            });
                    }),
                    t.querySelector('[data-kt-roles-modal-action="cancel"]').addEventListener("click", (t) => {
                        t.preventDefault(),
                            Swal.fire({
                                text: lang.are_you_sure_cancel,
                                icon: "warning",
                                showCancelButton: !0,
                                buttonsStyling: !1,
                                confirmButtonText: lang.yes_cancel_it,
                                cancelButtonText: lang.no_return,
                                customClass: { confirmButton: "btn btn-primary", cancelButton: "btn btn-active-light" },
                            }).then(function(t) {
                                t.value ?
                                    (e.reset(), n.hide()) :
                                    "cancel" === t.dismiss &&
                                    Swal.fire({ text: lang.your_form_cancelled, icon: "error", buttonsStyling: !1, confirmButtonText: lang.ok_got_it, customClass: { confirmButton: "btn btn-primary" } });
                            });
                    });
                const r = t.querySelector('[data-kt-roles-modal-action="submit"]');

                r.addEventListener("click", function(t) {
                    t.preventDefault(),
                        o &&
                        o.validate().then(function(t) {
                            console.log("validated!"),
                                "Valid" == t ?
                                (r.setAttribute("data-kt-indicator", "on"),
                                    (r.disabled = !0),
                                    $.ajax({
                                        url: admin_url + "user-group/add",
                                        method: 'POST',
                                        dataType: 'json',
                                        data: $('#kt_modal_add_role_form').serialize(),
                                        success: function(data) {
                                            if (data.success === true) {
                                                // window.location = admin_url + data.url;

                                                setTimeout(function() {
                                                    r.removeAttribute("data-kt-indicator"),
                                                        (r.disabled = !1),
                                                        Swal.fire({ text: lang.form_sucess_submit, icon: "success", buttonsStyling: !1, confirmButtonText: lang.ok_got_it, customClass: { confirmButton: "btn btn-primary" } }).then(
                                                            function(t) {
                                                                t.isConfirmed && n.hide();
                                                            }
                                                        );
                                                }, 15);
                                            } else {
                                                setTimeout((function() {
                                                    r.removeAttribute("data-kt-indicator"),
                                                        (r.disabled = !1),
                                                        Swal.fire({
                                                            text: lang.sorry_looks_like,
                                                            icon: "error",
                                                            buttonsStyling: !1,
                                                            confirmButtonText: lang.ok_got_it,
                                                            customClass: { confirmButton: "btn btn-primary" },
                                                        });
                                                }), 15)
                                            }
                                        },
                                        error: function(xhr, status, error) {

                                            let data = JSON.parse(xhr.responseText);
                                            console.log(data);
                                            setTimeout((function() {
                                                r.removeAttribute("data-kt-indicator"),
                                                    (r.disabled = !1),
                                                    Swal.fire({
                                                        text: data.error.message,
                                                        icon: "error",
                                                        buttonsStyling: !1,
                                                        confirmButtonText: lang.ok_got_it,
                                                        customClass: { confirmButton: "btn btn-primary" },
                                                    });
                                            }), 15)
                                        }
                                    })) :
                                Swal.fire({
                                    text: lang.sorry_looks_like,
                                    icon: "error",
                                    buttonsStyling: !1,
                                    confirmButtonText: lang.ok_got_it,
                                    customClass: { confirmButton: "btn btn-primary" },
                                });
                        });
                });
            })(),
            (() => {
                const t = e.querySelector("#kt_roles_select_all"),
                    n = e.querySelectorAll('[type="checkbox"]');
                t.addEventListener("change", (t) => {
                    n.forEach((e) => {
                        e.checked = t.target.checked;
                    });
                });
            })();
        },
    };
})();
KTUtil.onDOMContentLoaded(function() {
    KTUsersAddRole.init();
});