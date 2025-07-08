"use strict";

var KTPostList = (function () {
    var e,
        t,
        n,
        r,
        o = document.getElementById("kt_table_category"),
        y = document.getElementById("kt_category_created_at_clear"),
        d = document.querySelector('[data-kt-category-table-filter="date_picker"]'),
        s = document.querySelector('[data-kt-category-table-filter="status"]'),
        k = document.querySelector('[data-kt-category-table-filter="search"]'),
        c = () => {
            o.querySelectorAll('[data-kt-category-table-filter="delete_row"]').forEach((t) => {
                t.addEventListener("click", function (t) {
                    t.preventDefault();
                    const n = t.target.closest("tr"),
                        r = n.querySelectorAll("td")[1].querySelectorAll("a")[0].innerText,
                        v = n.querySelectorAll("td")[0].querySelectorAll("input")[0].value;
                    Swal.fire({
                        text: lang.are_you_sure_delete + r + "?",
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: lang.yes_delete_it,
                        cancelButtonText: lang.no_cancel,
                        customClass: { confirmButton: "btn fw-bold btn-danger", cancelButton: "btn fw-bold btn-primary" },
                    }).then(function (t) {
                        t.value ?
                            $.ajax({
                                url: admin_url + "category/delete/"+ v,
                                method: 'GET',
                                dataType: 'json',

                                success: function (data) {

                                    if(data.success === true) {
                                        // window.location = admin_url + data.url;

                                        e.row($(n)).remove().draw();
                                        Swal.fire({ text: data.message ?? lang.form_sucess_submit, icon: "success", buttonsStyling: !1, confirmButtonText: lang.ok_got_it, customClass: { confirmButton: "btn btn-primary" } }).then(
                                            function (t) {
                                                t.isConfirmed;
                                            }
                                        );
                                    } else {

                                        Swal.fire({
                                            text: data.message ?? lang.sorry_looks_like,
                                            icon: "error",
                                            buttonsStyling: !1,
                                            confirmButtonText: lang.ok_got_it,
                                            customClass: { confirmButton: "btn btn-primary" },
                                        });
                                    }
                                },
                                error: function (xhr, status, error) {

                                    let data = JSON.parse(xhr.responseText);

                                    Swal.fire({
                                        text: data.error.message,
                                        icon: "error",
                                        buttonsStyling: !1,
                                        confirmButtonText: lang.ok_got_it,
                                        customClass: { confirmButton: "btn btn-primary" },
                                    });
                                }
                            }).then(function () {
                                    a();
                                }) :
                            "cancel" === t.dismiss && Swal.fire({ text: r + lang.was_not_deleted, icon: "error", buttonsStyling: !1, confirmButtonText: lang.ok_got_it, customClass: { confirmButton: "btn fw-bold btn-primary" } });
                    });
                });
            });
        },
        l = () => {
            const c = o.querySelectorAll('[type="checkbox"]');

            (t = document.querySelector('[data-kt-category-table-toolbar="base"]')), (n = document.querySelector('[data-kt-category-table-toolbar="selected"]')), (r = document.querySelector('[data-kt-category-table-select="selected_count"]'));
            const s = document.querySelector('[data-kt-category-table-select="delete_selected"]');
            c.forEach((e) => {
                e.addEventListener("click", function () {
                    setTimeout(function () {
                        a();
                    }, 50);
                });
            }),

                s.addEventListener("click", function () {

                    var list_checked_id = [];

                    const c = o.querySelectorAll('tbody [type="checkbox"]');
                    c.forEach((e) => {
                        if(e.checked) {
                            list_checked_id.push(e.value);
                        }
                    });
                    if(list_checked_id.length === 0) return false;
                    Swal.fire({
                        text: lang.are_you_sure_detele_selected_category,
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: lang.yes_delete_it,
                        cancelButtonText: lang.no_cancel,
                        customClass: { confirmButton: "btn fw-bold btn-danger", cancelButton: "btn fw-bold btn-primary" },
                    }).then(function (t) {
                        t.value ?

                            $.ajax({
                                url: admin_url + "category/delete",
                                method: 'POST',
                                dataType: 'json',
                                data: {
                                    ids: list_checked_id,
                                    total: list_checked_id.length,
                                },
                                success: function (data) {

                                    if(data.success === true) {
                                        // window.location = admin_url + data.url;

                                        e.draw();
                                        Swal.fire({ text: data.message ?? lang.form_sucess_submit, icon: "success", buttonsStyling: !1, confirmButtonText: lang.ok_got_it, customClass: { confirmButton: "btn btn-primary" } }).then(
                                            function (t) {
                                                t.isConfirmed;
                                            }
                                        );
                                    } else {

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

                                    let data = JSON.parse(xhr.responseText);

                                    Swal.fire({
                                        text: data.error.message,
                                        icon: "error",
                                        buttonsStyling: !1,
                                        confirmButtonText: lang.ok_got_it,
                                        customClass: { confirmButton: "btn btn-primary" },
                                    });
                                }
                            }) :
                            "cancel" === t.dismiss &&
                            Swal.fire({ text: selected_category_deleted, icon: "error", buttonsStyling: !1, confirmButtonText: lang.ok_got_it, customClass: { confirmButton: "btn fw-bold btn-primary" } });
                    });
                });
        }, w = () => {

            e.search(k.value);
            e.column(4).search(s.value);
            let date = [];
            let selectedDate = d._flatpickr.selectedDates;
            selectedDate.forEach(function (e, index) {
                console.log(e);
                let currentDateObj = new Date(e);
                date.push(currentDateObj.getFullYear() + '/' + currentDateObj.getMonth() + '/' + currentDateObj.getDate());
            });

            e.column(5).search(date);
            e.draw();
        };
    const a = () => {
        const e = o.querySelectorAll('tbody [type="checkbox"]');
        let c = !1,
            l = 0;
        e.forEach((e) => {

            e.checked && ((c = !0), l++);
        }),

            c ? ((r.innerHTML = l), t.classList.add("d-none"), n.classList.remove("d-none")) : (t.classList.remove("d-none"), n.classList.add("d-none"));
    };
    const categoryCreatedAt = $("#kt_category_created_at").flatpickr({

        altInput: true,
        dateFormat: "Y-m-d",
        mode: "range",

    });
    return {
        reload:  function () {
            e.draw();
        },
        init: function () {
            o &&
                (o.querySelectorAll("tbody tr").forEach((e) => {
                    const t = e.querySelectorAll("td"),
                        n = t[3].innerText.toLowerCase();
                    let r = 0,
                        o = "minutes";
                    n.includes("yesterday") ?
                        ((r = 1), (o = "days")) :
                        n.includes("mins") ?
                            ((r = parseInt(n.replace(/\D/g, ""))), (o = "minutes")) :
                            n.includes("hours") ?
                                ((r = parseInt(n.replace(/\D/g, ""))), (o = "hours")) :
                                n.includes("days") ?
                                    ((r = parseInt(n.replace(/\D/g, ""))), (o = "days")) :
                                    n.includes("weeks") && ((r = parseInt(n.replace(/\D/g, ""))), (o = "weeks"));
                    const c = moment().subtract(r, o).format();
                    t[3].setAttribute("data-order", c);
                    const l = moment(t[5].innerHTML, "DD MMM YYYY, LT").format();
                    t[5].setAttribute("data-order", l);
                }),
                    (e = $(o).DataTable({
                        searchDelay: 500,
                        processing: true,
                        serverSide: true,
                        pageLength: 20,
                        order: [[5, "asc"]],
                        lengthChange: !1,
                        stateSave: true,
                        select: {
                            style: 'multi',
                            selector: 'td:first-child input[type="checkbox"]',
                            className: 'row-selected'
                        },
                        ajax: {
                            url: admin_url + "category",
                            method: "post",
                            data: {
                                category_type: 'news',
                            }

                        },
                        columns: [
                            { data: 'id' },
                            { data: 'category_name' },
                            { data: 'category_slug' },
                            { data: 'posts' },
                            { data: 'category_status' },
                            { data: 'last_ago' },
                            { data: null },
                        ],
                        columnDefs: [
                            {
                                targets: 0,
                                orderable: false,
                                visible: true,
                                render: function (data) {
                                    return `
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="${data}" />
                                        </div>`;
                                }
                            },
                            {

                                targets: 1,
                                orderable: false,
                                visible: true,
                                render: function (data, type, row) {

                                    return `<div class="">
                                <a target="_blank" href="${row.category_edit}" class="text-gray-800 text-hover-primary mb-1">${row.category_name}</a>

                            </div>`;

                                }
                            },
                            {

                                targets: 2,
                                orderable: false,
                                render: function (data, type, row) {
                                    return data;

                                }
                            },
                            {

                                targets: 3,
                                orderable: false,
                                render: function (data, type, row) {
                                    return `<div class="badge badge-light-success fw-bolder">${row.posts.length}</div>`;

                                }
                            },
                            {

                                targets: 4,
                                orderable: false,
                                sortable: false,
                                render: function (data, type, row) {
                                    var e = {
                                        'publish': {
                                            title: "Publish",
                                            class: "badge-success"
                                        },
                                        'pending': {
                                            title: "Pending",
                                            class: " badge-light-warning"
                                        },
                                        'draft': {
                                            title: "Draft",
                                            class: " badge-light-info"
                                        },
                                        'trash': {
                                            title: "Trash",
                                            class: "badge-light-danger"
                                        },

                                    };

                                    return `<div class="badge ${e[row.category_status].class} fw-bolder">${e[row.category_status].title}</div>`;


                                }
                            },
                            {
                                targets: 6,
                                orderable: false,
                                className: 'text-end',
                                render: function (data, type, row) {
                                    return `
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr072.svg-->
                                    <span class="svg-icon svg-icon-5 m-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="black"></path>
                                        </svg>
                                    </span>
                                    <!--end::Svg Icon--></a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a target="_blank" href="${row.category_edit}" class="menu-link px-3">${lang.edit}</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-kt-category-table-filter="delete_row">${lang.delete}</a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                    `;
                                },
                            },
                        ],
                        initComplete: function (settings, json) {

                            $('#kt_table_category_wrapper thead th').eq(1).removeClass('d-flex');
                            flatpickr.localize(flatpickr.l10ns.vi);
                            // $("#kt_category_created_at").flatpickr({

                            //     altInput: true,
                            //     dateFormat: "Y-m-d",
                            //     mode: "range",

                            // });

                        }
                    })).on("draw", function () {
                        l(), c(), a(), KTMenu.createInstances();;
                    }),
                    l(),
                    k.addEventListener("keyup", function (t) {
                        w();
                    }),

                    d.addEventListener("change", function (t) {
                        let selectedDate = d._flatpickr.selectedDates;
                        if(selectedDate.length == 2) {
                            w();
                        }

                    }),
                    y.addEventListener("click", function (t) {
                        categoryCreatedAt.clear();
                        w();

                    }),
                    $('#kt_post_status').on("change", function (t) {

                        w();
                    }),
                    c());
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTPostList.init();
});
