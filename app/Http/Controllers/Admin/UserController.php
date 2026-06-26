<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('plan')->latest()->get();
        $plans = Plan::where('is_active', true)->get();
        return view('admin.users.index', compact('users', 'plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
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
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if ($data['password']) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        if ($data['plan_id'] ?? null) {
            $user->subscriptions()->where('status', 'active')->update(['status' => 'inactive']);
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $data['plan_id'],
                'status' => 'active',
                'starts_at' => now(),
            ]);
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
