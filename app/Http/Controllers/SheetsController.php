<?php

namespace App\Http\Controllers;

use App\Models\WaSheetsIntegration;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SheetsController extends Controller
{
    public function __construct(
        protected GoogleSheetsService $googleSheets,
    ) {}

    public function index()
    {
        $integrations = WaSheetsIntegration::where('user_id', Auth::id())->latest()->get();
        $directions = ['import' => __('sheets.import'), 'export' => __('sheets.export'), 'both' => __('sheets.both')];
        return view('sheets.index', compact('integrations', 'directions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'spreadsheet_id' => 'required|string|max:200',
            'sheet_name' => 'required|string|max:100',
            'service_account_json' => 'required|string',
            'sync_direction' => 'required|in:import,export,both',
        ]);

        json_decode($validated['service_account_json']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', __('messages.error.invalid_json', ['field' => 'Service Account JSON']))->withInput();
        }

        WaSheetsIntegration::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'spreadsheet_id' => $validated['spreadsheet_id'],
            'sheet_name' => $validated['sheet_name'],
            'service_account_json' => $validated['service_account_json'],
            'sync_direction' => $validated['sync_direction'],
            'is_active' => true,
            'sync_status' => 'never',
        ]);

        return back()->with('success', __('messages.success.sheets_integration_added'));
    }

    public function connect(WaSheetsIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $result = $this->googleSheets->testConnection($integration);

        if ($result['success']) {
            $integration->update(['is_active' => true]);
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function sync(WaSheetsIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $stats = $this->googleSheets->syncContacts($integration);

        $msg = __('messages.success.sheets_synced', [
            'imported' => $stats['imported'],
            'exported' => $stats['exported'],
        ]);

        if ($stats['errors'] > 0) {
            return back()->with('warning', $msg . ' (' . __('common.with_errors') . ')');
        }

        return back()->with('success', $msg);
    }

    public function update(Request $request, WaSheetsIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'spreadsheet_id' => 'required|string|max:200',
            'sheet_name' => 'required|string|max:100',
            'service_account_json' => 'nullable|string',
            'sync_direction' => 'required|in:import,export,both',
        ]);

        $data = [
            'name' => $validated['name'],
            'spreadsheet_id' => $validated['spreadsheet_id'],
            'sheet_name' => $validated['sheet_name'],
            'sync_direction' => $validated['sync_direction'],
        ];

        if (!empty($validated['service_account_json'])) {
            json_decode($validated['service_account_json']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', __('messages.error.invalid_json', ['field' => 'Service Account JSON']))->withInput();
            }
            $integration->service_account_json = $validated['service_account_json'];
        }

        $integration->update($data);

        return back()->with('success', __('messages.success.sheets_integration_updated'));
    }

    public function destroy(WaSheetsIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $integration->delete();

        return back()->with('success', __('messages.success.sheets_integration_deleted'));
    }
}
