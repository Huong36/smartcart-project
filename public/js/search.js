/**
 * SmartCart - Live Search / Auto-suggest Module
 * Phụ trách: Thành viên 4 (nongvanminh)
 */

(function () {
    'use strict';

    let debounceTimer = null;
    let abortController = null;
    let searchGeneration = 0;
    let isDropdownDismissed = false;
    let currentSelectedIndex = -1;

    // Helper escape HTML (phòng ngừa cho các trường hợp cần chuỗi an toàn)
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

    // Hủy các request và debounce đang chờ
    function cancelPendingSearch() {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
        if (abortController) {
            abortController.abort();
            abortController = null;
        }
        searchGeneration++;
    }

    // Hiển thị trạng thái Loading
    function renderLoading(container) {
        container.innerHTML = '';
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'p-3 text-center text-secondary small';
        loadingDiv.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div><span>Đang tìm kiếm sản phẩm...</span>';
        container.appendChild(loadingDiv);
        container.classList.remove('d-none');
    }

    // Hiển thị trạng thái Không tìm thấy (dùng textContent an toàn chống XSS)
    function renderEmpty(container, query) {
        container.innerHTML = '';
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'p-3 text-center text-muted small';
        emptyDiv.appendChild(document.createTextNode('Không tìm thấy sản phẩm nào khớp với "'));
        const strong = document.createElement('strong');
        strong.textContent = query;
        emptyDiv.appendChild(strong);
        emptyDiv.appendChild(document.createTextNode('"'));
        container.appendChild(emptyDiv);
        container.classList.remove('d-none');
    }

    // Hiển thị trạng thái Lỗi
    function renderError(container) {
        container.innerHTML = '';
        const errorDiv = document.createElement('div');
        errorDiv.className = 'p-3 text-center text-danger small';
        errorDiv.textContent = 'Không thể tải kết quả. Vui lòng kiểm tra lại kết nối mạng.';
        container.appendChild(errorDiv);
        container.classList.remove('d-none');
    }

    // Đóng dropdown và hủy mọi tác vụ tìm kiếm đang chờ
    function closeDropdown(container) {
        if (!container) return;
        cancelPendingSearch();
        isDropdownDismissed = true;
        container.classList.add('d-none');
        container.innerHTML = '';
        currentSelectedIndex = -1;
    }

    // Render danh sách sản phẩm gợi ý an toàn tuyệt đối chống XSS
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

            const contentDiv = document.createElement('div');
            contentDiv.className = 'd-flex align-items-center gap-2 flex-grow-1 overflow-hidden';

            const img = document.createElement('img');
            img.src = item.image_url || '';
            img.alt = item.name || '';
            img.className = 'rounded border';
            img.style.cssText = 'width: 44px; height: 44px; object-fit: contain; background: #f8faf9; flex-shrink: 0;';

            const infoDiv = document.createElement('div');
            infoDiv.className = 'text-truncate';

            const nameDiv = document.createElement('div');
            nameDiv.className = 'fw-semibold text-truncate small mb-0 text-dark';
            nameDiv.textContent = item.name || ''; // An toàn tuyệt đối với textContent

            const metaDiv = document.createElement('div');
            metaDiv.className = 'd-flex align-items-center gap-2 small';

            const priceSpan = document.createElement('span');
            priceSpan.className = 'text-success fw-bold';
            priceSpan.textContent = item.price_formatted || '';

            metaDiv.appendChild(priceSpan);

            if (item.is_out_of_stock) {
                const stockBadge = document.createElement('span');
                stockBadge.className = 'badge bg-danger-subtle text-danger';
                stockBadge.style.fontSize = '0.7rem';
                stockBadge.textContent = 'Hết hàng';
                metaDiv.appendChild(stockBadge);
            } else {
                const stockSpan = document.createElement('span');
                stockSpan.className = 'text-muted';
                stockSpan.style.fontSize = '0.75rem';
                stockSpan.textContent = 'Còn hàng';
                metaDiv.appendChild(stockSpan);
            }

            infoDiv.appendChild(nameDiv);
            infoDiv.appendChild(metaDiv);

            contentDiv.appendChild(img);
            contentDiv.appendChild(infoDiv);

            const actionDiv = document.createElement('div');
            if (!item.is_out_of_stock) {
                const addBtn = document.createElement('button');
                addBtn.type = 'button';
                addBtn.className = 'btn btn-sm btn-outline-primary py-1 px-2 btn-add-to-cart';
                addBtn.dataset.productId = item.id;
                addBtn.title = 'Thêm nhanh vào giỏ';
                addBtn.textContent = '+ Giỏ';
                actionDiv.appendChild(addBtn);
            }

            itemEl.appendChild(contentDiv);
            itemEl.appendChild(actionDiv);

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
    async function performSearch(query, container, generation) {
        const searchInput = document.getElementById('navbar-live-search');
        if (!searchInput) return;

        // Kiểm tra generation và giá trị input hiện tại
        if (generation !== searchGeneration || searchInput.value.trim() !== query || isDropdownDismissed) {
            return;
        }

        if (query.length < 2) {
            closeDropdown(container);
            return;
        }

        abortController = new AbortController();
        const signal = abortController.signal;

        renderLoading(container);

        try {
            const url = `/search/suggest?q=${encodeURIComponent(query)}`;
            const response = await fetch(url, {
                signal: signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            // Sau khi fetch xong, kiểm tra tính hợp lệ trước khi thao tác DOM
            if (generation !== searchGeneration || searchInput.value.trim() !== query || isDropdownDismissed) {
                return;
            }

            if (!response.ok) {
                renderError(container);
                return;
            }

            const resData = await response.json();

            // Kiểm tra lại sau khi parse JSON
            if (generation !== searchGeneration || searchInput.value.trim() !== query || isDropdownDismissed) {
                return;
            }

            if (resData.success) {
                renderSuggestions(container, resData.data, query);
            } else {
                renderError(container);
            }
        } catch (err) {
            if (err.name === 'AbortError') {
                return;
            }
            // Chỉ render error nếu request này vẫn là mới nhất và input còn khớp
            if (generation === searchGeneration && searchInput.value.trim() === query && !isDropdownDismissed) {
                console.error('Live search error:', err);
                renderError(container);
            }
        } finally {
            if (abortController && abortController.signal === signal) {
                abortController = null;
            }
        }
    }

    // Điều hướng bằng bàn phím (ArrowUp, ArrowDown, Enter, Escape)
    function handleKeyNavigation(e, inputEl, container) {
        if (e.key === 'Escape') {
            closeDropdown(container);
            return;
        }

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
            const trimmed = query.trim();

            // 1. Xóa debounce timer cũ
            // 2. Hủy request đang chạy bằng AbortController
            // 3. Vô hiệu hóa kết quả cũ bằng generation ID
            cancelPendingSearch();
            isDropdownDismissed = false;

            // 4. Input dưới 2 ký tự phải xóa kết quả và đóng dropdown ngay
            if (trimmed.length < 2) {
                closeDropdown(searchDropdown);
                return;
            }

            const currentGen = searchGeneration;

            debounceTimer = setTimeout(() => {
                performSearch(trimmed, searchDropdown, currentGen);
            }, 300);
        });

        // Sự kiện phím điều hướng
        searchInput.addEventListener('keydown', function (e) {
            if (!searchDropdown.classList.contains('d-none') || e.key === 'Escape') {
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
            const trimmed = searchInput.value.trim();
            if (trimmed.length >= 2) {
                isDropdownDismissed = false;
                if (searchDropdown.children.length > 0) {
                    searchDropdown.classList.remove('d-none');
                } else {
                    cancelPendingSearch();
                    const currentGen = searchGeneration;
                    performSearch(trimmed, searchDropdown, currentGen);
                }
            }
        });
    });
})();
