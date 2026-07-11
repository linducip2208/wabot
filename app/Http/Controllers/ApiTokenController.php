<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::where('user_id', Auth::id())->latest()->get();
        return view('tokens.index', compact('tokens'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $token = ApiToken::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'token' => ApiToken::generate(),
        ]);

        return back()->with('success', __('messages.success.token_created', ['token' => $token->token]));
    }

    public function destroy(ApiToken $token)
    {
        abort_if($token->user_id !== Auth::id(), 403);
        $token->delete();
        return back()->with('success', __('messages.success.token_deleted'));
    }
}
