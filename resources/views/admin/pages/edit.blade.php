@extends('admin.index')
@section('page-header', 'Chỉnh sửa trang')
@section('page-sub_header', 'Trang')
@section('style')
    {{-- <link rel="stylesheet" href="admin/plugins/fancybox/jquery.fancybox.min.css" /> --}}
@endsection
@section('content')
    <form  id="kt_edit_page_form" class="form fv-plugins-bootstrap5 fv-plugins-framework" action="#">
        {{ csrf_field() }}
        <input data-kt-pages-action="id" type="hidden" name="id" value="{{ $page->id }}" />
        <div class="row mb-3">
            @include('admin.elements.error_flash')

            <div class="col-12">
                @include('admin.elements.alert_flash')
            </div>
        </div>
        <div class="row mb-5">

            <div class="col-md-8 col-lg-9">
                <div class="card card-flush shadow-sm mb-5">
                    <div class="card-header">
                        <h3 class="card-title "><i class="fa fa-star"></i> {{ __('admin.general.required_enter_field')}}</h3>
                    </div>
                    <!--begin::Form-->
                    <div class="card-body">
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_title" class="col-12 col-lg-12 col-xl-2 required form-label">{{ __('admin.pages.title') }}:</label>

                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="{{ $page->page_title }}" id="page_title" name="page_title"
                                    placeholder="{{ __('admin.pages.title_placeholder')}}">
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_slug" class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.pages.slug')}}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="{{ $page->page_slug }}" id="page_slug" name="page_slug"
                                    placeholder="{{ __('admin.pages.slug_placeholder')}}">
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_description" class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.pages.summary')}}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea id="page_description" rows="4" name="page_description" class="tox-target">{{ $page->page_description }}</textarea>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_content" class="col-12 col-lg-12 col-xl-2 required form-label">{{ __('admin.pages.content')}}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea id="page_content" name="page_content" class="tox-target">{{ $page->page_content }}</textarea>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-flush shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-star"></i> {{ __('admin.general.seo')}}</h3>
                    </div>
                    <!--begin::Form-->
                    <div class="card-body">
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_seo_title" class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.general.seo_title')}}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="page_seo_title"
                                    name="page_seo_title" placeholder="{{ __('admin.general.seo_title_placeholder')}}">
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_seo_keyword" class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.general.seo_keyword')}}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="page_seo_keyword"
                                    name="page_seo_keyword" placeholder="{{ __('admin.general.seo_keyword_placeholder')}}">
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_seo_description" class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.general.seo_description')}}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea class="form-control" type="text" value="" id="page_seo_description" name="page_seo_description"
                                    rows="6" placeholder="{{ __('admin.general.seo_description_placeholder')}}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="card card-flush shadow-sm p-5">
                    <div class="card-body p-3">
                        <div class="form-group row mb-5">
                            <label for="language" class="col-4 form-label">{{ __('admin.general.language')}}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="language" name="language">
                                    @foreach ($arrayLang as $key => $lang)
                                        <option {{ $key == $page->language ? 'selected' : '' }} value="{{ $key }}" disabled)>{{ $lang }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="created_at" class="col-4 form-label">{{ __('admin.general.publish_date')}}:</label>
                            <div class="col-8">
                                <input class="form-control form-control-solid rounded rounded-end-0" placeholder="{{ __('admin.general.pick_a_date')}}"
                                id="kt_pages_created_at" data-kt-page-table-filter="date_picker" name="created_at" value=" {{ $page->created_at_default }}">
                                
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_status" class="col-4 form-label">{{ __('admin.general.status')}}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="page_status" name="page_status">
                                    <option value="draft" {{ 'draft' == $page->page_status ? 'selected' : '' }} >{{ __('admin.general.draft')}}</option>
                                    <option value="pending" {{ 'pending' == $page->page_status ? 'selected' : '' }}>{{ __('admin.general.spending')}}</option>
                                    <option value="pending" {{ 'pending' == $page->page_status ? 'selected' : '' }}>{{ __('admin.general.publish')}}</option>
                                </select>

                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="page_status" class="col-4 form-label">{{ __('admin.pages.select_template')}}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="page_template" name="page_template">
                                    <option value="default" {{ 'default' == $page->page_template ? 'selected' : '' }}>{{ __('admin.general.default')}}</option>
                                    <option value="about-page" {{ 'about-page' == $page->page_template ? 'selected' : '' }}>{{ __('admin.pages.about_page')}}</option>
                                    <option value="contact-page" {{ 'contact-page' == $page->page_template ? 'selected' : '' }}>{{ __('admin.pages.contact_page')}}</option>
                                </select>

                            </div>
                        </div>

                    </div>
                    <div class="card-footer p-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-kt-pages-action="submit">
                            <span class="indicator-label">{{ __('admin.pages.edit')}} </span>
                            <span class="indicator-progress">@lang('admin.general.please_waiting') 
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                       
                    </div>
                </div>


            </div>

        </div>
    </form>
@endsection
@section('vendor-script')
    <script src="assets/plugins/custom/tinymce/tinymce.bundle.js" type="text/javascript"></script>
@endsection
@section('script')
     <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('admin-assets/assets/js/custom/apps/pages/list/edit.js') }}"></script>

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
                            selector: "#page_description",
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
                        }), tinymce.init({
                            selector: "#page_content",
                            height: 500,
                            skin: skinTinyMCE,
                            content_css: themeMode,
                            menubar: !1,
                            paste_data_images: true,
                            relative_urls: false,
                            remove_script_host: false,
                            setup: function (editor) {
                                editor.on('change', function () {
                                    tinymce.triggerSave();
                                });
                            },
                            toolbar: ["styleselect fontselect fontsizeselect",
                                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                                "bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap | print preview |  code"
                            ],
                            plugins: [
                                "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                                "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
                                "table contextmenu directionality paste textcolor code"
                            ],
                            image_advtab: true,
                            image_advtab: true,
                            image_advtab: true,
                            filemanager_access_key: '@filemanager_get_key()',
                            filemanager_sort_by: '',
                            filemanager_descending: '',
                            filemanager_subfolder: '',
                            filemanager_crossdomain: '',
                            external_filemanager_path: '@filemanager_get_resource(dialog.php)',
                            filemanager_title: "Responsive Filemanager",
                            external_plugins: {
                                "filemanager": base_url + "/vendor/responsivefilemanager/plugin.min.js"
                            }
                        })
                    }
                };
                jQuery(document).ready((function() {
                    i.init()
                }))
            }
        });

        // $('.iframe-btn').fancybox({
        //     // 'width': 900,
        //     // 'height': 600,
        //     // 'type': 'iframe',
        //     'iframe': {
        //         'css': {
        //             'width': '90%',
        //             'height': '90%',
        //         }
        //     },
        //     // 'autoScale': true
        // });

        // $(document).ready(function() {



        // });
    </script>
     <!--end::Page Custom Javascript-->

@endsection