"use strict";
var KTProjectList = (function () {
    var e,
        b,
        o = document.getElementById("kt_list_project"),
        k = document.getElementById("kt_pagination_project"),
        c = () => {
            console.log(k);
            k.querySelectorAll('[data-kt-pagination="page-link"]').forEach((t) => {
                console.log(t);
                t.addEventListener("click", function (t) {
                    t.preventDefault();
                    const n = t.target.href;
                    const s = new URL(n);
                    const p = s.searchParams.get('page');
                    b.block();
                    h(p);
                });
            });
        },
        h = (page) => {
            const n = window.location.search;
            const s = new URLSearchParams(n);
            const p = s.get('page');
            page = p ?? page;

            $.ajax({
                url: admin_url + "project",
                method: "POST",
                dataType: "json",
                data: {
                    page: page,
                    perpage: 9,
                },
                success: function (data) {
                    if (data.success === true) {
                        // window.location = admin_url + data.url;

                        $(o).html(data.content);
                        $(k).html(data.pagination);
                        c();
                    } else {
                        Swal.fire({
                            text: lang.sorry_looks_like,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: lang.ok_got_it,
                            customClass: {
                                confirmButton: "btn btn-primary",
                            },
                        });
                    }

                    b.release();
                },
                error: function (xhr, status, error) {
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
                },
            });
        };
    return {
        init: function () {
            !(function () {
                var t = document.getElementById("kt_project_list_chart");
                if (t) {
                    var e = t.getContext("2d");
                    new Chart(e, {
                        type: "doughnut",
                        data: {
                            datasets: [
                                {
                                    data: [30, 45, 25],
                                    backgroundColor: [
                                        "#00A3FF",
                                        "#50CD89",
                                        "#E4E6EF",
                                    ],
                                },
                            ],
                            labels: ["Active", "Completed", "Yet to start"],
                        },
                        options: {
                            chart: { fontFamily: "inherit" },
                            cutout: "75%",
                            cutoutPercentage: 65,
                            responsive: !0,
                            maintainAspectRatio: !1,
                            title: { display: !1 },
                            animation: { animateScale: !0, animateRotate: !0 },
                            tooltips: {
                                enabled: !0,
                                intersect: !1,
                                mode: "nearest",
                                bodySpacing: 5,
                                yPadding: 10,
                                xPadding: 10,
                                caretPadding: 0,
                                displayColors: !1,
                                backgroundColor: "#20D489",
                                titleFontColor: "#ffffff",
                                cornerRadius: 4,
                                footerSpacing: 0,
                                titleSpacing: 0,
                            },
                            plugins: { legend: { display: !1 } },
                        },
                    });
                }
            })();

            b = new KTBlockUI(o, {
                message:
                    '<div class="blockui-message"><span class="spinner-border text-primary"></span> Loading...</div>',
            });
            o,
            b.block(),
            h(1);
        },
    };
})();

KTUtil.onDOMContentLoaded(function () {
    KTProjectList.init();
});
