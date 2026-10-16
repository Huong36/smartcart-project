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

    // Hiển thị thông báo bằng SweetAlert2 hoặc Toast fallback
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
                title: message
            });
        } else {
            alert(message);
        }
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

        // SweetAlert2 xác nhận trước khi xóa
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: 'Xác nhận xóa?',
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
                title: 'Làm trống giỏ hàng?',
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

    // Hook thông báo đặt hàng thành công (dành cho module Checkout của Thành viên 3)
    window.SmartCartNotifyOrderSuccess = function (orderId, orderTotal) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Đặt hàng thành công! 🎉',
                html: `<p>Mã đơn hàng của bạn: <strong>#${orderId || ''}</strong></p>
                       <p>Cảm ơn bạn đã tin tưởng và mua sắm tại <strong>SmartCart</strong>. Đơn hàng đang được xử lý!</p>`,
                icon: 'success',
                confirmButtonColor: '#08796a',
                confirmButtonText: 'Xem đơn hàng của tôi'
            }).then(() => {
                window.location.href = '/account';
            });
        } else {
            alert('Đặt hàng thành công! Mã đơn: #' + orderId);
        }
    };
})();
