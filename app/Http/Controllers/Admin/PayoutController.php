<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Payout::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->get();

        return view('admin.payouts.index', compact('payouts'));
    }

    public function approve(Payout $payout)
    {
        $payout->update([
            'status' => 'completed',
            'processed_at' => now(),
            'admin_note' => request('admin_note'),
        ]);

        return back()->with('success', __('messages.success.payout_approved', ['id' => $payout->id]));
    }

    public function reject(Payout $payout)
    {
        $payout->update([
            'status' => 'rejected',
            'admin_note' => request('admin_note'),
        ]);

        return back()->with('success', __('messages.success.payout_rejected', ['id' => $payout->id]));
    }
}
