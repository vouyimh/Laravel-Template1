<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('admin.client.index');
    }

    public function addClient()
    {
        return view('admin.client.add-client');
    }
}
