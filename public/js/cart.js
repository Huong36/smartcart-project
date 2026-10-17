/**
 * SmartCart - Shopping Cart Module (Session & Fetch API)
 * Phụ trách: Thành viên 4 (nongvanminh)
 */

(function () {
    'use strict';

    // Lấy CSRF token từ thẻ meta
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Cập nhật số lượng trên Navbar Badge
    function updateNavbarBadge(count) {
        const badge = document.getElementById('navbar-cart-badge');
        if (!badge) return;

        count = parseInt(count, 10) || 0;
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    // Hiển thị thông báo bằng SweetAlert2 hoặc Toast fallback (chống XSS: dùng titleText)
    function showNotification(message, icon = 'success') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            Toast.fire({
                icon: icon,
                titleText: message
            });
        } else {
            alert(message);
        }
    }

    // Kiểm tra xem hiện tại người dùng có đang ở trang giỏ hàng không
    function isCartPage() {
        return !!(document.getElementById('cart-table') || document.getElementById('cart-empty-state'));
    }

    // Tạo phần tử tr cho từng dòng sản phẩm trong bảng giỏ hàng (an toàn tuyệt đối chống XSS)
    function createCartRowElement(item) {
        const tr = document.createElement('tr');
        tr.id = 'cart-row-' + item.product_id;
        tr.className = 'cart-item-row';
        tr.dataset.productId = item.product_id;

        // Cột 1: Thông tin sản phẩm (Hình ảnh, danh mục, tên, cảnh báo tồn kho)
        const tdProduct = document.createElement('td');
        const flexBox = document.createElement('div');
        flexBox.className = 'd-flex align-items-center gap-3';

        if (item.product && item.image_url) {
            const img = document.createElement('img');
            img.src = item.image_url;
            img.alt = item.name || '';
            img.className = 'rounded-3 border';
            img.style.cssText = 'width: 60px; height: 60px; object-fit: contain; background: #f8faf9;';
            flexBox.appendChild(img);
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'rounded-3 border bg-light d-flex align-items-center justify-content-center text-muted';
            placeholder.style.cssText = 'width: 60px; height: 60px;';
            placeholder.textContent = '⚠️';
            flexBox.appendChild(placeholder);
        }

        const infoBox = document.createElement('div');
        const categoryP = document.createElement('p');
        categoryP.className = 'small text-muted mb-0';
        categoryP.textContent = item.category_name || 'SmartCart';

        const nameH2 = document.createElement('h2');
        nameH2.className = 'h6 fw-semibold mb-1 item-product-name';
        nameH2.textContent = item.name || ''; // Dùng textContent an toàn với mọi HTML injection

        infoBox.appendChild(categoryP);
        infoBox.appendChild(nameH2);

        if (item.stock_warning) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle small';
            badge.textContent = item.stock_warning;
            infoBox.appendChild(badge);
        } else {
            const stockSpan = document.createElement('span');
            stockSpan.className = 'small text-success';
            stockSpan.textContent = 'Còn ' + item.stock + ' sản phẩm';
            infoBox.appendChild(stockSpan);
        }

        flexBox.appendChild(infoBox);
        tdProduct.appendChild(flexBox);
        tr.appendChild(tdProduct);

        // Cột 2: Đơn giá
        const tdPrice = document.createElement('td');
        tdPrice.className = 'text-center fw-medium';
        tdPrice.textContent = item.price_formatted || '0 ₫';
        tr.appendChild(tdPrice);

        // Cột 3: Số lượng (+/- và input)
        const tdQty = document.createElement('td');
        if (item.product) {
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group input-group-sm justify-content-center mx-auto';
            inputGroup.style.maxWidth = '120px';

            const minusBtn = document.createElement('button');
            minusBtn.className = 'btn btn-outline-secondary btn-qty-minus';
            minusBtn.type = 'button';
            minusBtn.setAttribute('aria-label', 'Giảm số lượng');
            minusBtn.dataset.productId = item.product_id;
            minusBtn.textContent = '−';
            if (item.quantity <= 1) minusBtn.disabled = true;

            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'form-control text-center input-qty px-1';
            input.value = item.quantity;
            input.min = '1';
            input.max = String(item.stock > 0 ? item.stock : 1);
            input.dataset.productId = item.product_id;
            input.dataset.originalVal = item.quantity;
            input.setAttribute('aria-label', 'Số lượng sản phẩm');

            const plusBtn = document.createElement('button');
            plusBtn.className = 'btn btn-outline-secondary btn-qty-plus';
            plusBtn.type = 'button';
            plusBtn.setAttribute('aria-label', 'Tăng số lượng');
            plusBtn.dataset.productId = item.product_id;
            plusBtn.textContent = '+';
            if (item.quantity >= item.stock) plusBtn.disabled = true;

            inputGroup.appendChild(minusBtn);
            inputGroup.appendChild(input);
            inputGroup.appendChild(plusBtn);
            tdQty.appendChild(inputGroup);
        } else {
            const naSpan = document.createElement('span');
            naSpan.className = 'text-muted small';
            naSpan.textContent = 'Không khả dụng';
            tdQty.appendChild(naSpan);
        }
        tr.appendChild(tdQty);

        // Cột 4: Thành tiền
        const tdSubtotal = document.createElement('td');
        tdSubtotal.className = 'text-end fw-bold price item-subtotal';
        tdSubtotal.id = 'subtotal-' + item.product_id;
        tdSubtotal.textContent = item.subtotal_formatted || '0 ₫';
        tr.appendChild(tdSubtotal);

        // Cột 5: Nút xóa
        const tdRemove = document.createElement('td');
        tdRemove.className = 'text-center';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-link text-danger p-0 btn-remove-item';
        removeBtn.dataset.productId = item.product_id;
        removeBtn.dataset.productName = item.name || '';
        removeBtn.title = 'Xóa khỏi giỏ hàng';
        removeBtn.setAttribute('aria-label', 'Xóa khỏi giỏ hàng');
        removeBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
            </svg>
        `;
        tdRemove.appendChild(removeBtn);
        tr.appendChild(tdRemove);

        return tr;
    }

    // Render lại toàn bộ giao diện giỏ hàng từ dữ liệu server trả về
    function renderCartPage(cartData) {
        const emptyState = document.getElementById('cart-empty-state');
        const contentWrapper = document.getElementById('cart-content-wrapper');
        const table = document.getElementById('cart-table');

        if (!emptyState && !contentWrapper && !table) {
            return; // Không ở trang giỏ hàng
        }

        const items = cartData.items || [];
        const isEmpty = cartData.is_empty || items.length === 0;

        if (isEmpty) {
            if (emptyState) emptyState.classList.remove('d-none');
            if (contentWrapper) contentWrapper.classList.add('d-none');
        } else {
            if (emptyState) emptyState.classList.add('d-none');
            if (contentWrapper) contentWrapper.classList.remove('d-none');

            if (table) {
                const tbody = table.querySelector('tbody');
                if (tbody) {
                    tbody.innerHTML = '';
                    items.forEach((item) => {
                        tbody.appendChild(createCartRowElement(item));
                    });
                }
            }
        }

        // Cập nhật tổng số lượng và tổng tiền từ server tính toán
        const totalEl = document.getElementById('cart-summary-total');
        const finalEl = document.getElementById('cart-summary-final');
        const qtyEl = document.getElementById('cart-summary-qty');

        if (totalEl) totalEl.textContent = cartData.total_price;
        if (finalEl) finalEl.textContent = cartData.total_price;
        if (qtyEl) qtyEl.textContent = cartData.total_qty;

        updateNavbarBadge(cartData.total_qty);
    }

    // Tải và đồng bộ toàn bộ dữ liệu giỏ hàng từ server
    async function syncCartPage() {
        const response = await fetch('/cart', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`Cart sync HTTP status: ${response.status}`);
        }

        const data = await response.json();
        if (!data || !data.success) {
            throw new Error((data && data.message) || 'Dữ liệu giỏ hàng không hợp lệ.');
        }

        renderCartPage(data);
    }

    // Thêm sản phẩm vào giỏ hàng
    async function addToCart(productId, quantity = 1, triggerBtn = null) {
        if (!productId) return;

        let originalContent = '';
        if (triggerBtn) {
            originalContent = triggerBtn.innerHTML;
            triggerBtn.disabled = true;
            triggerBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        }

        try {
            const response = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                updateNavbarBadge(data.cart_count);
                showNotification(data.message || 'Đã thêm vào giỏ hàng!', 'success');

                // Nếu đang ở trang giỏ hàng, đồng bộ lại toàn bộ giao diện với dữ liệu server
                if (isCartPage()) {
                    try {
                        await syncCartPage();
                    } catch (syncErr) {
                        console.error('Cart sync error after add:', syncErr);
                        showNotification('Đã thêm sản phẩm vào giỏ, nhưng chưa tải lại được trang giỏ hàng. Vui lòng làm mới trang.', 'warning');
                    }
                }
            } else {
                const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Không thể thêm vào giỏ.');
                showNotification(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Cart Add Error:', error);
            showNotification('Lỗi kết nối mạng, vui lòng thử lại sau.', 'error');
        } finally {
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.innerHTML = originalContent;
            }
        }
    }

    // Cập nhật số lượng sản phẩm trong trang giỏ hàng
    async function updateQuantity(productId, quantity, inputEl = null) {
        if (!productId || quantity < 1) return;

        if (inputEl) {
            inputEl.disabled = true;
        }

        try {
            const response = await fetch('/cart/update', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Cập nhật DOM của dòng tương ứng
                const subtotalEl = document.getElementById('subtotal-' + productId);
                if (subtotalEl) {
                    subtotalEl.textContent = data.item_subtotal;
                }

                // Cập nhật tổng giỏ hàng
                const totalEl = document.getElementById('cart-summary-total');
                const finalEl = document.getElementById('cart-summary-final');
                const qtyEl = document.getElementById('cart-summary-qty');

                if (totalEl) totalEl.textContent = data.total_price;
                if (finalEl) finalEl.textContent = data.total_price;
                if (qtyEl) qtyEl.textContent = data.cart_count;

                updateNavbarBadge(data.cart_count);

                // Cập nhật lại input value và state của nút + -
                if (inputEl) {
                    inputEl.value = quantity;
                    inputEl.dataset.originalVal = quantity;

                    const row = inputEl.closest('tr');
                    if (row) {
                        const minusBtn = row.querySelector('.btn-qty-minus');
                        const plusBtn = row.querySelector('.btn-qty-plus');
                        const maxStock = parseInt(inputEl.getAttribute('max'), 10) || 999;

                        if (minusBtn) minusBtn.disabled = (quantity <= 1);
                        if (plusBtn) plusBtn.disabled = (quantity >= maxStock);
                    }
                }
            } else {
                const errorMsg = data.message || 'Cập nhật thất bại.';
                showNotification(errorMsg, 'error');

                // Khôi phục giá trị cũ
                if (inputEl && inputEl.dataset.originalVal) {
                    inputEl.value = inputEl.dataset.originalVal;
                }
            }
        } catch (error) {
            console.error('Cart Update Error:', error);
            showNotification('Lỗi kết nối khi cập nhật số lượng.', 'error');
            if (inputEl && inputEl.dataset.originalVal) {
                inputEl.value = inputEl.dataset.originalVal;
            }
        } finally {
            if (inputEl) {
                inputEl.disabled = false;
            }
        }
    }

    // Xóa sản phẩm khỏi giỏ hàng
    async function removeItem(productId, productName) {
        if (!productId) return;

        // SweetAlert2 xác nhận trước khi xóa (titleText chống XSS)
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                titleText: 'Xác nhận xóa?',
                text: productName ? `Bạn muốn bỏ "${productName}" khỏi giỏ hàng?` : 'Bạn có chắc chắn muốn xóa sản phẩm này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Giữ lại',
                reverseButtons: true
            });

            if (!result.isConfirmed) {
                return; // Người dùng bấm hủy
            }
        } else {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ?')) {
                return;
            }
        }

        try {
            const response = await fetch('/cart/remove', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ product_id: productId })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Xóa hàng trong bảng
                const row = document.getElementById('cart-row-' + productId);
                if (row) {
                    row.remove();
                }

                // Cập nhật tổng tiền và badge
                const totalEl = document.getElementById('cart-summary-total');
                const finalEl = document.getElementById('cart-summary-final');
                const qtyEl = document.getElementById('cart-summary-qty');

                if (totalEl) totalEl.textContent = data.total_price;
                if (finalEl) finalEl.textContent = data.total_price;
                if (qtyEl) qtyEl.textContent = data.cart_count;

                updateNavbarBadge(data.cart_count);
                showNotification('Đã xóa sản phẩm khỏi giỏ hàng.', 'success');

                // Nếu giỏ rỗng, chuyển sang empty state
                if (data.is_empty) {
                    const emptyState = document.getElementById('cart-empty-state');
                    const contentWrapper = document.getElementById('cart-content-wrapper');
                    if (emptyState) emptyState.classList.remove('d-none');
                    if (contentWrapper) contentWrapper.classList.add('d-none');
                }
            } else {
                showNotification(data.message || 'Không thể xóa sản phẩm.', 'error');
            }
        } catch (error) {
            console.error('Cart Remove Error:', error);
            showNotification('Lỗi kết nối khi xóa sản phẩm.', 'error');
        }
    }

    // Xóa sạch giỏ hàng
    async function clearCart() {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                titleText: 'Làm trống giỏ hàng?',
                text: 'Tất cả sản phẩm trong giỏ hàng sẽ bị xóa!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xóa toàn bộ',
                cancelButtonText: 'Hủy'
            });

            if (!result.isConfirmed) return;
        } else {
            if (!confirm('Bạn có chắc muốn xóa sạch giỏ hàng?')) return;
        }

        try {
            const response = await fetch('/cart/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            const data = await response.json();
            if (response.ok && data.success) {
                updateNavbarBadge(0);
                const emptyState = document.getElementById('cart-empty-state');
                const contentWrapper = document.getElementById('cart-content-wrapper');
                if (emptyState) emptyState.classList.remove('d-none');
                if (contentWrapper) contentWrapper.classList.add('d-none');
                showNotification('Đã làm trống giỏ hàng.', 'success');
            }
        } catch (error) {
            console.error('Clear Cart Error:', error);
            showNotification('Lỗi kết nối khi xóa giỏ hàng.', 'error');
        }
    }

    // Gắn sự kiện (Delegation)
    document.addEventListener('DOMContentLoaded', function () {
        // Sự kiện click Thêm vào giỏ trên toàn trang (Nút có class .btn-add-to-cart)
        document.body.addEventListener('click', function (e) {
            const addBtn = e.target.closest('.btn-add-to-cart');
            if (addBtn) {
                e.preventDefault();
                const productId = addBtn.dataset.productId;
                const qtyInput = addBtn.dataset.qtyInput ? document.querySelector(addBtn.dataset.qtyInput) : null;
                const quantity = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
                addToCart(productId, quantity, addBtn);
                return;
            }

            // Nút giảm số lượng trong giỏ
            const minusBtn = e.target.closest('.btn-qty-minus');
            if (minusBtn) {
                e.preventDefault();
                const productId = minusBtn.dataset.productId;
                const row = minusBtn.closest('tr');
                const inputEl = row ? row.querySelector('.input-qty') : null;
                if (inputEl) {
                    let currentVal = parseInt(inputEl.value, 10) || 1;
                    if (currentVal > 1) {
                        updateQuantity(productId, currentVal - 1, inputEl);
                    }
                }
                return;
            }

            // Nút tăng số lượng trong giỏ
            const plusBtn = e.target.closest('.btn-qty-plus');
            if (plusBtn) {
                e.preventDefault();
                const productId = plusBtn.dataset.productId;
                const row = plusBtn.closest('tr');
                const inputEl = row ? row.querySelector('.input-qty') : null;
                if (inputEl) {
                    let currentVal = parseInt(inputEl.value, 10) || 1;
                    let maxStock = parseInt(inputEl.getAttribute('max'), 10) || 999;
                    if (currentVal < maxStock) {
                        updateQuantity(productId, currentVal + 1, inputEl);
                    } else {
                        showNotification('Đã đạt số lượng tồn kho tối đa.', 'warning');
                    }
                }
                return;
            }

            // Nút xóa từng món
            const removeBtn = e.target.closest('.btn-remove-item');
            if (removeBtn) {
                e.preventDefault();
                const productId = removeBtn.dataset.productId;
                const productName = removeBtn.dataset.productName || '';
                removeItem(productId, productName);
                return;
            }

            // Nút xóa toàn bộ giỏ
            const clearBtn = e.target.closest('#btn-clear-cart');
            if (clearBtn) {
                e.preventDefault();
                clearCart();
                return;
            }
        });

        // Sự kiện thay đổi ô input số lượng trực tiếp
        document.body.addEventListener('change', function (e) {
            const inputEl = e.target.closest('.input-qty');
            if (inputEl) {
                const productId = inputEl.dataset.productId;
                let val = parseInt(inputEl.value, 10);
                const maxStock = parseInt(inputEl.getAttribute('max'), 10) || 999;

                if (isNaN(val) || val < 1) {
                    val = 1;
                } else if (val > maxStock) {
                    val = maxStock;
                    showNotification('Đã điều chỉnh về số lượng tối đa trong kho (' + maxStock + ').', 'warning');
                }
                updateQuantity(productId, val, inputEl);
            }
        });
    });

    // Xuất ra global scope cho các module khác hoặc layout sử dụng
    window.SmartCart = window.SmartCart || {};
    window.SmartCart.addToCart = addToCart;
    window.SmartCart.updateNavbarBadge = updateNavbarBadge;
    window.SmartCart.showNotification = showNotification;
    window.SmartCart.syncCartPage = syncCartPage;
    window.SmartCart.renderCartPage = renderCartPage;
    window.SmartCart.isCartPage = isCartPage;

    // Hook thông báo đặt hàng thành công (dành cho module Checkout của Thành viên 3)
    window.SmartCartNotifyOrderSuccess = function (orderId, orderTotal) {
        if (typeof Swal !== 'undefined') {
            const safeOrderId = String(orderId || '').replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
            Swal.fire({
                titleText: 'Đặt hàng thành công! 🎉',
                html: `<p>Mã đơn hàng của bạn: <strong>#${safeOrderId}</strong></p>
                       <p>Cảm ơn bạn đã tin tưởng và mua sắm tại <strong>SmartCart</strong>. Đơn hàng đang được xử lý!</p>`,
                icon: 'success',
                confirmButtonColor: '#08796a',
                confirmButtonText: 'Xem đơn hàng của tôi'
            }).then(() => {
                window.location.href = '/orders/' + encodeURIComponent(orderId);
            });
        } else {
            alert('Đặt hàng thành công! Mã đơn: #' + orderId);
        }
    };
})();
