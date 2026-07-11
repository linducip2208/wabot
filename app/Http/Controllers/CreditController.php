<?php

namespace App\Http\Controllers;

use App\Models\WaCreditPack;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditController extends Controller
{
    protected CreditService $creditService;

    public function __construct(CreditService $creditService)
    {
        $this->creditService = $creditService;
    }

    public function index()
    {
        $user = Auth::user();
        $balance = $this->creditService->getBalance($user);
        $packs = WaCreditPack::where('is_active', true)->orderBy('sort_order')->get();
        $transactions = $this->creditService->getTransactionHistory($user);

        return view('credits.index', compact('balance', 'packs', 'transactions'));
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'pack_id' => 'required|exists:wa_credit_packs,id',
        ]);

        $user = Auth::user();
        $result = $this->creditService->purchasePack($user, $request->pack_id);

        if ($result['redirect_to_payment']) {
            return redirect()->route('credits.payment', $result['payment'])
                ->with('success', __('credits.redirect_to_payment'));
        }

        return back()->with('success', __('credits.credits_added', ['credits' => $result['pack']->credits]));
    }

    public function payment(PaymentTransaction $payment)
    {
        abort_if($payment->user_id !== Auth::id(), 403);

        $gateways = PaymentGateway::where('is_active', true)->orderBy('sort_order')->get();
        $pack = WaCreditPack::find($payment->gateway_meta['pack_id'] ?? 0);

        return view('credits.payment', compact('payment', 'gateways', 'pack'));
    }

    public function callback(Request $request, PaymentTransaction $payment)
    {
        abort_if($payment->user_id !== Auth::id(), 403);

        $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $gateway = PaymentGateway::findOrFail($request->gateway_id);
        $payment->update([
            'gateway' => $gateway->code,
        ]);

        $this->creditService->confirmPackPayment($payment);

        return redirect()->route('credits.index')
            ->with('success', __('credits.payment_confirmed'));
    }
}
