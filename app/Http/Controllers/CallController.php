<?php

namespace App\Http\Controllers;

use App\Models\WaCallBroadcast;
use App\Models\WaCallLog;
use App\Models\WaContact;
use App\Models\WaMetaAccount;
use App\Services\ElevenLabsService;
use App\Services\FfmpegService;
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CallController extends Controller
{
    public function __construct(
        protected MetaApiService $meta,
        protected ElevenLabsService $elevenlabs,
        protected FfmpegService $ffmpeg,
    ) {}

    public function index()
    {
        $broadcasts = WaCallBroadcast::where('user_id', Auth::id())
            ->with('metaAccount')
            ->latest()
            ->get();

        $accounts = WaMetaAccount::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();

        return view('calls.index', compact('broadcasts', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meta_account_id' => 'required|exists:wa_meta_accounts,id',
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'voice_id' => 'nullable|string|max:100',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'exists:wa_contacts,id',
            'manual_numbers' => 'nullable|string',
            'delay_seconds' => 'nullable|integer|min:5|max:120',
        ]);

        $account = WaMetaAccount::where('user_id', Auth::id())->findOrFail($validated['meta_account_id']);

        $allRecipients = collect();
        if (!empty($validated['recipient_ids'])) {
            $allRecipients = WaContact::where('user_id', Auth::id())
                ->whereIn('id', $validated['recipient_ids'])
                ->get();
        }

        $manualIds = [];
        if (!empty($validated['manual_numbers'])) {
            $lines = explode("\n", trim($validated['manual_numbers']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $phone = preg_replace('/[^0-9]/', '', $line);
                $contact = WaContact::firstOrCreate(
                    ['user_id' => Auth::id(), 'phone' => $phone],
                    ['name' => $phone]
                );
                $allRecipients->push($contact);
                $manualIds[] = (string) $contact->id;
            }
        }

        $recipientIds = array_merge($validated['recipient_ids'] ?? [], $manualIds);

        if (empty($recipientIds)) {
            return back()->with('error', __('messages.error.select_at_least_1_contact'));
        }

        $broadcast = WaCallBroadcast::create([
            'user_id' => Auth::id(),
            'meta_account_id' => $account->id,
            'name' => $validated['name'],
            'message' => $validated['message'],
            'voice_id' => $validated['voice_id'] ?? '21m00Tcm4TlvDq8ikWAM',
            'recipient_ids' => $recipientIds,
            'status' => 'sending',
            'total_recipients' => count($recipientIds),
            'delay_seconds' => $validated['delay_seconds'] ?? 10,
        ]);

        $this->startBroadcast($broadcast, $account, $allRecipients);

        return redirect()->route('calls.index')->with('success', __('messages.success.voice_broadcast_started'));
    }

    public function destroy(WaCallBroadcast $broadcast)
    {
        abort_if($broadcast->user_id !== Auth::id(), 403);
        $broadcast->delete();
        return redirect()->route('calls.index')->with('success', __('messages.success.broadcast_deleted'));
    }

    public function logs(WaCallBroadcast $broadcast)
    {
        abort_if($broadcast->user_id !== Auth::id(), 403);

        $logs = $broadcast->logs()->with('contact')->latest()->paginate(30);

        return view('calls.logs', compact('broadcast', 'logs'));
    }

    protected function startBroadcast(WaCallBroadcast $broadcast, WaMetaAccount $account, $recipients): void
    {
        $elevenlabsKey = config('services.elevenlabs.key');
        $audioUrl = null;

        if ($elevenlabsKey) {
            $audio = $this->elevenlabs->textToSpeech(
                $elevenlabsKey,
                $broadcast->message,
                $broadcast->voice_id
            );

            if ($audio) {
                $filename = 'call_' . $broadcast->id . '_' . time() . '.mp3';
                $path = storage_path("app/public/calls/{$filename}");
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }
                file_put_contents($path, $audio);
                $audioUrl = asset("storage/calls/{$filename}");
            }
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $contact) {
            $phone = preg_replace('/[^0-9]/', '', $phone = $contact->phone);

            $log = WaCallLog::create([
                'broadcast_id' => $broadcast->id,
                'contact_id' => $contact->id,
                'meta_account_id' => $account->id,
                'phone' => $phone,
                'status' => 'sent',
                'audio_url' => $audioUrl,
            ]);

            $message = __('messages.auto_reply.voice_call_prompt', ['name' => $broadcast->name]);

            $textResult = $this->meta->sendText($account, $phone, $message);

            if (!empty($textResult['messages'])) {
                $sentCount++;
            } else {
                $failedCount++;
                $log->update(['status' => 'failed', 'notes' => $textResult['error']['message'] ?? 'Send failed']);
                usleep($broadcast->delay_seconds * 1000000);
                continue;
            }

            if ($audioUrl) {
                $localPath = storage_path("app/public/calls/" . basename($audioUrl));
                $audioToSend = $audioUrl;

                if ($this->ffmpeg->isAvailable() && str_ends_with($localPath, '.mp3')) {
                    $oggPath = $this->ffmpeg->convertToOgg($localPath);
                    if ($oggPath) {
                        $oggFilename = basename($oggPath);
                        $audioToSend = asset("storage/calls/{$oggFilename}");
                        $log->update(['audio_url' => $audioToSend]);
                    }
                }

                $audioResult = $this->meta->sendAudio($account, $phone, $audioToSend);

                if (empty($audioResult['messages'])) {
                    $log->update(['notes' => ($log->notes ? $log->notes . ' | ' : '') . 'Audio send: ' . ($audioResult['error']['message'] ?? 'failed')]);
                }
            }

            usleep($broadcast->delay_seconds * 1000000);
        }

        $broadcast->update([
            'called_count' => $sentCount,
            'failed_count' => $failedCount,
            'status' => $sentCount > 0 ? 'completed' : 'failed',
        ]);
    }

    public function handleReply(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->input('phone', ''));
        $text = strtolower(trim($request->input('message', '')));

        if (!in_array($text, ['ya', 'ok', 'yes', 'oke', 'ok', 'iya'])) {
            return response()->json(['processed' => false]);
        }

        $pendingLog = WaCallLog::where('phone', $phone)
            ->where('status', 'sent')
            ->latest()
            ->first();

        if (!$pendingLog) {
            return response()->json(['processed' => false]);
        }

        $pendingLog->update([
            'status' => 'confirmed',
            'notes' => 'Recipient confirmed - ready for call',
        ]);

        $broadcast = $pendingLog->broadcast;
        if ($broadcast) {
            $broadcast->increment('answered_count');
        }

        return response()->json(['processed' => true]);
    }
}
