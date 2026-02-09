@extends('layouts/contentNavbarLayout')

@section('title', 'Client')

<!-- Page Scripts -->
@section('script')
<!-- @vite(['resources/assets/js/pages-account-settings-account.js']) -->
<!-- Include jQuery first -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
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
        $('#add-client-form').submit(function(e) {

            e.preventDefault(); // prevent normal form submission

            // Build houses array
            let houses = [];
            $('#houses-container > div').each(function() {
                houses.push({
                    house_address: $(this).find('.house_address').val(),
                    room: $(this).find('.house-room').val(),
                    size: $(this).find('.house-size').val(),
                    time: $(this).find('.house-time').val(),
                    tools: $(this).find('.house-tools').val(),
                    tasks: $(this).find('.house-tasks').val(),
                });
            });

            // Build main form data
            const formData = {
                company_name: $('input[name="company_name"]').val(),
                owner_name: $('input[name="owner_name"]').val(),
                email: $('input[name="email"]').val(),
                password: $('input[name="password"]').val(),
                phone_number: $('input[name="phone_number"]').val(),
                lockbox: $('input[name="lockbox_number"]').val(),
                company_type: $('select[name="company_type"]').val(),
                tax: $('#tax').is(':checked') ? true : false,
                company_address: $('input[name="company_address"]').val(),
                houses: houses
            };

            $.ajax({
                url: '/api/add-client',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(formData),

                success: function(response) {
                    alert(response.message);
                    $('#add-client-form')[0].reset();
                    $('#houses-container').html('');
                    window.location.href = '/admin/client';
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.errors;
                        let message = '';
                        for (let key in errors) {
                            message += errors[key].join(', ') + '\n';
                        }
                        alert(message);
                    } else {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
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

    // Function to add new house fields dynamically
    function addHouse() {
        const container = document.getElementById('houses-container');
        const index = container.children.length;

        const houseHTML = `
            <div class="card mb-3 p-3 border border-secondary">
                <h5>House #${index + 1}</h5>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text"
                        name="houses[${index}][house_address]"
                        class="form-control house_address address-autocomplete"
                        required>
                    <div class="address-suggestions" style="display:none;"></div>
                </div>
                

                <div class="mb-3">
                    <label class="form-label">Total Number of Rooms</label>
                    <input type="number"
                        name="houses[${index}][room]"
                        class="form-control house-room"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Size</label>
                    <input type="text"
                        name="houses[${index}][size]"
                        class="form-control house-size"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Time for Cleaning</label>
                    <input type="text"
                        name="houses[${index}][time]"
                        class="form-control house-time"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Tools</label>
                    <textarea name="houses[${index}][tools]"
                            class="form-control house-tools"
                            required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Task To Do</label>
                    <textarea name="houses[${index}][tasks]"
                            class="form-control house-tasks"
                            required></textarea>
                </div>

                <button type="button"
                        class="btn btn-danger"
                        onclick="this.parentElement.remove()">
                    Remove House
                </button>
            </div>
            `;

        container.insertAdjacentHTML('beforeend', houseHTML);
    }

</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-6 p-3">
            <h1 class="text-center fw-bold mb-4">Add Client</h1>

            <form id="add-client-form" method="POST">

                <!-- Company Name -->
                <div class="mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" required>
                </div>

                <!-- Owner Name -->
                <div class="mb-3">
                    <label class="form-label">Owner Name</label>
                    <input type="text" name="owner_name" class="form-control" required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <!-- Phone Number -->
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" required>
                </div>

                <!-- Lockbox -->
                <div class="mb-3">
                    <label class="form-label">Lockbox Number</label>
                    <input type="text" name="lockbox_number" class="form-control" required>
                </div>

                <!-- Company Type -->
                <div class="mb-6">
                    <label class="form-label">Company Type</label>
                    <select name="company_type" class="form-select " required="">
                        <option value="Personal">Personal</option>
                        <option value="Company">Company</option>
                    </select>
                </div>

                <!-- Tax -->
                <div class="form-check mb-9">
                    <input class="form-check-input" type="checkbox" name="tax" id="tax">
                    <label class="form-check-label" for="tax">Tax Registered</label>
                </div>

                <!-- Company Address -->
                <h4>Company Address</h4>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" class="form-control address-autocomplete" required>
                    <div class="address-suggestions" style="display:none;"></div>
                </div>

                <!-- Houses -->
                <h4>Houses</h4>
                <div id="houses-container">
                    <!-- Initial house can be added here if needed -->
                </div>
                <button type="button" class="btn btn-secondary mb-3" onclick="addHouse()">Add House</button>

                <div class="text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection