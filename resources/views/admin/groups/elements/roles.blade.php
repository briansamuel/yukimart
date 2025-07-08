<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
    @foreach ($roles as $key => $role)
        <!--begin::Col-->
        <div class="col-md-4">
            <!--begin::Card-->
            <div class="card card-flush h-md-100">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>{{ $role->group_name }}</h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-1">
                    <!--begin::Users-->
                    <div class="fw-bolder text-gray-600 mb-5">Total users with this role: 5</div>
                    <!--end::Users-->
                    <!--begin::Permissions-->
                    <div class="d-flex flex-column text-gray-600">
                        @if ($role->permissionArray)
                            @foreach ($role->permissionArray as $key => $perm)
                                @if ($key >= 5)
                                @break
                            @endif
                            <div class="d-flex align-items-center py-2">
                                <span class="bullet bg-primary me-3"></span> {{ $perm }}
                            </div>
                        @endforeach

                        @if (count($role->permissionArray) > 5)
                            <div class="d-flex align-items-center py-2">
                                <span class="bullet bg-primary me-3"></span>
                                <em>and {{ count($role->permissionArray) - 5 }} more...</em>
                            </div>
                        @endif
                    @endif
                </div>
                <!--end::Permissions-->
            </div>
            <!--end::Card body-->
            <!--begin::Card footer-->
            <div class="card-footer flex-wrap pt-0">
                <a href="{{ route('user_group.detail', ['id' => $role->id]) }}"
                    class="btn btn-light btn-active-primary my-1 me-2">{{ __('admin.roles.view_role') }}</a>
                <a href="{{ route('user_group.edit', ['id' => $role->id]) }}"
                    class="btn btn-light btn-active-primary my-1 me-2">{{ __('admin.roles.edit_role') }}</a>
            </div>
            <!--end::Card footer-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
@endforeach

<!--begin::Add new card-->
<div class="ol-md-4">
    <!--begin::Card-->
    <div class="card h-md-100">
        <!--begin::Card body-->
        <div class="card-body d-flex flex-center">
            <!--begin::Button-->
            <button type="button" class="btn btn-clear d-flex flex-column flex-center" data-bs-toggle="modal"
                data-bs-target="#kt_modal_add_role">
                <!--begin::Illustration-->
                <img src="{{  asset('admin-assets/assets/media/illustrations/sketchy-1/4.png') }}" alt="" class="mw-100 mh-150px mb-7">
                <!--end::Illustration-->
                <!--begin::Label-->
                <div class="fw-bolder fs-3 text-gray-600 text-hover-primary">Add New Role</div>
                <!--end::Label-->
            </button>
            <!--begin::Button-->
        </div>
        <!--begin::Card body-->
    </div>
    <!--begin::Card-->
</div>
<!--begin::Add new card-->
</div>
