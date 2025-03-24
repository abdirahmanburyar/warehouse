<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'users' => User::with('roles')->paginate(10),
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()
        ]);
    }
}
