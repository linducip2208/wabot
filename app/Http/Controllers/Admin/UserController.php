<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('plan', 'role')->latest()->get();
        $plans = Plan::where('is_active', true)->get();
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'plans', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role_id' => 'nullable|exists:roles,id',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $userRole = Role::find($data['role_id'] ?? null);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $userRole?->name ?? 'user',
            'role_id' => $data['role_id'] ?? null,
            'plan_id' => $data['plan_id'] ?? null,
        ]);

        if ($data['plan_id'] ?? null) {
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $data['plan_id'],
                'status' => 'active',
                'starts_at' => now(),
            ]);
        }

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role_id' => 'nullable|exists:roles,id',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $userRole = Role::find($data['role_id'] ?? null);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $userRole?->name ?? $user->role,
            'role_id' => $data['role_id'] ?? null,
            'plan_id' => $data['plan_id'] ?? null,
        ];

        if ($data['password']) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if ($data['plan_id'] ?? null) {
            $user->subscriptions()->where('status', 'active')->update(['status' => 'inactive']);
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $data['plan_id'],
                'status' => 'active',
                'starts_at' => now(),
            ]);
        } else {
            $user->subscriptions()->where('status', 'active')->update(['status' => 'inactive']);
        }

        return back()->with('success', 'User diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User dihapus.');
    }

    public function impersonate(User $user)
    {
        auth()->login($user);
        return redirect('/')->with('success', 'Login sebagai ' . $user->name);
    }
}
