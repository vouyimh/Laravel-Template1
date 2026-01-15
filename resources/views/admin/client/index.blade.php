@extends('layouts/contentNavbarLayout')

@section('title', 'Client')

@section('script')

<!-- Include jQuery first -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

#viewClientModal table th {
    width: 30%;
    background-color: #f8f9fa;
}
#viewClientModal table td {
    width: 70%;
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
            
            language: {
                emptyTable: "No clients found"
            },

            columns: [
                { data: 'company_name' },
                { data: 'owner_name' },
                { data: 'created_at' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                                <i class="fa fa-eye text-primary view-client"
                                    style="cursor:pointer; margin-right:10px;"
                                    title="View"
                                    data-id="${row.client_id}">
                                </i>

                                <i class="fa fa-edit text-warning edit-client"
                                    style="cursor:pointer; margin-right:10px;"
                                    title="Edit"
                                    data-id="${row.client_id}">
                                </i>

                                <i class="fa fa-trash text-danger delete-client"
                                    style="cursor:pointer;"
                                    title="Delete"
                                    data-id="${row.client_id}">
                                </i>
                            `;
                    }
                }
            ]
        });

        $('#clients-table').on('click', '.view-client', function () {
            const clientId = $(this).data('id');

            $.get(`http://127.0.0.1:8000/api/client/${clientId}`, function(res) {
                const client = res.data;

                // Company Info
                $('#viewCompanyName').val(client.company_name || '');
                $('#viewOwnerName').val(client.owner_name || '');
                $('#viewEmail').val(client.email || '');
                $('#viewPhone').val(client.phone_number || '');
                $('#viewTax').prop('checked', client.tax || false);

                // Company Address
                if (client.company_address) {
                    $('#viewStreet').val(client.company_address.street_name || '');
                    $('#viewLocalCode').val(client.company_address.local_code || '');
                    $('#viewVillage').val(client.company_address.village || '');
                    $('#viewHouseNumber').val(client.company_address.house_number || '');
                }

                // Houses
                const housesContainer = $('#viewHousesContainer');
                housesContainer.empty();
                if (client.houses && client.houses.length > 0) {
                    client.houses.forEach((h, i) => {
                        const houseHTML = `
                        <div class="card mb-2 p-2 border border-secondary">
                            <h6>House #${i + 1}</h6>
                            <div class="mb-1"><strong>Street Name:</strong> ${h.street_name}</div>
                            <div class="mb-1"><strong>Local Code:</strong> ${h.local_code}</div>
                            <div class="mb-1"><strong>Village:</strong> ${h.village}</div>
                            <div class="mb-1"><strong>House Number:</strong> ${h.house_number}</div>
                        </div>
                        `;
                        housesContainer.append(houseHTML);
                    });
                } else {
                    housesContainer.html('<p>No houses found.</p>');
                }

                // Show the modal
                $('#viewClientModal').modal('show');
            });
        });


        $('#clients-table').on('click', '.edit-client', function () {
            const clientId = $(this).data('id');

            // Get client data from API
            $.get(`http://127.0.0.1:8000/api/client/${clientId}`, function(res) {
                const client = res.data;

                // Populate the edit form fields
                $('#editCompanyName').val(client.company_name || '');
                $('#editOwnerName').val(client.owner_name || '');
                $('#editEmail').val(client.email || '');
                $('#editPhone').val(client.phone_number || '');
                $('#editTax').prop('checked', client.tax || false);

                if (client.company_address) {
                    $('#editStreet').val(client.company_address.street_name || '');
                    $('#editLocalCode').val(client.company_address.local_code || '');
                    $('#editVillage').val(client.company_address.village || '');
                    $('#editHouseNumber').val(client.company_address.house_number || '');
                }

                // Store clientId in form for submission
                $('#editClientForm').data('client-id', clientId);

                // Show the modal
                $('#editClientModal').modal('show');
            });
        });

        $('#editClientForm').submit(function(e) {
            e.preventDefault();

            const clientId = $(this).data('client-id');
            const formData = {
                company_name: $('#editCompanyName').val(),
                owner_name: $('#editOwnerName').val(),
                email: $('#editEmail').val(),
                phone_number: $('#editPhone').val(),
                tax: $('#editTax').is(':checked') ? true : false,
                company_address: {
                    street_name: $('#editStreet').val(),
                    local_code: $('#editLocalCode').val(),
                    village: $('#editVillage').val(),
                    house_number: $('#editHouseNumber').val()
                }
            };

            $.ajax({
                url: `http://127.0.0.1:8000/api/edit-client/${clientId}`,
                type: 'PATCH', // make sure your API supports PUT method
                contentType: 'application/json',
                data: JSON.stringify(formData),

                success: function(res) {
                    alert(res.message || 'Client updated successfully');
                    $('#editClientModal').modal('hide');
                    $('#clients-table').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Update failed');
                }
            });
        });


        // ✅ DELETE CLIENT (THIS GOES IN THE VIEW)
        $('#clients-table').on('click', '.delete-client', function () {

            const clientId = $(this).data('id');

            if (!confirm('Are you sure you want to delete this client?')) return;

            $.ajax({
                url: `http://127.0.0.1:8000/api/delete-client/${clientId}`,
                type: 'DELETE',
                success: function (response) {
                    alert(response.message);
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Delete failed');
                }
            });
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
              
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Client Modal -->
<div class="modal fade" id="viewClientModal" tabindex="-1" aria-labelledby="viewClientLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title" id="viewClientLabel">Client Details</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form>
            @csrf
            <!-- Company Info -->
            <h4>Company Info</h4>
            <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" id="viewCompanyName" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Owner Name</label>
                <input type="text" class="form-control" id="viewOwnerName" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="viewEmail" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="viewPhone" readonly>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="viewTax" disabled>
                <label class="form-check-label" for="viewTax">Tax Registered</label>
            </div>

            <!-- Company Address -->
            <h4>Company Address</h4>
            <div class="mb-3">
                <label class="form-label">Street Name</label>
                <input type="text" class="form-control" id="viewStreet" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Local Code</label>
                <input type="text" class="form-control" id="viewLocalCode" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Village</label>
                <input type="text" class="form-control" id="viewVillage" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">House Number</label>
                <input type="text" class="form-control" id="viewHouseNumber" readonly>
            </div>

            <!-- Houses -->
            <h4>Houses</h4>
            <div id="viewHousesContainer">
                <!-- Dynamic houses will appear here -->
            </div>

            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title" id="editClientLabel">Edit Client</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="editClientForm">
            @csrf
            <!-- Company Info -->
            <h4>Company Info</h4>
            <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" id="editCompanyName" name="company_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Owner Name</label>
                <input type="text" class="form-control" id="editOwnerName" name="owner_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="editPhone" name="phone_number">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="editTax" name="tax">
                <label class="form-check-label" for="editTax">Tax Registered</label>
            </div>

            <!-- Company Address -->
            <h4>Company Address</h4>
            <div class="mb-3">
                <label class="form-label">Street Name</label>
                <input type="text" class="form-control" id="editStreet" name="street_name">
            </div>
            <div class="mb-3">
                <label class="form-label">Local Code</label>
                <input type="text" class="form-control" id="editLocalCode" name="local_code">
            </div>
            <div class="mb-3">
                <label class="form-label">Village</label>
                <input type="text" class="form-control" id="editVillage" name="village">
            </div>
            <div class="mb-3">
                <label class="form-label">House Number</label>
                <input type="text" class="form-control" id="editHouseNumber" name="house_number">
            </div>

            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection