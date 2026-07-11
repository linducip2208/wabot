<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $gateways = PaymentGateway::orderBy('sort_order')->get();
        return view('admin.gateways.index', compact('gateways'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_gateways',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'logo_color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        PaymentGateway::create($data);

        return back()->with('success', __('messages.success.payment_gateway_added'));
    }

    public function update(Request $request, PaymentGateway $gateway)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_gateways,code,' . $gateway->id,
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'logo_color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        $gateway->update($data);

        return back()->with('success', __('messages.success.payment_gateway_updated'));
    }

    public function destroy(PaymentGateway $gateway)
    {
        $gateway->delete();
        return back()->with('success', __('messages.success.payment_gateway_deleted'));
    }
}
