<?php

namespace App\Http\Controllers;

use App\Models\WaRecurring;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Models\WaMetaAccount;
use App\Models\WaTelegramAccount;
use App\Services\BaileysService;
use App\Services\MetaApiService;
use App\Services\SpintaxService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected SpintaxService $spintax,
        protected MetaApiService $metaApi,
        protected TelegramService $telegram,
    ) {}

    public function index()
    {
        $schedules = WaRecurring::where('user_id', Auth::id())
            ->with('session', 'metaAccount', 'telegramAccount')
            ->latest()
            ->get();

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();

        $contacts = WaContact::where('user_id', Auth::id())->get();

        $metaAccounts = WaMetaAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        $telegramAccounts = WaTelegramAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('recurrings.index', compact(
            'schedules', 'sessions', 'contacts', 'metaAccounts', 'telegramAccounts'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'nullable|in:whatsapp,meta,telegram',
            'session_id' => 'nullable|exists:wa_sessions,id',
            'meta_account_id' => 'nullable|exists:wa_meta_accounts,id',
            'telegram_account_id' => 'nullable|exists:wa_telegram_accounts,id',
            'recurrence' => 'required|in:once,daily,weekly,monthly',
            'time' => 'nullable',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'message' => 'required|string|max:5000',
            'target_type' => 'required|in:all,group,numbers',
            'is_active' => 'boolean',
        ]);

        $data['user_id'] = Auth::id();
        $data['channel'] = $data['channel'] ?? 'whatsapp';
        $data['is_active'] = $request->boolean('is_active', true);

        $schedule = WaRecurring::create($data);
        $schedule->computeNextRun();

        return back()->with('success', __('messages.success.recurring_created'));
    }

    public function update(Request $request, WaRecurring $schedule)
    {
        abort_if($schedule->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'nullable|in:whatsapp,meta,telegram',
            'session_id' => 'nullable|exists:wa_sessions,id',
            'meta_account_id' => 'nullable|exists:wa_meta_accounts,id',
            'telegram_account_id' => 'nullable|exists:wa_telegram_accounts,id',
            'recurrence' => 'required|in:once,daily,weekly,monthly',
            'time' => 'nullable',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'message' => 'required|string|max:5000',
            'target_type' => 'required|in:all,group,numbers',
            'is_active' => 'boolean',
        ]);

        $data['channel'] = $data['channel'] ?? $schedule->channel ?? 'whatsapp';
        $data['is_active'] = $request->boolean('is_active', true);
        $schedule->update($data);
        $schedule->computeNextRun();

        return back()->with('success', __('messages.success.recurring_updated'));
    }

    public function destroy(WaRecurring $schedule)
    {
        abort_if($schedule->user_id !== Auth::id(), 403);
        $schedule->delete();
        return back()->with('success', __('messages.success.recurring_deleted'));
    }

    public function toggle(WaRecurring $schedule)
    {
        abort_if($schedule->user_id !== Auth::id(), 403);
        $schedule->update(['is_active' => !$schedule->is_active]);
        return back()->with('success', $schedule->is_active ? __('messages.success.activated') : __('messages.success.deactivated'));
    }
}
