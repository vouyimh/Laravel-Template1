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

    public function create(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'owner_name'   => 'required|string|max:255',
            'email'        => 'required|email|unique:clients,email',
            'password'     => 'required|string|min:6',

            'phone_number'    => 'nullable|string|max:20',
            'company_type'    => 'nullable|string|max:255',
            'company_address' => 'nullable|array',
            'tax'             => 'boolean',
            'file'            => 'nullable|string',

            // Client houses (array)
            'houses'                  => 'nullable|array',
            'houses.*.street_name'    => 'required_with:houses|string|max:255',
            'houses.*.local_code'     => 'required_with:houses|string|max:50',
            'houses.*.village'        => 'required_with:houses|string|max:255',
            'houses.*.house_number'   => 'required_with:houses|string|max:50',
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
        // Find client
        $client = Client::with('houses')->find($id);

        if (!$client) {
            return response()->json([
                'message' => 'Client not found',
            ], 404);
        }

        // Validate request (PATCH = sometimes)
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'owner_name'   => 'sometimes|string|max:255',
            'email'        => 'sometimes|email|unique:clients,email,' . $client->id,
            'password'     => 'sometimes|string|min:6',

            'phone_number'    => 'sometimes|nullable|string|max:20',
            'company_type'    => 'sometimes|nullable|string|max:255',
            'company_address' => 'sometimes|nullable|array',
            'tax'             => 'sometimes|boolean',
            'file'            => 'sometimes|nullable|string',

            // Houses
            'houses'                  => 'sometimes|array',
            'houses.*.id'             => 'sometimes|exists:houses,id',
            'houses.*.street_name'    => 'required_with:houses|string|max:255',
            'houses.*.local_code'     => 'required_with:houses|string|max:50',
            'houses.*.village'        => 'required_with:houses|string|max:255',
            'houses.*.house_number'   => 'required_with:houses|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            // Handle password hashing
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            // Update client fields
            $client->update($validated);

            // Update or create houses
            if (isset($validated['houses'])) {
                foreach ($validated['houses'] as $houseData) {

                    // Update existing house
                    if (isset($houseData['id'])) {
                        $client->houses()
                            ->where('id', $houseData['id'])
                            ->update($houseData);
                    }
                    // Create new house
                    else {
                        $client->houses()->create($houseData);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Client updated successfully',
                'data'    => $client->fresh()->load('houses'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update client',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}