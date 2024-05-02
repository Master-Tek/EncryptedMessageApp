<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // Return a list of all users with their IDs
        $users = User::select('id', 'name')
                     ->where('id', '!=', Auth::id()) // Exclude the authenticated user
                     ->get();
        return response()->json($users);
    }
}
