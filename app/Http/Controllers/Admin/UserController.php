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
            'ends_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $userRole = Role::find($data['role_id'] ?? null);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $userRole?->name ?? 'user',
            'role_id' => $data['role_id'] ?? null,
            'plan_id' => $data['plan_id'] ?? null,
            'email_verified_at' => now(),
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        if ($data['plan_id'] ?? null) {
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $data['plan_id'],
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $data['ends_at'] ?? null,
            ]);
        }

        return back()->with('success', __('messages.success.user_added'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role_id' => 'nullable|exists:roles,id',
            'plan_id' => 'nullable|exists:plans,id',
            'ends_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $userRole = Role::find($data['role_id'] ?? null);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $userRole?->name ?? $user->role,
            'role_id' => $data['role_id'] ?? null,
            'plan_id' => $data['plan_id'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ];

        if ($data['password']) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if ($data['plan_id'] ?? null) {
            $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $data['plan_id'],
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $data['ends_at'] ?? null,
            ]);
        } else {
            $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);
        }

        return back()->with('success', __('messages.success.user_updated'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', __('messages.success.user_deleted'));
    }

    public function impersonate(User $user)
    {
        auth()->login($user);
        return redirect('/')->with('success', __('messages.success.logged_in_as', ['name' => $user->name]));
    }
}
