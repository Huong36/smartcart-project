/**
 * SmartCart - Live Search / Auto-suggest Module
 * Phụ trách: Thành viên 4 (nongvanminh)
 */

(function () {
    'use strict';

    let debounceTimer = null;
    let abortController = null;
    let currentSelectedIndex = -1;

    // Helper escape HTML chống XSS
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Hiển thị trạng thái Loading
    function renderLoading(container) {
        container.innerHTML = `
            <div class="p-3 text-center text-secondary small">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Đang tìm kiếm sản phẩm...
            </div>
        `;
        container.classList.remove('d-none');
    }

    // Hiển thị trạng thái Không tìm thấy
    function renderEmpty(container, query) {
        container.innerHTML = `
            <div class="p-3 text-center text-muted small">
                Không tìm thấy sản phẩm nào khớp với "<strong>${escapeHtml(query)}</strong>"
            </div>
        `;
        container.classList.remove('d-none');
    }

    // Hiển thị trạng thái Lỗi
    function renderError(container) {
        container.innerHTML = `
            <div class="p-3 text-center text-danger small">
                Không thể tải kết quả. Vui lòng kiểm tra lại kết nối mạng.
            </div>
        `;
        container.classList.remove('d-none');
    }

    // Đóng dropdown
    function closeDropdown(container) {
        if (!container) return;
        container.classList.add('d-none');
        container.innerHTML = '';
        currentSelectedIndex = -1;
    }

    // Render danh sách sản phẩm gợi ý
    function renderSuggestions(container, items, query) {
        if (!items || items.length === 0) {
            renderEmpty(container, query);
            return;
        }

        container.innerHTML = '';
        currentSelectedIndex = -1;

        const list = document.createElement('div');
        list.className = 'list-group list-group-flush';

        items.forEach((item, index) => {
            const itemEl = document.createElement('div');
            itemEl.className = 'list-group-item list-group-item-action p-2 d-flex align-items-center justify-content-between gap-2 search-item';
            itemEl.dataset.index = index;
            itemEl.dataset.productId = item.id;

            // Xử lý highlight từ khóa an toàn
            const safeName = escapeHtml(item.name);
            const safeCategory = escapeHtml(item.category || '');
            const safePrice = escapeHtml(item.price_formatted);
            const isOutOfStock = item.is_out_of_stock;

            itemEl.innerHTML = `
                <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden">
                    <img src="${escapeHtml(item.image_url)}" alt="${safeName}" class="rounded border" style="width: 44px; height: 44px; object-fit: contain; background: #f8faf9; flex-shrink: 0;">
                    <div class="text-truncate">
                        <div class="fw-semibold text-truncate small mb-0 text-dark">${safeName}</div>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="text-success fw-bold">${safePrice}</span>
                            ${isOutOfStock 
                                ? '<span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">Hết hàng</span>'
                                : '<span class="text-muted" style="font-size: 0.75rem;">Còn hàng</span>'
                            }
                        </div>
                    </div>
                </div>
                <div>
                    ${!isOutOfStock ? `
                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 btn-add-to-cart" data-product-id="${item.id}" title="Thêm nhanh vào giỏ">
                            + Giỏ
                        </button>
                    ` : ''}
                </div>
            `;

            list.appendChild(itemEl);
        });

        // Footer gợi ý
        const footer = document.createElement('div');
        footer.className = 'p-2 bg-light text-center small text-secondary border-top';
        footer.textContent = `Hiển thị ${items.length} gợi ý hàng đầu`;
        list.appendChild(footer);

        container.appendChild(list);
        container.classList.remove('d-none');
    }

    // Xử lý gửi request tìm kiếm
    async function performSearch(query, container) {
        query = query.trim();

        if (query.length < 2) {
            closeDropdown(container);
            return;
        }

        // Hủy request cũ nếu đang chạy
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        renderLoading(container);

        try {
            const url = `/search/suggest?q=${encodeURIComponent(query)}`;
            const response = await fetch(url, {
                signal: abortController.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                renderError(container);
                return;
            }

            const resData = await response.json();
            if (resData.success) {
                renderSuggestions(container, resData.data, query);
            } else {
                renderError(container);
            }
        } catch (err) {
            if (err.name === 'AbortError') {
                // Request bị hủy bởi request mới hơn -> bỏ qua
                return;
            }
            console.error('Live search error:', err);
            renderError(container);
        }
    }

    // Điều hướng bằng bàn phím (ArrowUp, ArrowDown, Enter, Escape)
    function handleKeyNavigation(e, inputEl, container) {
        const items = container.querySelectorAll('.search-item');
        if (!items || items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentSelectedIndex = (currentSelectedIndex + 1) % items.length;
            updateItemSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentSelectedIndex = (currentSelectedIndex - 1 + items.length) % items.length;
            updateItemSelection(items);
        } else if (e.key === 'Enter') {
            if (currentSelectedIndex >= 0 && items[currentSelectedIndex]) {
                e.preventDefault();
                const selected = items[currentSelectedIndex];
                const addBtn = selected.querySelector('.btn-add-to-cart');
                if (addBtn) {
                    addBtn.click();
                }
            }
        } else if (e.key === 'Escape') {
            closeDropdown(container);
        }
    }

    function updateItemSelection(items) {
        items.forEach((item, idx) => {
            if (idx === currentSelectedIndex) {
                item.classList.add('active', 'bg-light');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active', 'bg-light');
            }
        });
    }

    // Khởi tạo Live Search
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('navbar-live-search');
        const searchDropdown = document.getElementById('navbar-search-dropdown');

        if (!searchInput || !searchDropdown) return;

        // Sự kiện gõ từ khóa (input) kèm debounce 300ms
        searchInput.addEventListener('input', function () {
            const query = searchInput.value;

            clearTimeout(debounceTimer);

            if (query.trim().length === 0) {
                closeDropdown(searchDropdown);
                if (abortController) abortController.abort();
                return;
            }

            debounceTimer = setTimeout(() => {
                performSearch(query, searchDropdown);
            }, 300);
        });

        // Sự kiện phím điều hướng
        searchInput.addEventListener('keydown', function (e) {
            if (!searchDropdown.classList.contains('d-none')) {
                handleKeyNavigation(e, searchInput, searchDropdown);
            }
        });

        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                closeDropdown(searchDropdown);
            }
        });

        // Mở lại dropdown khi focus nếu input đã có sẵn từ khóa >= 2 ký tự
        searchInput.addEventListener('focus', function () {
            if (searchInput.value.trim().length >= 2 && searchDropdown.children.length > 0) {
                searchDropdown.classList.remove('d-none');
            }
        });
    });
})();
