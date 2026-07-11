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
            'driver' => 'nullable|string|in:manual,stripe,razorpay',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'api_key' => 'nullable|string|max:1000',
            'api_secret' => 'nullable|string|max:1000',
            'logo_color' => 'nullable|string|max:7',
            'is_auto' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $gateway = PaymentGateway::create($data);
        $gateway->api_key = $data['api_key'] ?? null;
        $gateway->api_secret = $data['api_secret'] ?? null;
        $gateway->save();

        return back()->with('success', __('messages.success.payment_gateway_added'));
    }

    public function update(Request $request, PaymentGateway $gateway)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_gateways,code,' . $gateway->id,
            'driver' => 'nullable|string|in:manual,stripe,razorpay',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'api_key' => 'nullable|string|max:1000',
            'api_secret' => 'nullable|string|max:1000',
            'logo_color' => 'nullable|string|max:7',
            'is_auto' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $gateway->update($data);

        if (!empty($data['api_key'])) {
            $gateway->api_key = $data['api_key'];
        }
        if (!empty($data['api_secret'])) {
            $gateway->api_secret = $data['api_secret'];
        }
        $gateway->save();

        return back()->with('success', __('messages.success.payment_gateway_updated'));
    }

    public function destroy(PaymentGateway $gateway)
    {
        $gateway->delete();
        return back()->with('success', __('messages.success.payment_gateway_deleted'));
    }

    public function stripeWebhook(\Illuminate\Http\Request $request)
    {
        $gateway = PaymentGateway::where('driver', 'stripe')->where('is_active', true)->first();
        if (!$gateway) {
            return response()->json(['error' => 'No active Stripe gateway'], 404);
        }

        $service = new \App\Services\StripeService($gateway);
        $result = $service->handleWebhook($request, $gateway);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function razorpayWebhook(\Illuminate\Http\Request $request)
    {
        $gateway = PaymentGateway::where('driver', 'razorpay')->where('is_active', true)->first();
        if (!$gateway) {
            return response()->json(['error' => 'No active Razorpay gateway'], 404);
        }

        $service = new \App\Services\RazorpayService($gateway);
        $result = $service->handleWebhook($request, $gateway);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
