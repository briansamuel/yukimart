@extends('admin.index')
@section('page-header', __('users.edit_user'))
@section('page-sub_header', $user->full_name)

@section('content')
<!--begin::Form-->
<form id="kt_user_form" class="form d-flex flex-column flex-lg-row fv-plugins-bootstrap5 fv-plugins-framework" action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <!--begin::Aside column-->
    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
        <!--begin::Avatar-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('users.avatar') }}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body text-center pt-0">
                <!--begin::Image input-->
                <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3" data-kt-image-input="true">
                    <!--begin::Preview existing avatar-->
                    <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ $user->avatar ?? asset('admin-assets/assets/media/svg/files/blank-image.svg') }}')"></div>
                    <!--end::Preview existing avatar-->
                    <!--begin::Label-->
                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('users.tooltips.change_avatar') }}">
                        <i class="ki-duotone ki-pencil fs-7">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <!--begin::Inputs-->
                        <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                        <input type="hidden" name="avatar_remove" />
                        <!--end::Inputs-->
                    </label>
                    <!--end::Label-->
                    <!--begin::Cancel-->
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('common.cancel_avatar') }}">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <!--end::Cancel-->
                    <!--begin::Remove-->
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('common.remove_avatar') }}">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <!--end::Remove-->
                </div>
                <!--end::Image input-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('users.help.avatar') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Avatar-->
        <!--begin::Status-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('users.status') }}</h2>
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="rounded-circle {{ $user->status == 'active' ? 'bg-success' : 'bg-danger' }} w-15px h-15px" id="kt_user_status"></div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Select2-->
                <select class="form-select mb-2" data-control="select2" data-hide-search="true" data-placeholder="{{ __('users.select_status') }}" id="kt_user_status_select" name="status">
                    <option></option>
                    <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>{{ __('users.active') }}</option>
                    <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>{{ __('users.inactive') }}</option>
                    <option value="blocked" {{ $user->status == 'blocked' ? 'selected' : '' }}>{{ __('users.blocked') }}</option>
                </select>
                <!--end::Select2-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('users.help.status') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status-->
        <!--begin::Statistics-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('users.statistics') }}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Stats-->
                <div class="d-flex flex-wrap">
                    <!--begin::Stat-->
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <!--begin::Number-->
                        <div class="d-flex align-items-center">
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $user->branchShops->count() }}">{{ $user->branchShops->count() }}</div>
                        </div>
                        <!--end::Number-->
                        <!--begin::Label-->
                        <div class="fw-semibold fs-6 text-gray-400">{{ __('branch_shops.title') }}</div>
                        <!--end::Label-->
                    </div>
                    <!--end::Stat-->
                    <!--begin::Stat-->
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <!--begin::Number-->
                        <div class="d-flex align-items-center">
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $user->roles->count() }}">{{ $user->roles->count() }}</div>
                        </div>
                        <!--end::Number-->
                        <!--begin::Label-->
                        <div class="fw-semibold fs-6 text-gray-400">{{ __('roles.title') }}</div>
                        <!--end::Label-->
                    </div>
                    <!--end::Stat-->
                </div>
                <!--end::Stats-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Statistics-->
    </div>
    <!--end::Aside column-->
    <!--begin::Main column-->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
        <!--begin::General options-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ __('users.general_info') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="row">
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row fv-plugins-icon-container">
                        <!--begin::Label-->
                        <label class="required form-label">{{ __('users.username') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="username" class="form-control mb-2" placeholder="{{ __('users.enter_username') }}" value="{{ old('username', $user->username) }}" />
                        <!--end::Input-->
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row fv-plugins-icon-container">
                        <!--begin::Label-->
                        <label class="required form-label">{{ __('users.email') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="email" name="email" class="form-control mb-2" placeholder="{{ __('users.enter_email') }}" value="{{ old('email', $user->email) }}" />
                        <!--end::Input-->
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="row">
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row fv-plugins-icon-container">
                        <!--begin::Label-->
                        <label class="required form-label">{{ __('users.full_name') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="full_name" class="form-control mb-2" placeholder="{{ __('users.enter_full_name') }}" value="{{ old('full_name', $user->full_name) }}" />
                        <!--end::Input-->
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row fv-plugins-icon-container">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('users.phone') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="phone" class="form-control mb-2" placeholder="{{ __('users.enter_phone') }}" value="{{ old('phone', $user->phone) }}" />
                        <!--end::Input-->
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="row">
                    <!--begin::Input group-->
                    <div class="col-md-12 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('users.address') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea name="address" class="form-control mb-2" rows="3" placeholder="{{ __('users.enter_address') }}">{{ old('address', $user->address) }}</textarea>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="row">
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('users.birth_date') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="date" name="birth_date" class="form-control mb-2" value="{{ old('birth_date', $user->formatted_birth_date) }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('users.password') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="password" name="password" class="form-control mb-2" placeholder="{{ __('users.enter_new_password') }}" />
                        <!--end::Input-->
                        <!--begin::Description-->
                        <div class="text-muted fs-7">{{ __('users.help.password') }}</div>
                        <!--end::Description-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--begin::Input group-->
                <div class="mb-0 fv-row">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('users.description') }}</label>
                    <!--end::Label-->
                    <!--begin::Editor-->
                    <textarea name="description" class="form-control mb-2" rows="4" placeholder="{{ __('users.enter_description') }}">{{ old('description', $user->description) }}</textarea>
                    <!--end::Editor-->
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::General options-->
        
        <!--begin::Roles-->
        @can('manage_user_roles')
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ __('users.user_roles') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Roles-->
                <div class="d-flex flex-wrap">
                    @if(isset($roles))
                        @foreach ($roles as $role)
                            <!--begin::Checkbox-->
                            <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20 mb-3">
                                <input class="form-check-input" type="checkbox" value="{{ $role->id }}" name="roles[]" 
                                    {{ $user->roles->contains($role->id) ? 'checked' : '' }} />
                                <span class="form-check-label">{{ $role->display_name }}</span>
                            </label>
                            <!--end::Checkbox-->
                        @endforeach
                    @endif
                </div>
                <!--end::Roles-->
            </div>
            <!--end::Card body-->
        </div>
        @endcan
        <!--end::Roles-->

        <!--begin::Branch Shops-->
        @can('manage_user_branch_shops')
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ __('users.branch_shops') }}</h2>
                </div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_branch_shop">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        {{ __('users.add_branch_shop') }}
                    </button>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table wrapper-->
                <div class="table-responsive">
                    <!--begin::Table-->
                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_user_branch_shops_table">
                        <!--begin::Table head-->
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-250px">{{ __('branch_shops.name') }}</th>
                                <th class="min-w-125px">{{ __('users.role_in_shop') }}</th>
                                <th class="min-w-125px">{{ __('users.start_date') }}</th>
                                <th class="min-w-125px">{{ __('users.status') }}</th>
                                <th class="min-w-100px">{{ __('users.is_primary') }}</th>
                                <th class="text-end min-w-100px">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <!--end::Table head-->
                        <!--begin::Table body-->
                        <tbody class="fw-semibold text-gray-600">
                            @forelse($user->branchShops as $branchShop)
                                <tr data-branch-shop-id="{{ $branchShop->id }}">
                                    <td class="d-flex align-items-center">
                                        <!--begin::Branch info-->
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('admin.branch-shops.show', $branchShop->id) }}" class="text-gray-800 text-hover-primary mb-1">{{ $branchShop->name }}</a>
                                            <span class="text-muted">{{ $branchShop->code }}</span>
                                        </div>
                                        <!--end::Branch info-->
                                    </td>
                                    <td>
                                        <span class="badge badge-light-info">{{ $branchShop->pivot->role_label }}</span>
                                    </td>
                                    <td>{{ \App\Models\User::formatPivotDate($branchShop->pivot->start_date) }}</td>
                                    <td>
                                        @if($branchShop->pivot->is_active)
                                            <span class="badge badge-light-success">{{ __('users.active') }}</span>
                                        @else
                                            <span class="badge badge-light-danger">{{ __('users.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($branchShop->pivot->is_primary)
                                            <span class="badge badge-light-primary">{{ __('users.primary') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">{{ __('common.actions') }}
                                            <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                        </a>
                                        <!--begin::Menu-->
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3" data-kt-user-branch-shop-action="edit" data-branch-shop-id="{{ $branchShop->id }}">{{ __('common.edit') }}</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3" data-kt-user-branch-shop-action="remove" data-branch-shop-id="{{ $branchShop->id }}">{{ __('common.remove') }}</a>
                                            </div>
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu-->
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10">
                                        <div class="text-muted">{{ __('users.no_branch_shops') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Table wrapper-->
            </div>
            <!--end::Card body-->
        </div>
        @endcan
        <!--end::Branch Shops-->

        <div class="d-flex justify-content-end">
            <!--begin::Button-->
            <a href="{{ route('admin.users.index') }}" id="kt_user_cancel" class="btn btn-light me-5">{{ __('common.cancel') }}</a>
            <!--end::Button-->
            <!--begin::Button-->
            <button type="submit" id="kt_user_submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('users.update_user') }}</span>
                <span class="indicator-progress">{{ __('common.please_wait') }}...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
            <!--end::Button-->
        </div>
    </div>
    <!--end::Main column-->
</form>
<!--end::Form-->

<!--begin::Modals-->
@include('admin.users.modals.add-branch-shop')
@include('admin.users.modals.edit-branch-shop')
<!--end::Modals-->
@endsection

@push('scripts')
<script src="{{ asset('admin-assets/assets/js/custom/apps/users/edit-user.js') }}"></script>
@endpush
