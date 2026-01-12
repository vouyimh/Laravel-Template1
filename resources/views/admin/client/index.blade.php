@extends('layouts/contentNavbarLayout')

@section('title', 'Client')

@section('page-script')
<!-- Include jQuery and DataTables via CDN -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#clients-table').DataTable({
            
        });
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-6">
            <h1>List Client</h1>
            <div class="card-body">
                <!-- DataTable -->
                <table id="clients-table" class="display table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example static data -->
                        <tr>
                            <td>1</td>
                            <td>John Doe</td>
                            <td>john@example.com</td>
                            <td>2026-01-11</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jane Smith</td>
                            <td>jane@example.com</td>
                            <td>2026-01-10</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Robert Johnson</td>
                            <td>robert@example.com</td>
                            <td>2026-01-09</td>
                        </tr>
                        <!-- Add more static rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection