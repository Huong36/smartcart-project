@if(session('success'))
    <div class="toast-container flash-toast-container">
        <div class="toast flash-toast" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex align-items-center">
                <div class="toast-body">{{ session('success') }}</div>
                <button type="button" class="btn-close me-3" data-bs-dismiss="toast" aria-label="Đóng thông báo"></button>
            </div>
        </div>
    </div>
@endif
