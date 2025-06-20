<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ url('/admin') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ url('/admin') }}/vendor/js/bootstrap.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/node-waves/node-waves.js"></script>

<script src="{{ url('/admin') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/i18n/i18n.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/typeahead-js/typeahead.js"></script>

<script src="{{ url('/admin') }}/vendor/js/menu.js"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ url('/admin') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/swiper/swiper.js"></script>
<script src="{{ url('/admin') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

<!-- Main JS -->
<script src="{{ url('/admin') }}/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    // Modal handler
    $(document).on('click', '.open-global-modal', function (e) {
        e.preventDefault();

        let url = $(this).data('url');
        let title = $(this).data('title') ?? 'Loading...';

        $('#globalModal').modal('show');
        $('#modal-content-container').html(`
        <div class="modal-body text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                $('#modal-content-container').html(response);
            },
            error: function () {
                $('#modal-content-container').html(`
                <div class="modal-body">
                    <div class="alert alert-danger">Gagal memuat data. Coba lagi nanti.</div>
                </div>
            `);
            }
        });
    });

    // Offcanvas handler
    $(document).on('click', '.open-global-offcanvas', function (e) {
        e.preventDefault();

        let url = $(this).data('url');
        let title = $(this).data('title') ?? 'Loading...';

        $('#globalOffcanvas').offcanvas?.dispose?.(); // Reset jika sebelumnya pernah dipakai

        $('#offcanvas-content-container').html(`
        <div class="offcanvas-body text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

        const offcanvasInstance = new bootstrap.Offcanvas('#globalOffcanvas');
        offcanvasInstance.show();

        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                $('#offcanvas-content-container').html(response);
            },
            error: function () {
                $('#offcanvas-content-container').html(`
                <div class="offcanvas-body">
                    <div class="alert alert-danger">Gagal memuat data. Coba lagi nanti.</div>
                </div>
            `);
            }
        });
    });
</script>

{{--
<script>
    $(document).ready(function () {
        function loadOptions(type, parentId, target) {
            $.get('/wilayah-dropdown', { type: type, parent: parentId }, function (data) {
                let html = '<option value="">Pilih</option>';
                data.forEach(item => html += `<option value="${item.name}">${item.name}</option>`);
                $(target).html(html).prop('disabled', false);
            });
        }

        // load province
        loadOptions('province', null, '#province');

        $('#province').change(function () {
            const id = $(this).find('option:selected').index();
            $('#regency, #district, #village').html('<option value="">Pilih</option>').prop('disabled', true);
            if (id > 0) loadOptions('regency', id, '#regency');
        });

        $('#regency').change(function () {
            const id = $(this).find('option:selected').index();
            $('#district, #village').html('<option value="">Pilih</option>').prop('disabled', true);
            if (id > 0) loadOptions('district', id, '#district');
        });

        $('#district').change(function () {
            const id = $(this).find('option:selected').index();
            $('#village').html('<option value="">Pilih</option>').prop('disabled', true);
            if (id > 0) loadOptions('village', id, '#village');
        });
    });
</script> --}}