@extends('admin.index')
@section('page-header', 'Tin tức')
@section('page-sub_header', 'Danh sách tin')
@section('container-width', 'container-fluid')
@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
@endsection
@section('content')
<div class="row mb-5">
    <div class="col-md-4">
        <form id="kt_add_category_form" class="form fv-plugins-bootstrap5 fv-plugins-framework" action="#">
            {{ csrf_field() }}
            <input class="form-control" type="hidden" value="category_of_news" id="category_type" name="category_type">
            <div class="card">
                <div class="card-body">
                    <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                        <label for="post_title" class="col-12 col-lg-12 col-xl-12 required form-label">{{ __('admin.categories.title') }}:</label>
                        <div class="col-12 col-lg-12">
                            <input class="form-control" type="text" value="" id="category_name" name="category_name"
                                placeholder="{{ __('admin.categories.title_placeholder')}}">
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                        <label for="category_slug" class="col-12 col-lg-12 col-xl-12 form-label">{{ __('admin.categories.slug') }}:</label>
                        <div class="col-12 col-lg-12">

                            <input class="form-control" type="text" value="" id="category_slug" name="category_slug"
                                placeholder="{{ __('admin.categories.slug_placeholder')}}">
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                        <label for="category_status" class="col-12 col-lg-12 form-label">{{ __('admin.categories.status')}}:</label>
                        <div class="col-12 col-lg-12">
                            <select class="form-control kt-select2" id="category_status" name="category_status">
                                <option value="draft" >{{ __('admin.general.draft')}}</option>
                                <option value="pending">{{ __('admin.general.pending')}}</option>
                                <option value="publish">{{ __('admin.general.publish')}}</option>
                            </select>

                        </div>
                    </div>
                    <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                        <label for="category_parent" class="col-12 col-lg-12 form-label">{{ __('admin.category.parent')}}:</label>
                        <div class="col-12 col-lg-12">
                            <select class="form-control kt-select2" id="category_parent" name="category_parent">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                        <label for="language" class="col-12 col-lg-12 form-label">{{ __('admin.general.language')}}:</label>
                        <div class="col-12 col-lg-12">
                            <select class="form-control kt-select2" id="language" name="language">
                                @foreach ($arrayLang as $key => $lang)
                                    <option value="{{ $key }}" >{{ $lang }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                        <label for="category_description" class="col-12 col-lg-12 form-label">{{ __('admin.categories.description')}}:</label>
                        <div class="col-12 col-lg-12">
                            <textarea id="category_description" name="category_description" class="tox-target"></textarea>
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer p-3 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-kt-category-action="submit">
                        <span class="indicator-label">{{ __('admin.categories.add')}} </span>
                        <span class="indicator-progress">@lang('admin.general.please_waiting')
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>

                </div>
            </div>
        </form>
    </div>
    <div class="col-md-8">
        <div class="card">
            @include('admin.categories.elements.toolbar')
            <div class="card-body pt-0">
                <!--begin::Table-->
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_category">
                    <!--begin::Table head-->
                    <thead>
                        <!--begin::Table row-->
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                                        data-kt-check-target="#kt_table_category .form-check-input" value="1">
                                </div>
                            </th>
                            <th>@lang('admin.categories.title')</th>
                            <th>@lang('admin.categories.slug')</th>
                            <th>@lang('admin.categories.post_count')</th>
                            <th>@lang('admin.categories.status')</th>
                            <th>@lang('admin.categories.created_at')</th>
                            <th class="text-end min-w-100px">@lang('admin.general.action')</th>
                        </tr>
                        <!--end::Table row-->
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody class="text-gray-600 fw-bold">

                    </tbody>
                    <!--end::Table body-->
                </table>
                <!--end::Table-->
            </div>
        </div>
    </div>
</div>
@endsection
@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="assets/plugins/custom/tinymce/tinymce.bundle.js" type="text/javascript"></script>
@endsection
@section('script')


    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('admin-assets/assets/js/custom/apps/categories/list/table.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/categories/add.js') }}"></script>

    <script>
        ! function(t) {
            var e = {};

            function n(i) {
                if (e[i]) return e[i].exports;
                var r = e[i] = {
                    i: i,
                    l: !1,
                    exports: {}
                };
                return t[i].call(r.exports, r, r.exports, n), r.l = !0, r.exports
            }
            n.m = t, n.c = e, n.d = function(t, e, i) {
                n.o(t, e) || Object.defineProperty(t, e, {
                    enumerable: !0,
                    get: i
                })
            }, n.r = function(t) {
                "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
                    value: "Module"
                }), Object.defineProperty(t, "__esModule", {
                    value: !0
                })
            }, n.t = function(t, e) {
                if (1 & e && (t = n(t)), 8 & e) return t;
                if (4 & e && "object" == typeof t && t && t.__esModule) return t;
                var i = Object.create(null);
                if (n.r(i), Object.defineProperty(i, "default", {
                        enumerable: !0,
                        value: t
                    }), 2 & e && "string" != typeof t)
                    for (var r in t) n.d(i, r, function(e) {
                        return t[e]
                    }.bind(null, r));
                return i
            }, n.n = function(t) {
                var e = t && t.__esModule ? function() {
                    return t.default
                } : function() {
                    return t
                };
                return n.d(e, "a", e), e
            }, n.o = function(t, e) {
                return Object.prototype.hasOwnProperty.call(t, e)
            }, n.p = "", n(n.s = 677)
        }({
            677: function(t, e, n) {
                "use strict";
                var i = {
                    init: function() {
                        tinymce.init({
                            selector: "#category_description",
                            toolbar: !1,
                            statusbar: !1,
                            height: 200,
                            skin: skinTinyMCE,
                            content_css: themeMode,
                            setup: function (editor) {
                                editor.on('change', function () {
                                    tinymce.triggerSave();
                                });
                            }
                        })
                    }
                };
                jQuery(document).ready((function() {
                    i.init()
                }))
            }
        });


    </script>

    <!--end::Page Custom Javascript-->

@endsection
