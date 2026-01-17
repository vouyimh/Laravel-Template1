<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * GET /api/clients
     */
    public function index()
    {
        try {
            // Eager load 'houses' relationship
            $clients = Client::with('houses')->get();

            return response()->json([
                'message' => 'Clients retrieved successfully',
                'data' => $clients,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'message' => 'Failed to retrieve clients',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/clients/{id}
     */
    public function show($id)
    {
        try {
            $client = Client::with('houses')->find($id);

            if (!$client) {
                return response()->json([
                    'message' => 'Client not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Client retrieved successfully',
                'data'    => $client,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve client',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function create(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'owner_name'   => 'required|string|max:255',
            'email'        => 'required|email|unique:clients,email',
            'password'     => 'required|string|min:6',

            'phone_number'    => 'nullable|string|max:20',
            'company_type'    => 'nullable|string|in:Personal,Company',
            'company_address' => 'nullable|array',
            'company_address.street_name' => 'required_with:company_address|string|max:255',
            'company_address.local_code'  => 'required_with:company_address|string|max:50',
            'company_address.village'     => 'required_with:company_address|string|max:255',
            'company_address.house_number' => 'required_with:company_address|string|max:50',

            'tax'             => 'sometimes|boolean',
            'file'            => 'nullable|string',
            'lockbox'            => 'nullable|string',

            // Houses validation
            'houses'                => 'nullable|array',
            'houses.*.street_name'  => 'required_with:houses|string|max:255',
            'houses.*.local_code'   => 'required_with:houses|string|max:50',
            'houses.*.village'      => 'required_with:houses|string|max:255',
            'houses.*.house_number' => 'required_with:houses|string|max:50',
            'houses.*.room'         => 'required_with:houses|integer|min:1',
            'houses.*.size'         => 'required_with:houses|string|max:255',
            'houses.*.time'         => 'required_with:houses|string|max:255',
            'houses.*.tools'        => 'required_with:houses|string|max:1000',
            'houses.*.tasks'        => 'required_with:houses|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Create client
            $client = Client::create([
                'company_name'    => $validated['company_name'],
                'owner_name'      => $validated['owner_name'],
                'email'           => $validated['email'],
                'password'        => Hash::make($validated['password']),
                'phone_number'    => $validated['phone_number'] ?? null,
                'company_type'    => $validated['company_type'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'tax'             => $validated['tax'] ?? false,
                'file'            => $validated['file'] ?? null,
                'lockbox'         => $validated['lockbox'] ?? null,
            ]);

            // Create client houses (if any)
            if (!empty($validated['houses'])) {
                foreach ($validated['houses'] as $house) {
                    $client->houses()->create($house);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Client created successfully',
                'data'    => $client->load('houses'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create client',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/clients/{id}
     */
    public function destroy($id)
    {
        try {
            // Find the client by ID
            $client = Client::find($id);

            if (!$client) {
                return response()->json([
                    'message' => 'Client not found',
                ], 404);
            }

            // Optional: delete related houses if not cascade
            // $client->houses()->delete();

            // Delete the client
            $client->delete();

            return response()->json([
                'message' => 'Client deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * PATCH /api/clients/{id}
     */
    public function update(Request $request, $id)
    {
        $client = Client::with('houses')->find($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        // If you send JSON via AJAX
        if ($request->isJson()) {
            $request->merge($request->json()->all());
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'owner_name'   => 'required|string|max:255',
            'email'        => 'required|email|unique:clients,email,' . $id . ',client_id',

            // ✅ password OPTIONAL on update
            'password'     => 'nullable|string|min:6',

            'phone_number' => 'nullable|string|max:20',
            'company_type' => 'nullable|string|in:Personal,Company',
            'lockbox'      => 'nullable|string',
            'tax'          => 'sometimes|boolean',

            'company_address' => 'nullable|array',
            'company_address.street_name'  => 'required_with:company_address|string|max:255',
            'company_address.local_code'   => 'required_with:company_address|string|max:50',
            'company_address.village'      => 'required_with:company_address|string|max:255',
            'company_address.house_number' => 'required_with:company_address|string|max:50',

            // Houses
            'houses'                => 'nullable|array',
            'houses.*.id'           => 'nullable|integer|exists:client_houses,id',
            'houses.*.street_name'  => 'required_with:houses|string|max:255',
            'houses.*.local_code'   => 'required_with:houses|string|max:50',
            'houses.*.village'      => 'required_with:houses|string|max:255',
            'houses.*.house_number' => 'required_with:houses|string|max:50',
            'houses.*.room'         => 'required_with:houses|integer|min:1',
            'houses.*.size'         => 'required_with:houses|string|max:255',
            'houses.*.time'         => 'required_with:houses|string|max:255',
            'houses.*.tools'        => 'required_with:houses|string|max:1000',
            'houses.*.tasks'        => 'required_with:houses|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // ✅ Handle password safely
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // ✅ Update client (exclude houses)
            $clientData = collect($validated)->except('houses')->toArray();
            $client->update($clientData);

            // ✅ Handle houses
            if (isset($validated['houses'])) {

                $incomingIds = collect($validated['houses'])
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                // delete removed houses
                $client->houses()
                    ->whereNotIn('id', $incomingIds)
                    ->delete();

                foreach ($validated['houses'] as $house) {
                    if (!empty($house['id'])) {
                        $client->houses()
                            ->where('id', $house['id'])
                            ->update($house);
                    } else {
                        $client->houses()->create($house);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Client updated successfully',
                'data' => $client->fresh()->load('houses'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
