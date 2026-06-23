{{-- Hiển thị ngày dạng dd/mm/yyyy nhưng vẫn gửi Y-m-d cho backend (flatpickr, CDN) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.flatpickr) return;
        flatpickr('input[type=date]', {
            dateFormat: 'Y-m-d',   // giá trị gửi lên server
            altInput: true,        // ô hiển thị riêng
            altFormat: 'd/m/Y',    // hiển thị dd/mm/yyyy
            allowInput: true,
        });
    });
</script>
