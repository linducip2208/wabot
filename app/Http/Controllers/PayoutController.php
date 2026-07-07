<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('payouts.index', compact('payouts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'method' => 'required|in:paypal,bank_transfer',
            'account_info' => 'required|string|max:500',
        ]);

        Payout::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'method' => $request->method,
            'account_info' => $request->account_info,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Permintaan payout berhasil dikirim. Menunggu review admin.');
    }
}
