<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentTransaction::with('user', 'subscription.plan')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->get();

        $types = PaymentTransaction::distinct()->pluck('type');
        $statuses = PaymentTransaction::distinct()->pluck('status');

        return view('admin.transactions.index', compact('transactions', 'types', 'statuses'));
    }

    public function update(Request $request, PaymentTransaction $transaction)
    {
        $data = $request->validate([
            'status' => 'required|in:completed,failed,pending',
        ]);

        $transaction->update(['status' => $data['status']]);

        return back()->with('success', __('messages.success.transaction_updated'));
    }
}
