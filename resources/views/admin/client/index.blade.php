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
        justify-content: flex-end;
        /* top-right search */
        margin-bottom: 10px;
    }

    .bottom {
        display: flex;
        justify-content: space-between;
        /* page length left, pagination right */
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

    .address-suggestions {
        max-height: 220px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        z-index: 2000;
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
    }

    .address-suggestions .item {
        padding: 8px 12px;
        cursor: pointer;
    }

    .address-suggestions .item:hover {
        background: #f5f6f8;
    }

</style>
<script>
    $(document).ready(function() {
        $('#clients-table').DataTable({
            processing: true,
            ajax: {
                url: '/api/clients',
                type: 'GET',
                dataSrc: 'data' // <-- important!
            },

            aging: true,
            lengthChange: true,

            dom: '<"top"f>rt<"bottom"lip>', // search top-right, page list bottom

            language: {
                emptyTable: "No clients found"
            },

            columns: [{
                    data: 'company_name'
                },
                {
                    data: 'owner_name'
                },
                {
                    data: 'email'
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        if (!data) return '';
                        return data.split('T')[0]; // ✅ 2026-01-16
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
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

        $('#clients-table').on('click', '.view-client', function() {
            const clientId = $(this).data('id');

            $.get(`/api/client/${clientId}`, function(res) {
                const client = res.data;

                // Company Info
                $('#viewCompanyName').val(client.company_name || '');
                $('#viewOwnerName').val(client.owner_name || '');
                $('#viewEmail').val(client.email || '');
                $('#viewPhone').val(client.phone_number || '');
                $('#viewTax').prop('checked', client.tax || false);
                $('#viewLockbox').val(client.lockbox || '');
                $('#viewCompanyType').val(client.company_type || '');

                // Company Address
                $('#viewCompanyAddress').val(client.company_address || '');

                // Houses
                const housesContainer = $('#viewHousesContainer');
                housesContainer.empty();
                if (client.houses && client.houses.length > 0) {
                    client.houses.forEach((h, i) => {
                        const houseHTML = `
                        <div class="card mb-2 p-2 border border-secondary">
                            <h6>{{ __('House') }} #${i + 1}</h6>
                            <div class="mb-1"><strong>{{ __('Address') }}:</strong> ${h.house_address}</div>
                            <div class="mb-1"><strong>{{ __('Total Number of Rooms') }}:</strong> ${h.room}</div>
                            <div class="mb-1"><strong>{{ __('Size') }}:</strong> ${h.size}</div>
                            <div class="mb-1"><strong>{{ __('Total Time for Cleaning') }}:</strong> ${h.time}</div>
                            <div class="mb-1"><strong>{{ __('Total Tools') }}:</strong> ${h.tools}</div>
                            <div class="mb-1"><strong>{{ __('Task To Do') }}:</strong> ${h.tasks}</div>
                        </div>
                        `;
                        housesContainer.append(houseHTML);
                    });
                } else {
                    housesContainer.html('<p>{{ __('No houses found.') }}</p>');
                }

                // Show the modal
                $('#viewClientModal').modal('show');
            });
        });

        $('#clients-table').on('click', '.edit-client', function() {
            const clientId = $(this).data('id');

            $.get(`/api/client/${clientId}`, function(res) {

                const client = res.data;

                // Populate the edit form fields
                $('#editClientId').val(client.client_id);
                $('#editCompanyName').val(client.company_name || '');
                $('#editOwnerName').val(client.owner_name || '');
                $('#editEmail').val(client.email || '');
                $('#editPhone').val(client.phone_number || '');
                $('#editTax').prop('checked', client.tax || false);
                $('#editLockbox').val(client.lockbox || '');
                $('#editCompanyType').val(client.company_type || '');
                $('#editCompanyAddress').val(client.company_address || '');

                // Reset houses
                $('#edit-houses-container').html('');

                // Load existing houses
                if (client.houses && client.houses.length) {
                    client.houses.forEach(house => {
                        addEditHouse(house);
                    });
                }

                $('#editClientModal').modal('show');
            });
        });

        $('#editClientForm').submit(function(e) {
            e.preventDefault();

            const clientId = $('#editClientId').val();
            const houses = [];

            $('#edit-houses-container .house-item').each(function() {
                houses.push({
                    id: $(this).find('.house-id').val() || null,
                    house_address: $(this).find('.house_address').val(),
                    room: $(this).find('.room').val(),
                    size: $(this).find('.size').val(),
                    time: $(this).find('.time').val(),
                    tools: $(this).find('.tools').val(),
                    tasks: $(this).find('.tasks').val(),
                });
            });

            const formData = {
                company_name: $('#editCompanyName').val(),
                owner_name: $('#editOwnerName').val(),
                email: $('#editEmail').val(),
                phone_number: $('#editPhone').val(),
                tax: $('#editTax').is(':checked') ? true : false,
                lockbox: $('#editLockbox').val(),
                company_type: $('#editCompanyType').val(),
                company_address:  $('#editCompanyAddress').val(),
                houses: houses
            };

            $.ajax({
                url: `/api/edit-client/${clientId}`,
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
        $('#clients-table').on('click', '.delete-client', function() {

            const clientId = $(this).data('id');

            if (!confirm('Are you sure you want to delete this client?')) return;

            $.ajax({
                url: `/api/delete-client/${clientId}`,
                type: 'DELETE',
                success: function(response) {
                    alert(response.message);
                    $('#clients-table').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Delete failed');
                }
            });
        });

        // ===== ADDRESS AUTOCOMPLETE =====
        let timer;

        // Works for static + dynamically added inputs
        $(document).on('input', '.address-autocomplete', function () {
            const input = this;
            const query = input.value.trim();
            const suggestionBox = $(input).next('.address-suggestions');

            clearTimeout(timer);

            if (query.length < 1) {
                suggestionBox.hide();
                return;
            }

            timer = setTimeout(() => {
                searchAddress(query, input, suggestionBox);
            }, 300);
        });

        async function searchAddress(query, input, suggestionBox) {
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=8&accept-language=fr&q=${encodeURIComponent(query)}`;
                const res = await fetch(url);
                const data = await res.json();

                suggestionBox.empty();

                data.forEach(item => {
                    const a = item.address;
                    const parts = [];

                    // ===== YOUR EXACT FORMATTING LOGIC =====

                    // House number + street
                    if (a.house_number && a.road) {
                        parts.push(`${a.house_number} ${a.road}`);
                    } else if (a.road) {
                        parts.push(a.road);
                    }

                    // Additional local info
                    if (a.neighbourhood) parts.push(a.neighbourhood);
                    if (a.suburb) parts.push(a.suburb);
                    if (a.village) parts.push(a.village);
                    if (a.town) parts.push(a.town);
                    if (a.city) parts.push(a.city);

                    // Postal code, district, state, country
                    if (a.postcode) parts.push(a.postcode);
                    if (a.district) parts.push(a.district);
                    if (a.state) parts.push(a.state);
                    if (a.country) parts.push(a.country);

                    const text = parts.join(', ') || item.display_name;

                    // =====================================

                    const div = $('<div class="item"></div>').text(text);

                    div.on('click', function () {
                        input.value = text;
                        input.dataset.lat = item.lat;
                        input.dataset.lon = item.lon;
                        suggestionBox.hide();
                    });

                    suggestionBox.append(div);
                });

                suggestionBox.toggle(data.length > 0);

            } catch (err) {
                console.error(err);
                suggestionBox.hide();
            }
        }

        // Close suggestions when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.address-autocomplete, .address-suggestions').length) {
                $('.address-suggestions').hide();
            }
        });

    });


    function addEditHouse(house = null) {

        const container = document.getElementById('edit-houses-container');
        const index = container.children.length;

        const html = `
    <div class="card house-item mb-3 p-3 border border-secondary">
        <h5>{{ __('House') }} #${index + 1}</h5>

        ${house?.id ? `
            <input type="hidden"
                   class="house-id"
                   value="${house.id}">
        ` : ''}

        <div class="mb-3">
            <label class="form-label">{{ __('Street Name') }}</label>
            <input type="text"
                   class="form-control house_address address-autocomplete"
                   value="${house?.house_address ?? ''}"
                   required>
            <div class="address-suggestions" style="display:none;"></div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Total Number of Rooms') }}</label>
            <input type="text"
                   class="form-control room"
                   value="${house?.room ?? ''}"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Size') }}</label>
            <input type="text"
                   class="form-control size"
                   value="${house?.size ?? ''}"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Total Time for Cleaning') }}</label>
            <input type="text"
                   class="form-control time"
                   value="${house?.time ?? ''}"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Total Tools') }}</label>
            <input type="text"
                   class="form-control tools"
                   value="${house?.tools ?? ''}"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Task To Do') }}</label>
            <input type="text"
                   class="form-control tasks"
                   value="${house?.tasks ?? ''}"
                   required>
        </div>

        <button type="button"
                class="btn btn-danger"
                onclick="this.closest('.house-item').remove()">
            {{ __('Remove House') }}
        </button>
    </div>`;

        container.insertAdjacentHTML('beforeend', html);
    }
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-6">
            <h1 class="text-center fw-bold mb-4">{{ __('List Clients') }}</h1>
            <div class="card-body">
                <!-- DataTable -->
                <table id="clients-table" class="display table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Company Name') }}</th>
                            <th>{{ __('Owner Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th>{{ __('Actions') }}</th>
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
                <h1 class="modal-title" id="viewClientLabel">{{ __('Client Details') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form>
                    @csrf
                    <!-- Company Info -->
                    <h4>{{ __('Company Info') }}</h4>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Company Name') }}</label>
                        <input type="text" class="form-control" id="viewCompanyName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Owner Name') }}</label>
                        <input type="text" class="form-control" id="viewOwnerName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" class="form-control" id="viewEmail" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Phone Number') }}</label>
                        <input type="text" class="form-control" id="viewPhone" readonly>
                    </div>
                    <!-- Lockbox -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Lockbox Number') }}</label>
                        <input type="text" class="form-control" id="viewLockbox" readonly>
                    </div>
                    <!-- Company Type -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Company Type') }}</label>
                        <input type="text" class="form-control" id="viewCompanyType" readonly>
                    </div>
                    <!-- Tax -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="viewTax" disabled>
                        <label class="form-check-label" for="viewTax">{{ __('Tax Registered') }}</label>
                    </div>

                    <!-- Company Address -->
                    <h4>{{ __('Company Address') }}</h4>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Address') }}</label>
                        <input type="text" class="form-control" id="viewCompanyAddress" readonly>
                    </div>

                    <!-- Houses -->
                    <h4>{{ __('Houses') }}</h4>
                    <div id="viewHousesContainer">
                        <!-- Dynamic houses will appear here -->
                    </div>

                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title">{{ __('Edit Client') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="editClientForm">
                    @csrf
                    <input type="hidden" id="editClientId">

                    <!-- Company Info -->
                    <h4>{{ __('Company Info') }}</h4>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Company Name') }}</label>
                        <input type="text" id="editCompanyName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Owner Name') }}</label>
                        <input type="text" id="editOwnerName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="text" id="editEmail" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Phone Number') }}</label>
                        <input type="text" id="editPhone" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Lockbox Number') }}</label>
                        <input type="text" id="editLockbox" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Company Type') }}</label>
                        <select name="company_type" id="editCompanyType" class="form-select " required="">
                            <option value="Personal">{{ __('Personal') }}</option>
                            <option value="Company">{{ __('Company') }}</option>
                        </select>
                    </div>

                    <!-- Tax -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="tax" id="editTax">
                        <label class="form-check-label" for="tax">{{ __('Tax Registered') }}</label>
                    </div>

                    <!-- Company Address -->
                    <h4>{{ __('Company Address') }}</h4>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Address') }}</label>
                        <input type="text" class="form-control address-autocomplete" id="editCompanyAddress" name="company_address">
                        <div class="address-suggestions" style="display:none;"></div>
                    </div>

                    <!-- Houses -->
                    <h4>{{ __('Houses') }}</h4>
                    <div id="edit-houses-container"></div>

                    <button type="button" class="btn btn-secondary mb-3" onclick="addEditHouse()">
                        {{ __('Add House') }}
                    </button>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection