{{-- Floating Compare Bar - Khay so sánh nổi --}}
<div id="compareBar" class="compare-bar" style="display: none;">
    <div class="compare-bar-inner">
        {{-- 3 Slot --}}
        <div class="compare-slots" id="compareSlotsContainer">
            @for($i = 0; $i < 3; $i++)
                <div class="compare-slot" id="compareSlot{{ $i }}" data-index="{{ $i }}">
                    <div class="compare-slot-empty" onclick="openCompareSearch({{ $i }})">
                        <i class="fa-solid fa-plus"></i>
                        <span>Thêm sản phẩm</span>
                    </div>
                    <div class="compare-slot-filled" style="display: none;" data-product-id="">
                        <img class="compare-slot-img" src="" alt="">
                        <div class="compare-slot-info">
                            <span class="compare-slot-name"></span>
                            <span class="compare-slot-price"></span>
                        </div>
                        <button class="compare-slot-remove" onclick="removeFromCompare(this)" title="Xóa">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Hành động --}}
<div class="compare-actions">
            <a href="{{ route('compare.index') }}" class="compare-btn-go" id="compareGoBtn">
                So sánh ngay
                <span class="compare-count-badge" id="compareCountBadge">0</span>
            </a>
            <button class="compare-btn-clear" onclick="clearCompare()">Xóa tất cả</button>
            <button type="button" class="compare-btn-clear" id="compareCollapseBtn" style="margin-left:8px;">Thu gọn</button>
        </div>
        @vite('resources/js/compare.js')
    </div>
</div>

{{-- Modal tìm kiếm nhanh --}}
<div id="compareSearchModal" class="compare-search-modal" style="display: none;">
    <div class="compare-search-content">
        <div class="compare-search-header">
            <h3><i class="fa-solid fa-magnifying-glass"></i> Tìm sản phẩm để so sánh</h3>
            <button onclick="closeCompareSearch()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="compare-search-body">
            <input type="text" id="compareSearchInput" placeholder="Nhập tên sản phẩm..." autocomplete="off">
            <div id="compareSearchResults" class="compare-search-results"></div>
        </div>
    </div>
</div>
