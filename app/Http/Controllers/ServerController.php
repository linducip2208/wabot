<?php

namespace App\Http\Controllers;

use App\Models\WaServer;
use App\Models\WaSession;
use App\Models\WaMessage;
use App\Services\BaileysService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
    ) {}

    public function index()
    {
        $userId = Auth::id();
        $servers = WaServer::where('user_id', $userId)
            ->with('sessions')
            ->withCount('sessions')
            ->get();

        $totalSessions = WaSession::where('user_id', $userId)->count();
        $todayMessages = WaMessage::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        foreach ($servers as $s) {
            $s->messages_count = WaMessage::whereIn('session_id', $s->sessions->pluck('id'))->count();
            $s->uptime = $s->sessions->count() > 0 ? round($s->sessions->where('status', 'connected')->count() / $s->sessions->count() * 100, 1) : 0;
        }

        $serverLabels = $servers->pluck('name')->toArray();
        $serverMessages = $servers->map(function ($s) {
            return WaMessage::whereIn('session_id', $s->sessions->pluck('id'))->count();
        })->toArray();
        $serverSessions = $servers->map(fn($s) => $s->sessions->count())->toArray();

        return view('servers.index', compact(
            'servers', 'totalSessions', 'todayMessages',
            'serverLabels', 'serverMessages', 'serverSessions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'api_key' => 'required|string|max:255',
        ]);

        $server = WaServer::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'host' => $validated['host'],
            'port' => $validated['port'],
            'api_key' => $validated['api_key'],
            'is_active' => true,
        ]);

        $isOnline = $this->baileys->check($server);

        return back()->with(
            $isOnline ? 'success' : 'warning',
            $isOnline
                ? 'Server tersimpan dan online.'
                : 'Server tersimpan tapi tidak bisa dihubungi. Periksa host dan port.'
        );
    }

    public function update(Request $request, WaServer $server)
    {
        abort_if($server->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'api_key' => 'required|string|max:255',
        ]);

        $server->update($validated);

        return back()->with('success', 'Server diperbarui.');
    }

    public function destroy(WaServer $server)
    {
        abort_if($server->user_id !== Auth::id(), 403);

        if ($server->sessions()->exists()) {
            return back()->with('error', 'Hapus semua sesi WhatsApp terlebih dahulu.');
        }

        $server->delete();

        return back()->with('success', 'Server dihapus.');
    }
}
