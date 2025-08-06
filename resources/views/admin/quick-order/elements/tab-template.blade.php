<!-- Template for Order Tab Content -->
<div id="orderTabTemplate" style="display: none;">
    <div class="row main-row h-100">
        <!-- Left Column - Order Items -->
        <div class="col-8 main-col">
            <div class="order-items-col">
                <div class="order-items-header">
                    <h4 style="margin: 0; font-size: 16px; font-weight: 600;">{{ __('order.order_items') }}</h4>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #7e8299;">
                        <span class="items-count">0</span> {{ __('order.items') }}
                    </p>
                </div>

                <div class="order-items-list" id="TAB_ID_orderItemsList">
                    <div class="empty-order" id="TAB_ID_emptyOrderState">
                        <div class="empty-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="empty-text">
                            {{ __('order.scan_barcodes_to_start') }}
                        </div>
                        <div class="empty-hint">
                            Nhấn F3 để focus vào ô nhập mã vạch
                        </div>
                    </div>
                </div>

                <!-- Exchange Search Section (only for return tabs) -->
                <div class="exchange-search-section" id="TAB_ID_exchangeSearchSection" style="display: none;">
                    <div class="exchange-search-container">
                        <input type="text"
                               class="exchange-search-input"
                               id="TAB_ID_exchangeSearchInput"
                               placeholder="Tìm hàng đổi (F7)"
                               autocomplete="off">
                        <i class="fas fa-search exchange-search-icon"></i>
                    </div>
                    <!-- Exchange Product Suggestions (disabled) -->
                    <div class="product-suggestions" id="TAB_ID_exchangeProductSuggestions" style="display: none;"></div>
                </div>

                <!-- Exchange Items List (only for return tabs) -->
                <div class="exchange-items-list" id="TAB_ID_exchangeItemsList">
                    <div class="exchange-items-header">
                        <h6 style="margin: 0; font-size: 14px; font-weight: 600; color: #50cd89;">
                            <i class="fas fa-shopping-cart"></i> Hàng đổi
                        </h6>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #7e8299;">
                            <span class="exchange-items-count">0</span> sản phẩm
                        </p>
                    </div>
                    <div class="exchange-items-content">
                        <div class="empty-exchange" id="TAB_ID_emptyExchangeState">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="empty-text">
                                Chưa có hàng đổi
                            </div>
                            <div class="empty-hint">
                                Nhấn F7 để tìm hàng đổi
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Notes Section -->
                <div class="order-notes-section">
                    <label class="form-label">{{ __('order.notes') }}</label>
                    <textarea name="notes" id="orderNotes" placeholder="{{ __('order.order_notes_placeholder') }}"></textarea>
                </div>
            </div>
        </div>

        <!-- Right Column - Customer Info -->
        <div class="col-4 main-col">
            <!-- Return Order Header (only for return tabs) -->
            <!-- Hidden field to store selected invoice ID for return tabs -->
            <input type="hidden" id="TAB_ID_selectedInvoiceId" value="" />

            <div class="return-order-header" id="TAB_ID_returnOrderHeader" style="display: none;">
                <div class="return-customer-info">
                    <i class="fas fa-user-circle"></i>
                    <span class="return-customer-name" id="TAB_ID_returnCustomerName">Chọn hóa đơn</span>
                    <span class="return-customer-points" id="TAB_ID_returnCustomerPoints">Điểm: 0</span>
                </div>
                <h3 class="return-order-title" id="TAB_ID_returnOrderTitle">Trả hàng / Chọn hóa đơn</h3>
            </div>

            <!-- Header Info Section -->
            <div class="header-info-section hide-in-return" id="TAB_ID_headerInfoSection">
                <!-- Dropdowns Row -->
                <div class="dropdowns-row">
                    <!-- Seller Dropdown -->
                    <div class="info-dropdown" id="TAB_ID_sellerDropdown">
                        <div class="info-label">Người bán</div>
                        <div class="info-dropdown-toggle {{ Auth::user()->is_root == 1 ? '' : 'disabled' }}" id="TAB_ID_sellerToggle">
                            <span id="TAB_ID_sellerName">{{ Auth::user()->full_name ?? Auth::user()->name }}</span>
                            @if(Auth::user()->is_root == 1)
                                <i class="fas fa-chevron-down"></i>
                            @endif
                        </div>
                        @if(Auth::user()->is_root == 1)
                            <div class="info-dropdown-menu" id="TAB_ID_sellerMenu">
                                <div class="info-dropdown-search">
                                    <input type="text" placeholder="Tìm người bán..." id="TAB_ID_sellerSearch">
                                </div>
                                <div id="TAB_ID_sellerList">
                                    <!-- Seller list will be populated by JavaScript -->
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Channel Dropdown -->
                    <div class="info-dropdown" id="TAB_ID_channelDropdown">
                        <div class="info-label">Kênh bán hàng</div>
                        <div class="info-dropdown-toggle" id="TAB_ID_channelToggle">
                            <div class="info-dropdown-icon channel-icon offline">
                                <i class="fas fa-store"></i>
                            </div>
                            <span id="TAB_ID_channelName">Cửa hàng</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="info-dropdown-menu" id="TAB_ID_channelMenu">
                            <div class="info-dropdown-search">
                                <input type="text" placeholder="Tìm kênh bán hàng..." id="TAB_ID_channelSearch">
                            </div>
                            <div id="TAB_ID_channelList">
                                <div class="info-dropdown-item selected" data-channel="offline">
                                    <div class="info-dropdown-icon channel-icon offline">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <span>Cửa hàng</span>
                                    <i class="fas fa-check ms-auto"></i>
                                </div>
                                <div class="info-dropdown-item" data-channel="online">
                                    <div class="info-dropdown-icon channel-icon online">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <span>Website</span>
                                </div>
                                <div class="info-dropdown-item" data-channel="marketplace">
                                    <div class="info-dropdown-icon channel-icon marketplace">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <span>Marketplace</span>
                                </div>
                                <div class="info-dropdown-item" data-channel="social_media">
                                    <div class="info-dropdown-icon channel-icon social_media">
                                        <i class="fab fa-facebook-f"></i>
                                    </div>
                                    <span>Mạng xã hội</span>
                                </div>
                                <div class="info-dropdown-item" data-channel="phone_order">
                                    <div class="info-dropdown-icon channel-icon phone_order">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <span>Điện thoại</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Branch Shop and Time Row -->
                <div class="branch-time-row">
                    <!-- Branch Shop Column -->
                    <div class="col-6">
                        <div class="info-dropdown" id="TAB_ID_branchShopDropdown">
                            <div class="info-label">Chi nhánh</div>
                            <div class="info-dropdown-toggle" id="TAB_ID_branchShopToggle">
                                <div class="info-dropdown-icon branch-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <span id="TAB_ID_branchShopName">{{ $defaultBranchShop->name ?? 'Chọn chi nhánh' }}</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="info-dropdown-menu" id="TAB_ID_branchShopMenu">
                                <div class="info-dropdown-search">
                                    <input type="text" placeholder="Tìm chi nhánh..." id="TAB_ID_branchShopSearch">
                                </div>
                                <div id="TAB_ID_branchShopList">
                                    @foreach($branchShops as $branchShop)
                                        <div class="info-dropdown-item {{ ($defaultBranchShop && $defaultBranchShop->id == $branchShop->id) ? 'selected' : '' }}"
                                             data-branch-id="{{ $branchShop->id }}"
                                             data-branch-name="{{ $branchShop->name }}">
                                            <div class="info-dropdown-icon branch-icon">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div class="info-dropdown-text">
                                                <span class="info-dropdown-title">{{ $branchShop->name }}</span>
                                                @if($branchShop->address)
                                                    <span class="info-dropdown-subtitle">{{ $branchShop->address }}</span>
                                                @endif
                                            </div>
                                            @if($defaultBranchShop && $defaultBranchShop->id == $branchShop->id)
                                                <i class="fas fa-check ms-auto"></i>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Time Column -->
                    <div class="col-6">
                        <div class="info-time-display">
                            <div class="current-time" id="currentTime">{{ now()->format('d/m/Y H:i:s') }}</div>
                            <div class="order-time" id="orderCreatedTime">Thời gian tạo đơn: <span id="orderTime">--:--</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="customer-col">
                <div class="customer-header">
                    <h4 style="margin: 0; font-size: 16px; font-weight: 600;">{{ __('order.order_info') }}</h4>
                </div>

                <!-- Customer form only for non-return tabs -->
                <div class="customer-form hide-in-return">
                    <div class="form-group">
                        <div class="customer-selection">
                            <!-- Customer search input (shown when no customer selected) -->
                            <div class="autocomplete-container" id="TAB_ID_customerSearchContainer">
                                <input type="text" class="form-control" name="customer_search" id="TAB_ID_customerSearch"
                                       placeholder="Tìm khách hàng (để trống = Khách lẻ)" autocomplete="off">
                                <div class="autocomplete-suggestions" id="TAB_ID_customerSuggestions"></div>
                            </div>

                            <!-- Selected customer display (shown when customer selected) -->
                            <div class="selected-customer" id="TAB_ID_selectedCustomerDisplay" style="display: none;">
                                <div class="customer-info">
                                    <i class="fas fa-user-circle text-primary"></i>
                                    <span class="customer-name-phone" id="TAB_ID_selectedCustomerText" data-customer-id="" style="cursor: pointer; color: #009ef7; text-decoration: underline;"></span>
                                </div>
                                <button type="button" class="btn-remove-customer" id="TAB_ID_removeCustomerBtn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <input type="hidden" name="customer_id" id="TAB_ID_customerSelect">
                        </div>
                    </div>

                    <!-- Hidden branch shop field - use default -->
                    <input type="hidden" name="branch_shop_id" id="branchShopSelect" value="{{ $defaultBranchShop->id ?? '' }}">

                    <div class="form-group">
                        <label class="form-label">{{ __('order.payment_method') }}</label>
                        <div class="payment-methods">
                            <label class="payment-method active" data-method="cash">
                                <input type="radio" name="payment_method" value="cash" checked>
                                <span class="payment-method-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <span class="payment-method-text">{{ __('order.cash') }}</span>
                            </label>
                            <label class="payment-method" data-method="transfer">
                                <input type="radio" name="payment_method" value="transfer">
                                <span class="payment-method-icon"><i class="fas fa-university"></i></span>
                                <span class="payment-method-text">{{ __('order.transfer') }}</span>
                            </label>
                            <label class="payment-method" data-method="card">
                                <input type="radio" name="payment_method" value="card">
                                <span class="payment-method-icon"><i class="fas fa-credit-card"></i></span>
                                <span class="payment-method-text">{{ __('order.card') }}</span>
                            </label>
                            <label class="payment-method" data-method="wallet">
                                <input type="radio" name="payment_method" value="wallet">
                                <span class="payment-method-icon"><i class="fas fa-wallet"></i></span>
                                <span class="payment-method-text">{{ __('order.wallet') }}</span>
                            </label>
                        </div>

                        <!-- Bank Account Selection (shown when transfer is selected) -->
                        <div class="bank-account-section" id="bankAccountSection">
                            <div class="bank-transfer-layout">
                                <!-- QR Code -->
                                <div class="qr-code-container">
                                    <div class="qr-code-image" id="qrCodeImage">
                                        <i class="fas fa-qrcode" style="font-size: 48px; color: #e4e6ef;"></i>
                                    </div>
                                    <button type="button" class="qr-toggle-btn" id="showQrBtn">
                                        <i class="fas fa-qrcode"></i>
                                        <span>{{ __('order.show_qr_code') }}</span>
                                    </button>
                                </div>

                                <!-- Bank Account Dropdown -->
                                <div class="bank-account-dropdown">
                                    <div class="autocomplete-container">
                                        <div class="bank-account-select" id="bankAccountSelect">
                                            <span class="bank-account-text" id="selectedBankText">{{ __('order.select_bank_account') }}</span>
                                            <i class="fas fa-chevron-down bank-account-dropdown-icon"></i>
                                        </div>
                                        <div class="bank-account-options" id="bankAccountOptions">
                                            @foreach($bankAccounts as $account)
                                                <div class="bank-account-option {{ $account->is_default ? 'selected' : '' }}"
                                                     data-account-id="{{ $account->id }}"
                                                     data-bank-name="{{ $account->bank_name }}"
                                                     data-account-number="{{ $account->account_number }}"
                                                     data-account-holder="{{ $account->account_holder }}">
                                                    <div class="bank-option-name">{{ $account->bank_name }}</div>
                                                    <div class="bank-option-details">{{ $account->account_number }} - {{ $account->account_holder }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="bank_account_id" id="bankAccountId" value="{{ $bankAccounts->where('is_default', true)->first()->id ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Return Order Summary (only for return tabs) -->
                <div class="return-summary-section" id="TAB_ID_returnSummarySection" style="display: none;">
                    <!-- Return Header with Invoice Info -->
                  

                    <!-- Return Summary -->
                    <div class="return-summary-block">
                        <div class="summary-section-title text-success">
                            <i class="fas fa-undo"></i>
                            Trả hàng
                        </div>
                        <div class="summary-row">
                            <span>Tổng giá gốc hàng mua</span>
                            <span class="summary-count">1</span>
                            <span id="TAB_ID_originalTotalAmount">45,000</span>
                        </div>
                        <div class="summary-row">
                            <span>Tổng tiền hàng trả</span>
                            <span class="summary-count">1</span>
                            <span id="TAB_ID_returnTotalAmount">45,000</span>
                        </div>
                        <div class="summary-row">
                            <span>Giảm giá</span>
                            <span id="TAB_ID_returnDiscountAmount">0</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí trả hàng</span>
                            <span id="TAB_ID_returnFeeAmount">0</span>
                        </div>
                        <div class="summary-row">
                            <span>Hoàn trả thu khác</span>
                            <span id="TAB_ID_returnOtherAmount">0</span>
                        </div>
                        <div class="summary-row total-row">
                            <span class="fw-bold">Tổng tiền trả</span>
                            <span id="TAB_ID_returnTotalRefund" class="fw-bold text-success">45,000</span>
                        </div>
                    </div>

                    <!-- Exchange/Purchase Summary -->
                    <div class="exchange-summary-block">
                        <div class="summary-section-title text-primary">
                            <i class="fas fa-shopping-cart"></i>
                            Mua hàng
                            <div class="float-end">
                                <input type="checkbox" id="TAB_ID_deliveryCheckbox" class="form-check-input">
                                <label for="TAB_ID_deliveryCheckbox" class="form-check-label ms-1">Giao hàng</label>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span>Tổng tiền hàng</span>
                            <span class="summary-count">1</span>
                            <span id="TAB_ID_exchangeSubtotalAmount">40,000</span>
                        </div>
                        <div class="summary-row">
                            <span>Giảm giá</span>
                            <span id="TAB_ID_exchangeDiscountAmount">0</span>
                        </div>
                        <div class="summary-row">
                            <span>Thu khác</span>
                            <span id="TAB_ID_exchangeOtherAmount">0</span>
                        </div>
                        <div class="summary-row total-row">
                            <span class="fw-bold">Tổng tiền mua</span>
                            <span id="TAB_ID_exchangeTotalAmount" class="fw-bold">40,000</span>
                        </div>
                    </div>

                    <!-- Final Calculation Summary using regular-summary-section -->
                    <div class="final-calculation-summary">
                        <div class="summary-row total">
                            <span id="TAB_ID_finalCalculationLabel" class="fs-4 text-primary">Cần trả khách</span>
                            <span id="TAB_ID_finalCalculationAmount" class="fs-3 fw-bold text-primary">5,000</span>
                        </div>
                        <div class="summary-row payment">
                            <span id="TAB_ID_paymentLabel" class="fs-5">Tiền trả khách</span>
                            <span id="TAB_ID_paymentAmount" class="fs-5 fw-bold text-success">5,000</span>
                        </div>
                    </div>

                    <!-- Payment Methods for Return Tabs -->
                    <div class="return-payment-methods-section">
                        <div class="form-group">
                            <label class="form-label">{{ __('order.payment_method') }}</label>
                            <div class="payment-methods">
                                <label class="payment-method active" data-method="cash">
                                    <input type="radio" name="payment_method" value="cash" checked>
                                    <span class="payment-method-icon"><i class="fas fa-money-bill-wave"></i></span>
                                    <span class="payment-method-text">{{ __('order.cash') }}</span>
                                </label>
                                <label class="payment-method" data-method="transfer">
                                    <input type="radio" name="payment_method" value="transfer">
                                    <span class="payment-method-icon"><i class="fas fa-university"></i></span>
                                    <span class="payment-method-text">{{ __('order.transfer') }}</span>
                                </label>
                                <label class="payment-method" data-method="card">
                                    <input type="radio" name="payment_method" value="card">
                                    <span class="payment-method-icon"><i class="fas fa-credit-card"></i></span>
                                    <span class="payment-method-text">{{ __('order.card') }}</span>
                                </label>
                                <label class="payment-method" data-method="wallet">
                                    <input type="radio" name="payment_method" value="wallet">
                                    <span class="payment-method-icon"><i class="fas fa-wallet"></i></span>
                                    <span class="payment-method-text">{{ __('order.wallet') }}</span>
                                </label>
                            </div>

                            <!-- Bank Account Selection (shown when transfer is selected) -->
                            <div class="bank-account-section" id="bankAccountSection">
                                <div class="bank-transfer-layout">
                                    <!-- QR Code -->
                                    <div class="qr-code-container">
                                        <div class="qr-code-image" id="qrCodeImage">
                                            <i class="fas fa-qrcode" style="font-size: 48px; color: #e4e6ef;"></i>
                                        </div>
                                        <button type="button" class="qr-toggle-btn" id="showQrBtn">
                                            <i class="fas fa-qrcode"></i>
                                            <span>{{ __('order.show_qr_code') }}</span>
                                        </button>
                                    </div>

                                    <!-- Bank Account Dropdown -->
                                    <div class="bank-account-dropdown">
                                        <div class="autocomplete-container">
                                            <div class="bank-account-select" id="bankAccountSelect">
                                                <span class="bank-account-text" id="selectedBankText">{{ __('order.select_bank_account') }}</span>
                                                <i class="fas fa-chevron-down bank-account-dropdown-icon"></i>
                                            </div>
                                            <div class="bank-account-options" id="bankAccountOptions">
                                                @foreach($bankAccounts as $account)
                                                    <div class="bank-account-option {{ $account->is_default ? 'selected' : '' }}"
                                                         data-account-id="{{ $account->id }}"
                                                         data-bank-name="{{ $account->bank_name }}"
                                                         data-account-number="{{ $account->account_number }}"
                                                         data-account-holder="{{ $account->account_holder }}">
                                                        <div class="bank-option-name">{{ $account->bank_name }}</div>
                                                        <div class="bank-option-details">{{ $account->account_number }} - {{ $account->account_holder }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <input type="hidden" name="bank_account_id" id="bankAccountId" value="{{ $bankAccounts->where('is_default', true)->first()->id ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="quick-amounts-section">
                        <div class="quick-amounts">
                            <button type="button" class="quick-amount-btn" data-amount="5000">5,000</button>
                            <button type="button" class="quick-amount-btn" data-amount="6000">6,000</button>
                            <button type="button" class="quick-amount-btn" data-amount="10000">10,000</button>
                            <button type="button" class="quick-amount-btn" data-amount="20000">20,000</button>
                            <button type="button" class="quick-amount-btn" data-amount="50000">50,000</button>
                            <button type="button" class="quick-amount-btn" data-amount="100000">100,000</button>
                            <button type="button" class="quick-amount-btn" data-amount="200000">200,000</button>
                            <button type="button" class="quick-amount-btn" data-amount="500000">500,000</button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="return-action-buttons">
                        <button type="button" class="btn btn-secondary btn-lg me-2" id="TAB_ID_printButton">IN</button>
                        <button type="button" class="btn btn-primary btn-lg flex-fill" id="TAB_ID_returnButton">TRẢ HÀNG</button>
                    </div>
                </div>
            </div>

            <!-- Order Summary - Sticky Bottom -->
            <div class="order-summary">

                <!-- Regular Order Summary (for order/invoice tabs) -->
                <div class="regular-summary-section" id="TAB_ID_regularSummarySection">
                    <div class="summary-row">
                        <span class="fs-5">Tổng tiền hàng</span>
                        <span id="subtotalAmount" class="fs-5 fw-bold">0 ₫</span>
                    </div>
                    <div class="summary-row">
                        <span class="fs-5">Giảm giá</span>
                        <input type="text" id="discountAmount" class="summary-input text-primary fw-bold fs-5"
                               value="0" readonly onclick="openDiscountModal()"
                               style="cursor: pointer; background: transparent; border: none; border-bottom: 2px solid #009ef7; outline: none; text-align: right; width: 120px;">
                    </div>
                    <div class="summary-row">
                        <span class="fs-5">Thu khác</span>
                        <input type="text" id="otherAmount" class="summary-input fw-bold fs-5"
                               value="0" readonly onclick="openOtherChargesModal()"
                               style="cursor: pointer; background: transparent; border: none; border-bottom: 2px solid #6c757d; outline: none; text-align: right; width: 120px;">
                    </div>
                    <div class="summary-row total">
                        <span class="fs-4 text-primary">Khách cần trả</span>
                        <span id="totalAmount" class="fs-3 fw-bold text-primary">0 ₫</span>
                    </div>
                    <div class="summary-row payment">
                        <span class="fs-5">Khách thanh toán</span>
                        <input type="text" id="paidAmount" class="summary-input text-success fw-bold fs-5"
                               value="0"
                               style="background: transparent; border: none; border-bottom: 2px solid #198754; outline: none; text-align: right; width: 120px;">
                    </div>
                </div>

                <button type="button" id="createOrderBtn" class="create-order-btn" disabled>
                    <span class="btn-text">THANH TOÁN</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden inputs for order data -->
    <input type="hidden" id="soldBy" value="{{ Auth::id() }}">
    <input type="hidden" id="channel" value="offline">
</div>
