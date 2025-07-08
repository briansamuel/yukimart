<div class="row g-6 g-xl-9">
    <div class="col-lg-6 col-xxl-4">
        <!--begin::Card-->
        <div class="card h-100">
            <!--begin::Card body-->
            <div class="card-body p-9">
                <!--begin::Heading-->
                <div class="fs-2hx fw-bold">{{ $total_projects }}</div>
                <div class="fs-4 fw-semibold text-gray-400 mb-7">Current Projects</div>
                <!--end::Heading-->
                <!--begin::Wrapper-->
                <div class="d-flex flex-wrap">
                    <!--begin::Chart-->
                    <div class="d-flex flex-center h-100px w-100px me-9 mb-5">
                        <canvas id="kt_project_list_chart" width="100" height="100"
                            style="display: block; box-sizing: border-box; height: 100px; width: 100px;"></canvas>
                    </div>
                    <!--end::Chart-->
                    <!--begin::Labels-->
                    <div class="d-flex flex-column justify-content-center flex-row-fluid pe-11 mb-5">
                        <!--begin::Label-->
                        <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                            <div class="bullet bg-primary me-3"></div>
                            <div class="text-gray-400">Active</div>
                            <div class="ms-auto fw-bold text-gray-700">{{ $total_projects_active }}</div>
                        </div>
                        <!--end::Label-->
                        <!--begin::Label-->
                        <div class="d-flex fs-6 fw-semibold align-items-center mb-3">
                            <div class="bullet bg-success me-3"></div>
                            <div class="text-gray-400">Completed</div>
                            <div class="ms-auto fw-bold text-gray-700">{{ $total_projects_completed }}</div>
                        </div>
                        <!--end::Label-->
                        <!--begin::Label-->
                        <div class="d-flex fs-6 fw-semibold align-items-center">
                            <div class="bullet bg-gray-300 me-3"></div>
                            <div class="text-gray-400">Yet to start</div>
                            <div class="ms-auto fw-bold text-gray-700">{{ $total_projects_pending }}</div>
                        </div>
                        <!--end::Label-->
                    </div>
                    <!--end::Labels-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <div class="col-lg-6 col-xxl-4">
        <!--begin::Budget-->
        <div class="card h-100">
            <div class="card-body p-9">
                <div class="fs-2hx fw-bold">$3,290.00</div>
                <div class="fs-4 fw-semibold text-gray-400 mb-7">Project Finance</div>
                <div class="fs-6 d-flex justify-content-between mb-4">
                    <div class="fw-semibold">Avg. Project Budget</div>
                    <div class="d-flex fw-bold">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr007.svg-->
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-success">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M13.4 10L5.3 18.1C4.9 18.5 4.9 19.1 5.3 19.5C5.7 19.9 6.29999 19.9 6.69999 19.5L14.8 11.4L13.4 10Z"
                                    fill="currentColor"></path>
                                <path opacity="0.3" d="M19.8 16.3L8.5 5H18.8C19.4 5 19.8 5.4 19.8 6V16.3Z"
                                    fill="currentColor"></path>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->$6,570
                    </div>
                </div>
                <div class="separator separator-dashed"></div>
                <div class="fs-6 d-flex justify-content-between my-4">
                    <div class="fw-semibold">Lowest Project Check</div>
                    <div class="d-flex fw-bold">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr006.svg-->
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-danger">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M13.4 14.8L5.3 6.69999C4.9 6.29999 4.9 5.7 5.3 5.3C5.7 4.9 6.29999 4.9 6.69999 5.3L14.8 13.4L13.4 14.8Z"
                                    fill="currentColor"></path>
                                <path opacity="0.3" d="M19.8 8.5L8.5 19.8H18.8C19.4 19.8 19.8 19.4 19.8 18.8V8.5Z"
                                    fill="currentColor"></path>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->$408
                    </div>
                </div>
                <div class="separator separator-dashed"></div>
                <div class="fs-6 d-flex justify-content-between mt-4">
                    <div class="fw-semibold">Ambassador Page</div>
                    <div class="d-flex fw-bold">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr007.svg-->
                        <span class="svg-icon svg-icon-3 me-1 svg-icon-success">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M13.4 10L5.3 18.1C4.9 18.5 4.9 19.1 5.3 19.5C5.7 19.9 6.29999 19.9 6.69999 19.5L14.8 11.4L13.4 10Z"
                                    fill="currentColor"></path>
                                <path opacity="0.3" d="M19.8 16.3L8.5 5H18.8C19.4 5 19.8 5.4 19.8 6V16.3Z"
                                    fill="currentColor"></path>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->$920
                    </div>
                </div>
            </div>
        </div>
        <!--end::Budget-->
    </div>
    <div class="col-lg-6 col-xxl-4">
        <!--begin::Clients-->
        <div class="card h-100">
            <div class="card-body p-9">
                <!--begin::Heading-->
                <div class="fs-2hx fw-bold">49</div>
                <div class="fs-4 fw-semibold text-gray-400 mb-7">Our Clients</div>
                <!--end::Heading-->
                <!--begin::Users group-->
                <div class="symbol-group symbol-hover mb-9">
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip"
                        data-bs-original-title="Alan Warden" data-kt-initialized="1">
                        <span class="symbol-label bg-warning text-inverse-warning fw-bold">A</span>
                    </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Michael Eberon"
                        data-bs-original-title="Michael Eberon" data-kt-initialized="1">
                        <img alt="Pic" src="/metronic8/demo1/assets/media/avatars/300-11.jpg">
                    </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip"
                        aria-label="Michelle Swanston" data-bs-original-title="Michelle Swanston"
                        data-kt-initialized="1">
                        <img alt="Pic" src="/metronic8/demo1/assets/media/avatars/300-7.jpg">
                    </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip"
                        aria-label="Francis Mitcham" data-bs-original-title="Francis Mitcham"
                        data-kt-initialized="1">
                        <img alt="Pic" src="/metronic8/demo1/assets/media/avatars/300-20.jpg">
                    </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip"
                        data-bs-original-title="Susan Redwood" data-kt-initialized="1">
                        <span class="symbol-label bg-primary text-inverse-primary fw-bold">S</span>
                    </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Melody Macy"
                        data-bs-original-title="Melody Macy" data-kt-initialized="1">
                        <img alt="Pic" src="/metronic8/demo1/assets/media/avatars/300-2.jpg">
                    </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip"
                        data-bs-original-title="Perry Matthew" data-kt-initialized="1">
                        <span class="symbol-label bg-info text-inverse-info fw-bold">P</span>
                    </div>
                    <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Barry Walter"
                        data-bs-original-title="Barry Walter" data-kt-initialized="1">
                        <img alt="Pic" src="/metronic8/demo1/assets/media/avatars/300-12.jpg">
                    </div>
                    <a href="#" class="symbol symbol-35px symbol-circle" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_view_users">
                        <span class="symbol-label bg-dark text-gray-300 fs-8 fw-bold">+42</span>
                    </a>
                </div>
                <!--end::Users group-->
                <!--begin::Actions-->
                <div class="d-flex">
                    <a href="#" class="btn btn-primary btn-sm me-3" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_view_users">All Clients</a>
                    <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_users_search">Invite New</a>
                </div>
                <!--end::Actions-->
            </div>
        </div>
        <!--end::Clients-->
    </div>
</div>
