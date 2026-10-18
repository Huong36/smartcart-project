@extends('client')
@section('title', 'Tìm sản phẩm bằng ảnh')
@section('content')
<div class="image-search-heading"><h1>Tìm sản phẩm bằng ảnh</h1><p>Chụp một món đồ hoặc chọn ảnh để tìm sản phẩm tương tự trong cửa hàng.</p></div>
<div class="image-search-layout" id="image-search-app">
    <form id="image-search-form" action="{{ route('search.image.submit') }}" method="POST" enctype="multipart/form-data" class="image-search-upload">
        @csrf
        <div class="image-search-preview"><img id="image-query-preview" alt="Ảnh bạn muốn tìm" hidden><span id="image-preview-placeholder">Chưa chọn ảnh</span></div>
        <input id="image-search-file" name="image" type="file" accept="image/jpeg,image/png,image/webp" hidden aria-label="Chọn ảnh sản phẩm">
        <input id="image-camera-file" type="file" accept="image/*" capture="environment" hidden aria-label="Chụp ảnh sản phẩm">
        <div class="image-source-buttons"><button type="button" class="btn btn-outline-secondary" id="choose-image">Chọn ảnh</button><button type="button" class="btn btn-outline-secondary" id="open-camera">Chụp ảnh</button></div>
        <div id="image-camera-panel" hidden><video id="image-camera-video" autoplay playsinline muted aria-label="Hình ảnh từ camera"></video><div class="image-source-buttons"><button type="button" class="btn btn-primary" id="capture-image">Chụp</button><button type="button" class="btn btn-outline-secondary" id="close-camera">Đóng camera</button></div></div>
        <p class="image-search-hint">JPG, PNG, WebP · Tối đa 2 MB.<br>Ảnh rõ, một món đồ và nền đơn giản sẽ dễ tìm hơn.</p>
        <button class="btn btn-primary w-100" type="submit" id="find-by-image" disabled>Tìm sản phẩm</button>
        <p class="image-search-privacy">Ảnh tìm kiếm chỉ được xử lý để so sánh, không lưu vào tài khoản.</p>
    </form>
    <section class="image-search-results" aria-labelledby="image-results-title" aria-busy="false">
        <h2 id="image-results-title">Sản phẩm tương tự</h2>
        <p id="image-search-status" role="status" aria-live="polite">Chọn ảnh để bắt đầu tìm kiếm.</p>
        <div id="image-search-error" role="alert" hidden></div>
        <div class="row g-3" id="image-search-results"></div>
    </section>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/image-search.js') }}" defer></script>
@endpush
