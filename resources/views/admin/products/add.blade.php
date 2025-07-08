@extends('admin.index')
@section('page-header', __('product.products'))
@section('page-sub_header', __('product.add_product'))
@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/fancybox/jquery.fancybox.min.css') }}" />
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/tagify/tagify.bundle.css') }}" />
@endsection
@section('content')

    <form id="kt_add_product_form" class="form fv-plugins-bootstrap5 fv-plugins-framework" action="{{ route('admin.products.add.action') }}">
        {{ csrf_field() }}
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
                        <h3 class="card-title "><i class="fa fa-star"></i> {{ __('product.product_information') }}</h3>
                    </div>
                    <!--begin::Form-->
                    <div class="card-body">
                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="product_name" class="col-12 col-lg-12 col-xl-2 required form-label">{{ __('product.product_name') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="product_name" name="product_name"
                                    placeholder="{{ __('product.product_name') }}">
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="product_slug" class="col-12 col-lg-12 col-xl-2 form-label">{{ __('product.product_slug') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <input class="form-control" type="text" value="" id="product_slug" name="product_slug"
                                    placeholder="{{ __('product.product_slug') }}">
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group fv-row fv-plugins-icon-container">
                                    <label for="sku" class="required form-label">{{ __('product.sku') }}:</label>
                                    <input class="form-control" type="text" value="" id="sku" name="sku"
                                        placeholder="{{ __('product.sku') }}">
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fv-row fv-plugins-icon-container">
                                    <label for="barcode" class="form-label">{{ __('product.barcode') }}:</label>
                                    <input class="form-control" type="text" value="" id="barcode" name="barcode"
                                        placeholder="{{ __('product.barcode') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group fv-row fv-plugins-icon-container">
                                    <label for="cost_price" class="required form-label">{{ __('product.cost_price') }}:</label>
                                    <input class="form-control" type="number" step="0.01" value="" id="cost_price" name="cost_price"
                                        placeholder="0.00">
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fv-row fv-plugins-icon-container">
                                    <label for="sale_price" class="required form-label">{{ __('product.sale_price') }}:</label>
                                    <input class="form-control" type="number" step="0.01" value="" id="sale_price" name="sale_price"
                                        placeholder="0.00">
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="product_description" class="col-12 col-lg-12 col-xl-2 form-label">{{ __('product.product_description') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea id="product_description" rows="4" name="product_description" class="tox-target"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="product_content" class="col-12 col-lg-12 col-xl-2 required form-label">{{ __('product.product_content') }}:</label>
                            <div class="col-12 col-lg-12 col-xl-10">
                                <textarea id="product_content" name="product_content" class="tox-target"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-5">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-box"></i> {{ __('product.inventory') }} & {{ __('product.details') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="reorder_point" class="form-label">{{ __('product.reorder_point') }}:</label>
                                    <input class="form-control" type="number" value="0" id="reorder_point" name="reorder_point"
                                        placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="weight" class="form-label">{{ __('product.weight') }} ({{ __('product.gram') }}):</label>
                                    <input class="form-control" type="number" value="" id="weight" name="weight"
                                        placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="points" class="form-label">{{ __('product.points') }}:</label>
                                    <input class="form-control" type="number" value="0" id="points" name="points"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brand" class="form-label">{{ __('product.brand') }}:</label>
                                    <input class="form-control" type="text" value="" id="brand" name="brand"
                                        placeholder="{{ __('product.brand') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="form-label">{{ __('product.location') }}:</label>
                                    <input class="form-control" type="text" value="" id="location" name="location"
                                        placeholder="{{ __('product.location') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product Variants Section --}}
                @include('admin.products.partials.variants')
            </div>

            {{-- Right Sidebar Add New --}}
            <div class="col-md-4 col-lg-3">
                {{-- Card Submit form --}}
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="form-group row mb-5">
                            <label for="language" class="col-4 form-label">{{ __('product.language') }}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="language" name="language">
                                    <option value="vi">{{ __('common.vietnamese') }}</option>
                                    <option value="en">{{ __('common.english') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="product_status" class="col-4 form-label">{{ __('product.product_status') }}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="product_status" name="product_status">
                                    <option value="draft" selected="selected">{{ __('product.draft') }}</option>
                                    <option value="pending">{{ __('product.pending') }}</option>
                                    <option value="publish">{{ __('product.publish') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="product_type" class="col-4 form-label">{{ __('product.product_type') }}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="product_type" name="product_type">
                                    <option value="simple" selected="selected">{{ __('product.simple') }}</option>
                                    <option value="variable">{{ __('product.variable') }}</option>
                                    <option value="grouped">{{ __('product.grouped') }}</option>
                                    <option value="external">{{ __('product.external') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-5 fv-row fv-plugins-icon-container">
                            <label for="category_id" class="col-4 form-label">{{ __('product_category.category') }}:</label>
                            <div class="col-8">
                                <select class="form-control kt-select2" id="category_id" name="category_id" data-placeholder="{{ __('product_category.select_category') }}">
                                    <option value="">{{ __('product_category.select_category') }}</option>
                                    @if(isset($categories))
                                        @foreach($categories as $category)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-5">
                            <div class="col-12">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" id="product_feature" name="product_feature">
                                    <label class="form-check-label" for="product_feature">
                                        {{ __('product.product_featured') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer p-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-kt-product-action="submit">
                            <span class="indicator-label">{{ __('product.add_product') }}</span>
                            <span class="indicator-progress">{{ __('common.please_wait') }}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
                {{-- End Card submit form --}}

                {{-- Card upload thumbnail --}}
                <div class="card shadow-sm mt-5">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-image"></i> {{ __('product.product_image') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('product.product_image') }}</label>
                            <div class="input-group">
                                <input id="thumbnail" class="form-control" type="text" name="product_thumbnail" placeholder="{{ __('common.select_image') }}..." readonly>
                                <button id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary" type="button">
                                    <i class="fa fa-picture-o"></i> {{ __('common.browse_files') }}
                                </button>
                            </div>
                            <div class="form-text">{{ __('common.click_browse_files_description') }}</div>
                        </div>

                        <!-- Alternative file upload -->
                        <div class="form-group mb-3">
                            <label class="form-label">{{ __('common.or_upload_new_image') }}</label>
                            <input type="file" id="file_upload" class="form-control" accept="image/*">
                            <div class="form-text">{{ __('common.upload_image_description') }}</div>
                        </div>

                        <div class="form-group">
                            <div id="holder" class="text-center">
                                <img id="preview_thumbnail" class="img-fluid border rounded" style="max-height: 200px; max-width: 100%;"
                                    src="{{ asset('admin-assets/assets/images/upload-thumbnail.png') }}" alt="Product thumbnail preview" />
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
    <script src="{{ asset('admin-assets/assets/plugins/custom/tinymce/tinymce.bundle.js') }}" type="text/javascript"></script>
    <script src="{{ asset('admin-assets/assets/plugins/custom/tagify/tagify.bundle.js') }}" type="text/javascript"></script>
@endsection
@section('scripts')
    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('admin-assets/assets/js/custom/apps/products/list/add.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/products/variants/variant-manager.js') }}"></script>

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
                            selector: "#product_description",
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
                            selector: "#product_content",
                            height: 500,
                            skin: skinTinyMCE,
                            content_css: themeMode,
                            menubar: !1,
                            paste_data_images: true,
                            automatic_uploads: true,
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
                            file_picker_callback: function(callback, value, meta) {
                                var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                                var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

                                // Use our new custom file manager
                                var cmsURL = '/admin/filemanager?editor=' + meta.fieldname;
                                if (meta.filetype == 'image') {
                                    cmsURL = cmsURL + "&type=images";
                                } else {
                                    cmsURL = cmsURL + "&type=files";
                                }

                                // Open custom file manager
                                var popup = window.open(cmsURL, 'filemanager', 'width=' + (x * 0.8) + ',height=' + (y * 0.8) + ',scrollbars=yes,resizable=yes');

                                // Set callback for file selection
                                window.SetUrl = function(url, file_path) {
                                    callback(url);
                                    if (popup && !popup.closed) {
                                        popup.close();
                                    }
                                };
                            },
                            images_upload_handler: function (blobInfo, success, failure) {
                                var xhr, formData;
                                xhr = new XMLHttpRequest();
                                xhr.withCredentials = false;
                                xhr.open('POST', '/admin/upload-image');
                                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                                xhr.onload = function() {
                                    var json;
                                    if (xhr.status != 200) {
                                        failure('HTTP Error: ' + xhr.status);
                                        return;
                                    }
                                    json = JSON.parse(xhr.responseText);
                                    if (!json || typeof json.url != 'string') {
                                        failure('Invalid JSON: ' + xhr.responseText);
                                        return;
                                    }
                                    success(json.url);
                                };

                                formData = new FormData();
                                formData.append('upload', blobInfo.blob(), blobInfo.filename());
                                xhr.send(formData);
                            }
                        })
                    }
                };
                jQuery(document).ready((function() {
                    i.init()
                }))
            }
        });

        // Initialize file upload functionality when document is ready
        $(document).ready(function() {
            // Custom File Manager integration
            $('#lfm').click(function(e) {
                e.preventDefault();

                // Open our new custom file manager in popup window
                var popup = window.open(
                    '/admin/filemanager?type=images&field_id=thumbnail',
                    'filemanager',
                    'width=1200,height=800,scrollbars=yes,resizable=yes'
                );

                // Global callback function for file manager
                window.SetUrl = function (url, file_path) {
                    // Update the input field with the selected file path
                    $('#thumbnail').val(file_path).trigger('change');

                    // Update preview image
                    $('#preview_thumbnail').attr('src', url);

                    // Close the popup
                    if (popup && !popup.closed) {
                        popup.close();
                    }
                };
            });

            // Handle direct file upload
            $('#file_upload').change(function(e) {
                var file = e.target.files[0];
                if (file) {
                    // Create FormData for file upload
                    var formData = new FormData();
                    formData.append('upload', file);
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                    // Show loading
                    $('#preview_thumbnail').attr('src', '{{ asset('admin-assets/assets/images/loading.gif') }}');

                    // Upload file via AJAX
                    $.ajax({
                        url: '/admin/upload-image', // You'll need to create this route
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                $('#thumbnail').val(response.path);
                                $('#preview_thumbnail').attr('src', response.url);
                            } else {
                                alert('Upload failed: ' + response.message);
                                $('#preview_thumbnail').attr('src', '{{ asset('admin-assets/assets/images/upload-thumbnail.png') }}');
                            }
                        },
                        error: function() {
                            alert('Upload failed. Please try again.');
                            $('#preview_thumbnail').attr('src', '{{ asset('admin-assets/assets/images/upload-thumbnail.png') }}');
                        }
                    });
                }
            });

            // Update preview when thumbnail input changes
            $('#thumbnail').on('input change', function() {
                var url = $(this).val();
                if (url) {
                    $('#preview_thumbnail').attr('src', url).show();
                } else {
                    $('#preview_thumbnail').attr('src', '{{ asset('admin-assets/assets/images/upload-thumbnail.png') }}');
                }
            });

            // Global callback for file manager
            window.SetUrl = function (url, file_path) {
                $('#thumbnail').val(file_path).trigger('change');
                $('#preview_thumbnail').attr('src', file_path);
            };
        });
    </script>
    <!--end::Page Custom Javascript-->
@endsection
