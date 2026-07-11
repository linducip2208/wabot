<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaCreditTransaction;
use App\Services\CreditService;
use Illuminate\Http\Request;

class CreditTransactionController extends Controller
{
    public function index()
    {
        $transactions = WaCreditTransaction::with('user')->latest()->paginate(50);
        return view('admin.credit-transactions.index', compact('transactions'));
    }

    public function grant(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $service = app(CreditService::class);
        $user = \App\Models\User::findOrFail($data['user_id']);
        $service->addCredits($user, $data['amount'], $data['description'] ?? 'Admin grant');

        return back()->with('success', __('admin.credits_granted', ['amount' => $data['amount'], 'user' => $user->name]));
    }
}
