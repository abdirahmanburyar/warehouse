<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia;


class TransferController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Transfer/Index', [
            'transfers' => []
        ]);
    }
}
