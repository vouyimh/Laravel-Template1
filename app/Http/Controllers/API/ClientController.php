<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * GET /api/clients
     */
    public function index()
    {
        return response()->json(
            Client::with('houses')->latest()->get()
        );
    }

    /**
     * POST /api/clients
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'owner_name'   => 'required|string|max:255',
            'email'        => 'required|email|unique:clients,email',
            'password'     => 'required|min:5',
            'phone_number' => 'nullable|string',
            'company_type' => 'nullable|string',
            'tax'          => 'boolean',
            'file'         => 'nullable|string',

            'company_address' => 'nullable|array',
            'company_address.street_name' => 'nullable|string',
            'company_address.local_code'  => 'nullable|string',
            'company_address.village'     => 'nullable|string',
            'company_address.house_number'=> 'nullable|string',

            'houses' => 'nullable|array',
            'houses.*.street_name' => 'required|string',
            'houses.*.local_code'  => 'required|string',
            'houses.*.village'     => 'required|string',
            'houses.*.house_number'=> 'required|string',
        ]);

        $client = Client::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['houses'])) {
            $client->houses()->createMany($validated['houses']);
        }

        return response()->json([
            'message' => 'Client created successfully',
            'data' => $client->load('houses')
        ], 201);
    }

    /**
     * GET /api/clients/{id}
     */
    public function show($id)
    {
        return response()->json(
            Client::with('houses')->findOrFail($id)
        );
    }

    /**
     * PUT /api/clients/{id}
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'owner_name'   => 'sometimes|string|max:255',
            'email'        => [
                'sometimes',
                'email',
                Rule::unique('clients')->ignore($client->id)
            ],
            'password'     => 'sometimes|min:5',
            'phone_number' => 'nullable|string',
            'company_type' => 'nullable|string',
            'tax'          => 'boolean',
            'file'         => 'nullable|string',
            'company_address' => 'nullable|array',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $client->update($validated);

        return response()->json([
            'message' => 'Client updated successfully',
            'data' => $client->load('houses')
        ]);
    }

    /**
     * DELETE /api/clients/{id}
     */
    public function destroy($id)
    {
        Client::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }
}