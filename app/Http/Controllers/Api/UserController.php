<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users (admin only).
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return response()->json($users);
    }

    /**
     * Create a new user (admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,managing_director,inventory_officer,production_manager,purchasing_manager,sales_officer,accountant',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Show a single user.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update a user's details.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|string|in:admin,managing_director,inventory_officer,production_manager,purchasing_manager,sales_officer,accountant',
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json($user);
    }

    /**
     * Update only the role of a user.
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|in:admin,managing_director,inventory_officer,production_manager,purchasing_manager,sales_officer,accountant',
        ]);

        $user->update(['role' => $request->role]);

        return response()->json($user);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
