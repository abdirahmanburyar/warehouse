<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpiredController extends Controller
{
    public function index()
    {
        return inertia('Expired/Index');
    }
}
