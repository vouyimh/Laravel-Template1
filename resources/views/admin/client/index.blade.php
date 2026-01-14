@extends('layouts/contentNavbarLayout')

@section('title', 'Client')

@section('script')

<!-- Include jQuery first -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.top {
    display: flex;
    justify-content: flex-end; /* top-right search */
    margin-bottom: 10px;
}

.bottom {
    display: flex;
    justify-content: space-between; /* page length left, pagination right */
    align-items: center;
    margin-top: 10px;
}
</style>
<script>
    $(document).ready(function() {
        $('#clients-table').DataTable({
            processing: true,
            ajax: {
                url: 'http://127.0.0.1:8000/api/clients',
                type: 'GET',
                dataSrc: 'data'  // <-- important!
            },

            aging: true,
            lengthChange: true,
            
            dom: '<"top"f>rt<"bottom"lip>',  // search top-right, page list bottom

            columns: [
                { data: 'company_name' },
                { data: 'owner_name' },
                { data: 'created_at' }
            ]
        });
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-6">
            <h1 class="text-center fw-bold mb-4">List Clients</h1>
            <div class="card-body">
                <!-- DataTable -->
                <table id="clients-table" class="display table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
              
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection