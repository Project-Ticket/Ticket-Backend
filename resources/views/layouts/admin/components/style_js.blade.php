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

<!-- Page JS -->
<script src="{{ url('/admin') }}/js/dashboards-analytics.js"></script>

<script>
    $(document).on('click', '.open-global-modal', function(e) {
        e.preventDefault();

        const url = $(this).data('url');
        const title = $(this).data('title') || 'Modal Title';

        $('#globalModalTitle').text(title);
        $('#globalModalBody').html('<div class="text-center">Loading...</div>');
        $('#globalModalFooter').html(
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#globalModalBody').html(response.body || response);
                if (response.footer) {
                    $('#globalModalFooter').html(response.footer);
                }
                $('#globalModal').modal('show');
            },
            error: function(xhr) {
                $('#globalModalBody').html(
                    '<div class="alert alert-danger">Failed to load content.</div>');
            }
        });
    });
</script>
