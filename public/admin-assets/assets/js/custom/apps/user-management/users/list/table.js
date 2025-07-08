"use strict";
var KTUsersList = (function () {
    var e,
        t,
        n,
        r,
        o = document.getElementById("kt_table_users"),
        c = () => {
            o.querySelectorAll('[data-kt-users-table-filter="delete_row"]').forEach((t) => {
                t.addEventListener("click", function (t) {
                    t.preventDefault();
                    const n = t.target.closest("tr"),
                        r = n.querySelectorAll("td")[1].querySelectorAll("a")[1].innerText;
                    Swal.fire({
                        text: "Are you sure you want to delete " + r + "?",
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "No, cancel",
                        customClass: { confirmButton: "btn fw-bold btn-danger", cancelButton: "btn fw-bold btn-active-light-primary" },
                    }).then(function (t) {
                        t.value ?
                            Swal.fire({ text: "You have deleted " + r + "!.", icon: "success", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn fw-bold btn-primary" } })
                                .then(function () {
                                    e.row($(n)).remove().draw();
                                })
                                .then(function () {
                                    a();
                                }) :
                            "cancel" === t.dismiss && Swal.fire({ text: customerName + " was not deleted.", icon: "error", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn fw-bold btn-primary" } });
                    });
                });
            });
        },
        l = () => {
            const c = o.querySelectorAll('[type="checkbox"]');
            (t = document.querySelector('[data-kt-user-table-toolbar="base"]')), (n = document.querySelector('[data-kt-user-table-toolbar="selected"]')), (r = document.querySelector('[data-kt-user-table-select="selected_count"]'));
            const s = document.querySelector('[data-kt-user-table-select="delete_selected"]');
            c.forEach((e) => {
                e.addEventListener("click", function () {
                    setTimeout(function () {
                        a();
                    }, 50);
                });
            }),
                s.addEventListener("click", function () {
                    Swal.fire({
                        text: "Are you sure you want to delete selected customers?",
                        icon: "warning",
                        showCancelButton: !0,
                        buttonsStyling: !1,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "No, cancel",
                        customClass: { confirmButton: "btn fw-bold btn-danger", cancelButton: "btn fw-bold btn-active-light-primary" },
                    }).then(function (t) {
                        t.value ?
                            Swal.fire({ text: "You have deleted all selected customers!.", icon: "success", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn fw-bold btn-primary" } })
                                .then(function () {
                                    c.forEach((t) => {
                                        t.checked &&
                                            e
                                                .row($(t.closest("tbody tr")))
                                                .remove()
                                                .draw();
                                    });
                                    o.querySelectorAll('[type="checkbox"]')[0].checked = !1;
                                })
                                .then(function () {
                                    a(), l();
                                }) :
                            "cancel" === t.dismiss &&
                            Swal.fire({ text: "Selected customers was not deleted.", icon: "error", buttonsStyling: !1, confirmButtonText: "Ok, got it!", customClass: { confirmButton: "btn fw-bold btn-primary" } });
                    });
                });
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
    return {
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
                        responsive: true,
                        searchDelay: 500,
                        processing: true,
                        serverSide: true,
                        pageLength: 20,
                        lengthChange: !1,
                        stateSave: true,
                        ajax: {
                            url: admin_url + "user/ajax/get-list",
                        },
                        columns: [
                            { data: 'id' },
                            { data: 'username' },
                            { data: 'group_id' },
                            { data: 'last_visit' },
                            { data: 'status' },
                            { data: 'created_at' },
                            { data: null },
                        ],
                        columnDefs: [{
                            targets: 0,

                            orderable: false,
                            render: function (data) {
                                return `
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="${ data }" />
                                        </div>`;
                            }
                        },
                        {
                            className: "d-flex align-items-center",
                            targets: 1,
                            orderable: false,
                            render: function (data, type, row) {

                                return `<div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    <a href="${ row.page_detail }">
                                        <div class="symbol-label">
                                        <div class="symbol-label fs-3 bg-light-danger text-danger">${ row.full_name.charAt(0) }</div>
                                        </div>
                                    </a>
                                </div><div class="d-flex flex-column">
                                <a href="${ row.page_detail }" class="text-gray-800 text-hover-primary mb-1">${ row.full_name }</a>
                                <span>${ row.email }</span>
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
                                return `<div class="badge badge-light fw-bolder">${ row.last_ago }</div>`;

                            }
                        },
                        {

                            targets: 4,
                            orderable: false,
                            sortable: false,
                            render: function (data, type, row) {
                                var e = {
                                    'active': {
                                        title: "Active",
                                        class: "badge-light-success"
                                    },
                                    'inactive': {
                                        title: "InActive",
                                        class: " badge-light-warning"
                                    },
                                    'block': {
                                        title: "Banned",
                                        class: "badge-light-danger"
                                    },

                                };
                                if (row.status == 'active') {
                                    return `<div class="badge badge-light-success fw-bolder">${ row.status }</div>`;
                                } else {
                                    return `<div class="badge badge-light-danger fw-bolder">${ row.status }</div>`;
                                }


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
                                            <a href="${ row.page_edit }" class="menu-link px-3">Edit</a>
                                        </div>
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="${ row.page_delete }" class="menu-link px-3" data-kt-users-table-filter="delete_row">Delete</a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                    `;
                            },
                        },
                        ],
                        initComplete: function (settings, json) {

                            $('#kt_table_users_wrapper thead th').eq(1).removeClass('d-flex');
                        }
                    })).on("draw", function () {
                        l(), c(), a(), KTMenu.createInstances();;
                    }),
                    l(),
                    document.querySelector('[data-kt-user-table-filter="search"]').addEventListener("keyup", function (t) {
                        e.search(t.target.value).draw();
                    }),
                    document.querySelector('[data-kt-user-table-filter="reset"]').addEventListener("click", function () {
                        document
                            .querySelector('[data-kt-user-table-filter="form"]')
                            .querySelectorAll("select")
                            .forEach((e) => {
                                $(e).val("").trigger("change");
                            }),
                            e.search("").draw();
                    }),
                    c(),
                    (() => {
                        const t = document.querySelector('[data-kt-user-table-filter="form"]'),
                            n = t.querySelector('[data-kt-user-table-filter="filter"]'),
                            r = t.querySelectorAll("select");
                        n.addEventListener("click", function () {
                            var t = "";
                            r.forEach((e, n) => {
                                e.value && "" !== e.value && (0 !== n && (t += " "), (t += e.value));
                            }),
                                e.search(t).draw();
                        });
                    })());
        },
    };
})();
KTUtil.onDOMContentLoaded(function () {
    KTUsersList.init();
});