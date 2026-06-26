<?php

namespace App\Http\Controllers;

use App\Models\WaServer;
use App\Models\WaSession;
use App\Services\BaileysService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
    ) {}

    public function index()
    {
        $servers = WaServer::where('user_id', Auth::id())->get();
        $sessions = WaSession::where('user_id', Auth::id())
            ->with('server')
            ->latest()
            ->get();

        foreach ($sessions as $session) {
            if ($session->server) {
                $live = $this->baileys->getStatus($session->server, $session->session_id);
                $liveStatus = $live['status'] ?? 'unknown';
                if ($liveStatus === 'connected') {
                    $phone = $live['phone'] ?? null;
                    if ($phone) {
                        $phone = preg_replace('/[@:].*$/', '', $phone);
                        $phone = preg_replace('/[^0-9]/', '', $phone);
                    }
                    if ($session->status !== 'connected') {
                        \App\Models\WaSessionLog::create([
                            'user_id' => $session->user_id,
                            'session_id' => $session->id,
                            'event' => 'connected',
                            'phone' => $phone ?: $session->phone,
                            'logged_at' => now(),
                        ]);
                    }
                    $session->update([
                        'status' => 'connected',
                        'phone' => $phone ?: $session->phone,
                        'last_active_at' => now(),
                    ]);
                } elseif (in_array($liveStatus, ['disconnected', 'logged_out'])) {
                    if ($session->status !== 'disconnected') {
                        \App\Models\WaSessionLog::create([
                            'user_id' => $session->user_id,
                            'session_id' => $session->id,
                            'event' => $liveStatus,
                            'phone' => $session->phone,
                            'logged_at' => now(),
                        ]);
                    }
                    $session->update(['status' => 'disconnected', 'is_active' => false]);
                } elseif ($liveStatus === 'qr_ready') {
                    $session->update(['status' => 'qr_ready']);
                }
            }
        }

        return view('sessions.index', compact('servers', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:wa_servers,id',
            'name' => 'required|string|max:255',
        ]);

        $server = WaServer::where('user_id', Auth::id())->findOrFail($validated['server_id']);

        if (!$this->baileys->check($server)) {
            return back()->with('error', 'Tidak bisa terhubung ke server Baileys. Periksa host dan port.');
        }

        $sessionId = uniqid('wa_', true);
        $session = WaSession::create([
            'user_id' => Auth::id(),
            'server_id' => $server->id,
            'session_id' => $sessionId,
            'name' => $validated['name'],
            'status' => 'pending',
        ]);

        $webhookUrl = route('webhook.whatsapp');
        $result = $this->baileys->createSession($server, $session, $webhookUrl);

        if ($result['ok'] ?? false) {
            return redirect()->route('sessions.show', $session)
                ->with('success', 'Sesi berhasil dibuat. Scan QR untuk menghubungkan.');
        }

        return back()->with('error', 'Gagal membuat sesi: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function show(WaSession $session)
    {
        abort_if($session->user_id !== Auth::id(), 403);

        $qrImage = null;
        $refresh = true;

        if ($session->server && $session->status !== 'connected') {
            $status = $this->baileys->getStatus($session->server, $session->session_id);
            $qrString = $this->baileys->getQr($session->server, $session->session_id);

            if ($qrString) {
                $session->update(['qr_code' => $qrString, 'status' => 'qr_ready']);
                $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrString);
            }

            if (($status['status'] ?? '') === 'connected') {
                $phone = $status['phone'] ?? null;
                if ($phone) {
                    $phone = preg_replace('/[@:].*$/', '', $phone);
                    $phone = preg_replace('/[^0-9]/', '', $phone);
                }
                \App\Models\WaSessionLog::create([
                    'user_id' => $session->user_id,
                    'session_id' => $session->id,
                    'event' => 'connected',
                    'phone' => $phone,
                    'logged_at' => now(),
                ]);
                $session->update([
                    'status' => 'connected',
                    'phone' => $phone,
                    'last_active_at' => now(),
                ]);
                $refresh = false;
            }

            if (($status['status'] ?? '') === 'qr_ready') {
                $session->update(['status' => 'qr_ready']);
            }
        } elseif ($session->status === 'connected') {
            $refresh = false;
        }

        $logs = \App\Models\WaSessionLog::where('session_id', $session->id)
            ->latest('logged_at')
            ->take(50)
            ->get();

        return view('sessions.show', compact('session', 'qrImage', 'refresh', 'logs'));
    }

    public function update(Request $request, WaSession $session)
    {
        abort_if($session->user_id !== Auth::id(), 403);
        $session->update($request->validate(['name' => 'required|string|max:255']));
        return back()->with('success', 'Sesi diperbarui.');
    }

    public function destroy(WaSession $session)
    {
        abort_if($session->user_id !== Auth::id(), 403);
        if ($session->server) {
            $this->baileys->deleteSession($session->server, $session->session_id);
        }
        $session->delete();
        return redirect()->route('sessions.index')->with('success', 'Sesi WhatsApp dihapus.');
    }

    public function status(WaSession $session)
    {
        abort_if($session->user_id !== Auth::id(), 403);

        if (!$session->server) {
            return response()->json(['status' => 'no_server']);
        }

        $status = $this->baileys->getStatus($session->server, $session->session_id);

        if ($status['status'] === 'connected') {
            $phone = $status['phone'] ?? null;
            if ($phone) {
                $phone = preg_replace('/[@:].*$/', '', $phone);
                $phone = preg_replace('/[^0-9]/', '', $phone);
            }
            $session->update([
                'status' => 'connected',
                'phone' => $phone,
                'last_active_at' => now(),
            ]);
        } elseif ($status['status'] === 'qr_ready') {
            $session->update(['status' => 'qr_ready']);
        }

        return response()->json($status);
    }
}
