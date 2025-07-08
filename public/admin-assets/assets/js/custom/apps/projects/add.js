"use strict";
var KTCreateApp = (function () {
    var e,
        t,
        o,
        r,
        a,
        i,
        k,
        q,
        z,
        n = [];
    return {
        init: function () {
            (e = document.querySelector("#kt_modal_create_app")) &&
                (new bootstrap.Modal(e),
                (t = document.querySelector("#kt_modal_create_app_stepper")),
                (o = document.querySelector("#kt_modal_create_app_form")),
                (r = t.querySelector('[data-kt-stepper-action="submit"]')),
                (a = t.querySelector('[data-kt-stepper-action="next"]')),
                (k = t.querySelector('[data-kt-stepper-action="budget_label"]')),
                (q = t.querySelector('[data-kt-stepper-action="budget_slider"]')),
                (z = t.querySelector('[data-kt-stepper-action="budget_value"]')),
                (i = new KTStepper(t)).on("kt.stepper.changed", function (e) {
                    5 === i.getCurrentStepIndex()
                        ? (r.classList.remove("d-none"),
                          r.classList.add("d-inline-block"),
                          a.classList.add("d-none"))
                        : 6 === i.getCurrentStepIndex()
                        ? (r.classList.add("d-none"), a.classList.add("d-none"))
                        : (r.classList.remove("d-inline-block"),
                          r.classList.remove("d-none"),
                          a.classList.remove("d-none"));
                }),
                i.on("kt.stepper.next", function (e) {
                    console.log("stepper.next");
                    var t = n[e.getCurrentStepIndex() - 1];
                    t
                        ? t.validate().then(function (t) {
                              console.log("validated!"),
                                  "Valid" == t
                                      ? e.goNext()
                                      : Swal.fire({
                                            text: "Sorry, looks like there are some errors detected, please try again.",
                                            icon: "error",
                                            buttonsStyling: !1,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-light",
                                            },
                                        }).then(function () {});
                          })
                        : (e.goNext(), KTUtil.scrollTop());
                }),
                noUiSlider.create(q, {
                    start: [5],
                    connect: true,
                    step: 500,
                    range: {

                        "min": 500,
                        "max": 100000
                    }
                }),
                q.noUiSlider.on("update", function (values, handle) {
                    k.innerHTML = Math.round(values[handle]);
                    z.value = Math.round(values[handle]);
                    if (handle) {
                        k.innerHTML = Math.round(values[handle]);
                        z.value = Math.round(values[handle]);
                    }
                }),
                $('#project_due_date').flatpickr(),
                i.on("kt.stepper.previous", function (e) {
                    console.log("stepper.previous"),
                        e.goPrevious(),
                        KTUtil.scrollTop();
                }),

                r.addEventListener("click", function (e) {
                    n[3].validate().then(function (t) {
                        console.log("validated!"),
                            "Valid" == t
                                ? (e.preventDefault(),
                                  (r.disabled = !0),
                                  r.setAttribute("data-kt-indicator", "on"),
                                  $.ajax({
                                      url: admin_url + "project/add",
                                      method: "POST",
                                      dataType: "json",
                                      data: $(
                                          "#kt_modal_create_app_form"
                                      ).serialize(),
                                      success: function (data) {
                                          r.removeAttribute(
                                              "data-kt-indicator"
                                          ),
                                              (r.disabled = !1),
                                              i.goNext();
                                          if (data.success === true) {
                                              // window.location = admin_url + data.url;
                                            //   KTPostList.reload();

                                                  Swal.fire({
                                                      text:
                                                          data.message ??
                                                          lang.form_sucess_submit,
                                                      icon: "success",
                                                      buttonsStyling: !1,
                                                      confirmButtonText:
                                                          lang.ok_got_it,
                                                      customClass: {
                                                          confirmButton:
                                                              "btn btn-primary",
                                                      },
                                                  }).then(function (t) {
                                                      t.isConfirmed;
                                                      if (data.redirect_url) {
                                                          window.location.href =
                                                              data.redirect_url;
                                                      }
                                                  });
                                          } else {

                                                  Swal.fire({
                                                      text: lang.sorry_looks_like,
                                                      icon: "error",
                                                      buttonsStyling: !1,
                                                      confirmButtonText:
                                                          lang.ok_got_it,
                                                      customClass: {
                                                          confirmButton:
                                                              "btn btn-primary",
                                                      },
                                                  });
                                          }
                                          r.removeAttribute(
                                            "data-kt-indicator"
                                        ),
                                        (r.disabled = !1);
                                      },
                                      error: function (xhr, status, error) {
                                          let data = xhr.responseText
                                              ? JSON.parse(xhr.responseText)
                                              : null;
                                          let message =
                                              data != null && data.error.message
                                                  ? data.error.message
                                                  : lang.sorry_looks_like;
                                          let errors =
                                              data != null && data.error.errors
                                                  ? data.error.errors.join(
                                                        "<br>"
                                                    )
                                                  : "";
                                          r.removeAttribute(
                                              "data-kt-indicator"
                                          ),
                                              (r.disabled = !1);
                                          Swal.fire({
                                              titleText: message,
                                              html: errors,
                                              icon: "error",
                                              buttonsStyling: !1,
                                              confirmButtonText: lang.ok_got_it,
                                              customClass: {
                                                  confirmButton:
                                                      "btn btn-primary",
                                              },
                                          });
                                      },
                                  }))
                                : Swal.fire({
                                      text: "Sorry, looks like there are some errors detected, please try again.",
                                      icon: "error",
                                      buttonsStyling: !1,
                                      confirmButtonText: "Ok, got it!",
                                      customClass: {
                                          confirmButton: "btn btn-light",
                                      },
                                  }).then(function () {
                                      KTUtil.scrollTop();
                                  });
                    });
                }),
                $(o.querySelector('[name="card_expiry_month"]')).on(
                    "change",
                    function () {
                        n[4].revalidateField("card_expiry_month");
                    }
                ),
                $(o.querySelector('[name="card_expiry_year"]')).on(
                    "change",
                    function () {
                        n[4].revalidateField("card_expiry_year");
                    }
                ),
                n.push(
                    FormValidation.formValidation(o, {
                        fields: {
                            project_name: {
                                validators: {
                                    notEmpty: {
                                        message: lang.project_name_is_required,
                                    },
                                },
                            },
                            project_category: {
                                validators: {
                                    notEmpty: {
                                        message: lang.project_category_is_required,
                                    },
                                },
                            },
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: ".fv-row",
                                eleInvalidClass: "",
                                eleValidClass: "",
                            }),
                        },
                    })
                ),
                n.push(
                    FormValidation.formValidation(o, {
                        fields: {
                            project_description: {
                                validators: {
                                    notEmpty: {
                                        message: lang.project_description_is_required,
                                    },
                                },
                            },
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: ".fv-row",
                                eleInvalidClass: "",
                                eleValidClass: "",
                            }),
                        },
                    })
                ),
                n.push(
                    FormValidation.formValidation(o, {
                        fields: {
                            framework: {
                                validators: {
                                    notEmpty: {
                                        message: "Framework is required",
                                    },
                                },
                            },
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: ".fv-row",
                                eleInvalidClass: "",
                                eleValidClass: "",
                            }),
                        },
                    })
                ),
                n.push(
                    FormValidation.formValidation(o, {
                        fields: {
                            dbname: {
                                validators: {
                                    notEmpty: {
                                        message: "Database name is required",
                                    },
                                },
                            },
                            dbengine: {
                                validators: {
                                    notEmpty: {
                                        message: "Database engine is required",
                                    },
                                },
                            },
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: ".fv-row",
                                eleInvalidClass: "",
                                eleValidClass: "",
                            }),
                        },
                    })
                ),
                n.push(
                    FormValidation.formValidation(o, {
                        fields: {
                            card_name: {
                                validators: {
                                    notEmpty: {
                                        message: "Name on card is required",
                                    },
                                },
                            },
                            card_number: {
                                validators: {
                                    notEmpty: {
                                        message: "Card member is required",
                                    },
                                    creditCard: {
                                        message: "Card number is not valid",
                                    },
                                },
                            },
                            card_expiry_month: {
                                validators: {
                                    notEmpty: { message: "Month is required" },
                                },
                            },
                            card_expiry_year: {
                                validators: {
                                    notEmpty: { message: "Year is required" },
                                },
                            },
                            card_cvv: {
                                validators: {
                                    notEmpty: { message: "CVV is required" },
                                    digits: {
                                        message: "CVV must contain only digits",
                                    },
                                    stringLength: {
                                        min: 3,
                                        max: 4,
                                        message:
                                            "CVV must contain 3 to 4 digits only",
                                    },
                                },
                            },
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: ".fv-row",
                                eleInvalidClass: "",
                                eleValidClass: "",
                            }),
                        },
                    })
                ));
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTCreateApp.init();
});
