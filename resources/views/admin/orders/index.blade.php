@extends('admin.layouts.master')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Hyper</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">eCommerce</a></li>
                    <li class="breadcrumb-item active">Goods Receipt</li>
                </ol>
            </div>
            <h4 class="page-title">Goods Receipt</h4>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-sm-4">
                        <a href="{{ route('admin.GoodsReceipt.create') }}" class="btn btn-danger mb-2">
                            <i class="mdi mdi-plus-circle mr-2"></i> Add Goods Receipt
                        </a>
                    </div>
                </div>
                {{-- table --}}
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col -->
</div>
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-end" id="pagination">
    </ul>
</nav>

<!-- Modal for receipt detail -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">Receipt Detail</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-centered mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-modal">
                    </tbody>
                </table>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection

@push('css')
<link href="{{ asset('assets_admin/css/vendor/swal2.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('js')
<script src="{{ asset('assets_admin/js/vendor/sweetalert2.js') }}"></script>
<script>
    $(document).ready(function() {
        const paginationContainer = $('#pagination');
        let currentPage = 1;

        // Function to fetch goods receipt data with pagination
        function fetchData(page) {
            $.ajax({
                url: "{{ route('api.GoodsReceipt.index') }}" + `?page=${page}`,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    updateUI(data);
                    updatePagination(data);
                },
                error: function(error) {
                    alert(error);
                }
            });
            const newUrl = window.location.pathname + `?page=${page}`;
            history.pushState({
                page: page
            }, null, newUrl);
        }

        // Update the table with the data from API
        function updateUI(data) {
            $('tbody').empty();
            $.each(data.data, function(i, v) {
                $('tbody').append('<tr>')
                    .append(`<td>${v.id}</td>`)
                    .append(`<td>${v.provider.name}</td>`)
                    .append(`<td>${v.date}</td>`)
                    .append(`<td>${v.sum}</td>`)
                    .append(
                        `<td class="table-action">
                            <a href="##" class="action-icon btn-detail" data-id="${v.id}">
                                <i class="mdi mdi-eye-outline"></i>
                            </a>
                        </td>`
                    ).append('</tr>');
            });
        }

        // Update the pagination UI
        function updatePagination(data) {
            paginationContainer.empty();
            if (data.last_page < currentPage) {
                currentPage = data.last_page;
            }
            const prevPageLink = $('<li class="page-item">').append($('<a class="page-link" href="##" aria-label="Previous">').html('&laquo;'));
            prevPageLink.click(function(e) {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage--;
                    fetchData(currentPage);
                }
            });

            paginationContainer.append(prevPageLink);
            for (let i = 1; i <= data.last_page; i++) {
                const pageLink = $('<li class="page-item">').append($('<a class="page-link" href="##">').text(i));
                if (i === currentPage) {
                    pageLink.addClass('active');
                }
                pageLink.click(function(e) {
                    e.preventDefault();
                    currentPage = i;
                    fetchData(currentPage);
                });
                paginationContainer.append(pageLink);
            }

            const nextPageLink = $('<li class="page-item">').append($('<a class="page-link" href="##" aria-label="Next">').html('&raquo;'));
            nextPageLink.click(function(e) {
                e.preventDefault();
                if (currentPage < data.last_page) {
                    currentPage++;
                    fetchData(currentPage);
                }
            });
            paginationContainer.append(nextPageLink);
        }

        // Event listener for the detail button
        $('tbody').on('click', '.btn-detail', function(e) {
            e.preventDefault();
            var dataId = $(this).data('id');
            $.ajax({
                type: "GET",
                url: `/api/ReceiptDetail/${dataId}`,
                success: function(response) {
                    updateModal(response);
                },
                error: function(error) {
                    alert("Error fetching receipt details.");
                }
            });
        });

        function updateModal(data) {
            $('#tbody-modal').empty();
            if (data.length > 0) {
                $.each(data, function(i, v) {
                    $('#tbody-modal').append('<tr>')
                        .append(`<td>${v.product.name}</td>`)
                        .append(`<td>${v.amount}</td>`)
                        .append(`<td>${v.price}</td>`)
                        .append('</tr>');
                });
            } else {
                $('#tbody-modal').append('<tr><td colspan="3">No details available</td></tr>');
            }
            $('#modal-detail').modal('show');
        }

        // Fetch data on page load
        fetchData(currentPage);
    });
</script>
@endpush