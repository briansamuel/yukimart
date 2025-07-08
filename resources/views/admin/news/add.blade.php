@extends('admin.index')
@section('page-header', 'Trang')
@section('page-sub_header', 'THÊM TRANG')
@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/fancybox/jquery.fancybox.min.css') }} " />
@endsection
@section('content')

    <form id="kt_add_news_form" class="form fv-plugins-bootstrap5 fv-plugins-framework" action="#">
        {{ csrf_field() }}
        <input type="hidden" name="post_type" value="news" />
        <div class="row">
            @include('admin.elements.error_flash')

            <div class="col-xxl-12">
                @include('admin.elements.alert_flash')
            </div>
        </div>

        <div class="row mb-5">

            <div class="col-md-8 col-lg-9">
                <div class="card shadow-sm mb-5">
                    <div class="card-header">
                        <h3 class="card-title "><i class="fa fa-star"></i> {{ __('admin.general.required_enter_field') }}
                        </h3>
                    </div>
                    <!--begin::Form-->
                    <div class="card-body">
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_title"
                                class="col-12 col-lg-12 col-xl-2 required form-label">{{ __('admin.news.title') }}:</label>

                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="post_title" name="post_title"
                                    placeholder="{{ __('admin.news.title_placeholder') }}">
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_slug"
                                class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.news.slug') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="post_slug" name="post_slug"
                                    placeholder="{{ __('admin.news.slug_placeholder') }}">
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_description"
                                class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.news.summary') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea id="post_description" rows="4" name="post_description" class="tox-target"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_content"
                                class="col-12 col-lg-12 col-xl-2 required form-label">{{ __('admin.news.content') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea id="post_content" name="post_content" class="tox-target"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-flush shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-star"></i> {{ __('admin.general.seo') }}</h3>
                    </div>
                    <!--begin::Form-->
                    <div class="card-body">
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_seo_title"
                                class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.general.seo_title') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="post_seo_title"
                                    name="post_seo_title" placeholder="{{ __('admin.general.seo_title_placeholder') }}">
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_seo_keyword"
                                class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.general.seo_keyword') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="post_seo_keyword"
                                    name="post_seo_keyword"
                                    placeholder="{{ __('admin.general.seo_keyword_placeholder') }}">
                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_seo_description"
                                class="col-12 col-lg-12 col-xl-2 form-label">{{ __('admin.general.seo_description') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea class="form-control" type="text" value="" id="post_seo_description" name="post_seo_description"
                                    rows="6" placeholder="{{ __('admin.general.seo_description_placeholder') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Right Sidebar Add New --}}
            <div class="col-md-4 col-lg-3">
                {{-- Card Submit form --}}
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="form-group row mb-5">
                            <label for="language" class="col-4 form-label">{{ __('admin.general.language') }}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="language" name="language">
                                    @foreach ($arrayLang as $key => $lang)
                                        <option value="{{ $key }}">{{ $lang }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="created_at"
                                class="col-4 form-label">{{ __('admin.general.publish_date') }}:</label>
                            <div class="col-8">
                                <input class="form-control rounded rounded-end-0"
                                    placeholder="{{ __('admin.general.pick_a_date') }}" id="kt_news_created_at"
                                    data-kt-news-table-filter="date_picker" name="created_at">

                            </div>
                        </div>
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="post_status" class="col-4 form-label">{{ __('admin.general.status') }}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="post_status" name="post_status">
                                    <option value="draft" selected="selected">{{ __('admin.general.draft') }}</option>
                                    <option value="pending">{{ __('admin.general.pending') }}</option>
                                    <option value="publish">{{ __('admin.general.publish') }}</option>
                                </select>

                            </div>
                        </div>


                    </div>
                    <div class="card-footer p-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-kt-news-action="submit">
                            <span class="indicator-label">{{ __('admin.news.add') }} </span>
                            <span class="indicator-progress">@lang('admin.general.please_waiting')
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>

                    </div>
                </div>
                {{-- End Card submit form --}}
                {{-- Card category --}}
                <div class="card shadow-sm p-5 mt-5">

                    <div class="card-body p-2">
                        <h3 class="card-title mb-5">{{ __('admin.news.add_categories') }}</h3>
                        @include('admin.elements.categories_checkbox')
                    </div>
                </div>
                {{-- End Card category --}}
                {{-- Card upload thumbnail --}}
                <div class="card shadow-sm p-5 mt-5">
                    <div class="card-body p-2">
                        <h3 class="card-title mb-5">{{ __('admin.news.select_thumbnail') }}</h3>
                        <div class="form-group row mb-0">
                            <div class="col-12">
                                <a data-src="@filemanager_get_resource(dialog.php)?type=1&field_id=thumbnail&lang=vi&akey=@filemanager_get_key()"
                                    class="iframe-btn" data-fancybox data-fancybox data-type="iframe"
                                    href="javascript:;">
                                    <img id="preview_thumbnail" class="img-fluid"
                                        src="{{ asset('admin-assets/assets/images/upload-thumbnail.png') }}" />
                                </a>
                                <input type="hidden" name="post_thumbnail" id="thumbnail" value="">
                            </div>
                        </div>
                    </div>
                </div>
                {{-- End Card upload thumbnail --}}

            </div>

        </div>

    </form>
@endsection
@section('vendor-script')
    <script src="assets/plugins/custom/tinymce/tinymce.bundle.js" type="text/javascript"></script>
@endsection
@section('script')
    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('admin-assets/assets/plugins/custom/fancybox/jquery.fancybox.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('admin-assets/assets/plugins/custom/fancybox/jquery.observe_field.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/news/list/add.js') }}"></script>

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
                            selector: "#post_description",
                            toolbar: !1,
                            statusbar: !1,
                            height: 200,
                            skin: skinTinyMCE,
                            content_css: themeMode,
                            setup: function(editor) {
                                editor.on('change', function() {
                                    tinymce.triggerSave();
                                });
                            }
                        }), tinymce.init({
                            selector: "#post_content",
                            height: 500,
                            skin: skinTinyMCE,
                            content_css: themeMode,
                            menubar: !1,
                            paste_data_images: true,
                            relative_urls: false,
                            remove_script_host: false,
                            setup: function(editor) {
                                editor.on('change', function() {
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
                                "filemanager": base_url +
                                    "/vendor/responsivefilemanager/plugin.min.js"
                            }
                        })
                    }
                };
                jQuery(document).ready((function() {
                    i.init()
                }))
            }
        });

        $('.iframe-btn').fancybox({
            // 'width': 900,
            // 'height': 600,
            'type': 'iframe',
            'iframe': {
                'css': {
                    'width': '90%',
                    'height': '90%',
                }
            },
            'autoScale': true
        });

        // $(document).ready(function() {

        //     $("#post_thumbnail").observe_field(1, function() {

        //         $('#preview_thumbnail').attr('src', this.value).show();
        //     });
        // });
        function responsive_filemanager_callback(field_id){
            console.log(field_id);
            var url=jQuery('#'+field_id).val();
            // alert('update '+field_id+" with "+url);
            $('#preview_thumbnail').attr('src', url).show();
            //your code
        }
    </script>
    <!--end::Page Custom Javascript-->

@endsection
