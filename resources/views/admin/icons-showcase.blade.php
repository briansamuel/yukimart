@extends('admin.index')

@section('page-header', 'Font Awesome Icons')
@section('page-sub_header', 'Icon Showcase')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Font Awesome Icons Showcase</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Basic Icons -->
                    <div class="col-md-6 mb-8">
                        <h4 class="mb-4">Basic Icons</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-home me-3 text-primary"></i>
                                    <span>Home</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user me-3 text-info"></i>
                                    <span>User</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-cog me-3 text-warning"></i>
                                    <span>Settings</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-search me-3 text-success"></i>
                                    <span>Search</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Icons -->
                    <div class="col-md-6 mb-8">
                        <h4 class="mb-4">Action Icons</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-edit me-3 text-primary"></i>
                                    <span>Edit</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-trash me-3 text-danger"></i>
                                    <span>Delete</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-eye me-3 text-info"></i>
                                    <span>View</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-copy me-3 text-secondary"></i>
                                    <span>Copy</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Icons -->
                    <div class="col-md-6 mb-8">
                        <h4 class="mb-4">Status Icons</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="ms-3">Success</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-warning"></i>
                                    <span class="ms-3">Warning</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <span class="ms-3">Danger</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-info"></i>
                                    <span class="ms-3">Info</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Icons -->
                    <div class="col-md-6 mb-8">
                        <h4 class="mb-4">Business Icons</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-wallet me-3 text-success"></i>
                                    <span>Wallet</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-box me-3 text-warning"></i>
                                    <span>Package</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-truck me-3 text-primary"></i>
                                    <span>Delivery</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar me-3 text-info"></i>
                                    <span>Calendar</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Examples -->
                <div class="separator my-10"></div>
                <h4 class="mb-4">Usage Examples</h4>

                <div class="row">
                    <div class="col-md-12 mb-6">
                        <h5>In Blade Templates:</h5>
                        <div class="bg-light p-4 rounded">
                            <code>
                                &lt;!-- Basic icons --&gt;<br>
                                &lt;i class="fas fa-home"&gt;&lt;/i&gt;<br>
                                &lt;i class="fas fa-edit"&gt;&lt;/i&gt;<br>
                                &lt;i class="fas fa-trash"&gt;&lt;/i&gt;<br><br>

                                &lt;!-- Icons with classes --&gt;<br>
                                &lt;i class="fas fa-home me-2 text-primary"&gt;&lt;/i&gt;<br>
                                &lt;i class="fas fa-check-circle text-success"&gt;&lt;/i&gt;<br>
                                &lt;i class="fas fa-edit me-2"&gt;&lt;/i&gt;<br><br>

                                &lt;!-- Different sizes --&gt;<br>
                                &lt;i class="fas fa-home fa-lg"&gt;&lt;/i&gt;<br>
                                &lt;i class="fas fa-home fa-2x"&gt;&lt;/i&gt;<br>
                                &lt;i class="fas fa-home fa-3x"&gt;&lt;/i&gt;
                            </code>
                        </div>
                    </div>

                    <div class="col-md-12 mb-6">
                        <h5>In PHP Controllers:</h5>
                        <div class="bg-light p-4 rounded">
                            <code>
                                // Direct HTML in controllers<br><br>

                                // Basic usage<br>
                                '&lt;i class="fas fa-home me-2 text-primary"&gt;&lt;/i&gt;'<br><br>

                                // Action buttons<br>
                                '&lt;i class="fas fa-edit me-2"&gt;&lt;/i&gt;'<br>
                                '&lt;i class="fas fa-check-circle text-success"&gt;&lt;/i&gt;'<br>
                                '&lt;i class="fas fa-trash text-danger"&gt;&lt;/i&gt;'<br><br>

                                // Button examples<br>
                                '&lt;button class="btn btn-primary"&gt;&lt;i class="fas fa-plus me-2"&gt;&lt;/i&gt;Add&lt;/button&gt;'<br>
                                '&lt;button class="btn btn-danger"&gt;&lt;i class="fas fa-trash me-2"&gt;&lt;/i&gt;Delete&lt;/button&gt;'
                            </code>
                        </div>
                    </div>
                </div>

                <!-- Available Icons List -->
                <div class="separator my-10"></div>
                <h4 class="mb-4">Available Font Awesome Classes</h4>
                <div class="row">
                    <div class="col-md-3">
                        <ul class="list-unstyled">
                            <li><code>fas fa-home</code></li>
                            <li><code>fas fa-user</code></li>
                            <li><code>fas fa-cog</code></li>
                            <li><code>fas fa-edit</code></li>
                            <li><code>fas fa-trash</code></li>
                            <li><code>fas fa-eye</code></li>
                            <li><code>fas fa-copy</code></li>
                            <li><code>fas fa-print</code></li>
                            <li><code>fas fa-download</code></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="list-unstyled">
                            <li><code>fas fa-search</code></li>
                            <li><code>fas fa-filter</code></li>
                            <li><code>fas fa-plus</code></li>
                            <li><code>fas fa-save</code></li>
                            <li><code>fas fa-times</code></li>
                            <li><code>fas fa-wallet</code></li>
                            <li><code>fas fa-box</code></li>
                            <li><code>fas fa-truck</code></li>
                            <li><code>fas fa-clock</code></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="list-unstyled">
                            <li><code>fas fa-check-circle</code></li>
                            <li><code>fas fa-times-circle</code></li>
                            <li><code>fas fa-calendar</code></li>
                            <li><code>fas fa-rocket</code></li>
                            <li><code>fas fa-question-circle</code></li>
                            <li><code>fas fa-undo</code></li>
                            <li><code>fas fa-arrow-up</code></li>
                            <li><code>fas fa-arrow-down</code></li>
                            <li><code>fas fa-sync-alt</code></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <ul class="list-unstyled">
                            <li><code>fas fa-bolt</code></li>
                            <li><code>fas fa-medal</code></li>
                            <li><code>fas fa-ellipsis-v</code></li>
                            <li><code>fas fa-check</code></li>
                            <li><code>fas fa-info-circle</code></li>
                            <li><code>fas fa-star</code></li>
                            <li><code>fas fa-cogs</code></li>
                            <li><code>fas fa-bell</code></li>
                        </ul>
                    </div>
                </div>

                <!-- Size Examples -->
                <div class="separator my-10"></div>
                <h4 class="mb-4">Icon Sizes</h4>
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center gap-4 mb-4">
                            <div class="text-center">
                                <i class="fas fa-home"></i>
                                <div class="small text-muted mt-1">Default</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-home fa-lg"></i>
                                <div class="small text-muted mt-1">fa-lg</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-home fa-2x"></i>
                                <div class="small text-muted mt-1">fa-2x</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-home fa-3x"></i>
                                <div class="small text-muted mt-1">fa-3x</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
