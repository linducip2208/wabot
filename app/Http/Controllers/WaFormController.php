<?php

namespace App\Http\Controllers;

use App\Models\WaForm;
use App\Models\WaFormSubmission;
use App\Models\WaContact;
use App\Models\WaMetaAccount;
use App\Models\WaSession;
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaFormController extends Controller
{
    public function __construct(
        protected MetaApiService $meta,
    ) {}

    public function index()
    {
        $forms = WaForm::where('user_id', Auth::id())
            ->with('metaAccount')
            ->latest()
            ->get();

        return view('forms.index', compact('forms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'meta_account_id' => 'nullable|exists:wa_meta_accounts,id',
            'header_text' => 'nullable|string|max:60',
            'body_text' => 'nullable|string|max:1024',
            'components' => 'required|json',
        ]);

        $components = json_decode($validated['components'], true);

        WaForm::create([
            'user_id' => Auth::id(),
            'meta_account_id' => $validated['meta_account_id'] ?? null,
            'name' => $validated['name'],
            'header_text' => $validated['header_text'] ?? null,
            'body_text' => $validated['body_text'] ?? null,
            'components' => $components,
            'status' => 'draft',
        ]);

        return redirect()->route('forms.index')->with('success', __('messages.success.form_created'));
    }

    public function update(Request $request, WaForm $form)
    {
        abort_if($form->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'meta_account_id' => 'nullable|exists:wa_meta_accounts,id',
            'header_text' => 'nullable|string|max:60',
            'body_text' => 'nullable|string|max:1024',
            'components' => 'required|json',
        ]);

        $form->update([
            'name' => $validated['name'],
            'meta_account_id' => $validated['meta_account_id'] ?? $form->meta_account_id,
            'header_text' => $validated['header_text'] ?? null,
            'body_text' => $validated['body_text'] ?? null,
            'components' => json_decode($validated['components'], true),
        ]);

        return back()->with('success', __('messages.success.form_updated'));
    }

    public function destroy(WaForm $form)
    {
        abort_if($form->user_id !== Auth::id(), 403);
        $form->delete();
        return redirect()->route('forms.index')->with('success', __('messages.success.form_deleted'));
    }

    public function sendForm(Request $request, WaForm $form)
    {
        abort_if($form->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'meta_account_id' => 'required|exists:wa_meta_accounts,id',
        ]);

        $account = WaMetaAccount::where('user_id', Auth::id())
            ->findOrFail($validated['meta_account_id']);

        if ($account->status !== 'connected') {
            return back()->with('error', __('messages.error.meta_not_connected'));
        }

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);

        $sections = $this->buildFormSections($form);
        $result = $this->meta->sendInteractiveList(
            $account,
            $phone,
            $form->body_text ?? __('messages.forms.fill_form_prompt'),
            $form->header_text ?? __('messages.forms.form_header_default'),
            $sections
        );

        if (!empty($result['messages'])) {
            return back()->with('success', __('messages.success.form_sent_to', ['phone' => $phone]));
        }

        $error = $result['error']['message'] ?? 'Unknown error';
        return back()->with('error', __('messages.error.form_send_failed', ['error' => $error]));
    }

    public function sendBulk(Request $request, WaForm $form)
    {
        abort_if($form->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'meta_account_id' => 'required|exists:wa_meta_accounts,id',
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'exists:wa_contacts,id',
        ]);

        $account = WaMetaAccount::where('user_id', Auth::id())
            ->findOrFail($validated['meta_account_id']);

        $contacts = WaContact::where('user_id', Auth::id())
            ->whereIn('id', $validated['contact_ids'])
            ->get();

        $sections = $this->buildFormSections($form);
        $sent = 0;
        $failed = 0;

        foreach ($contacts as $contact) {
            $phone = preg_replace('/[^0-9]/', '', $contact->phone);
            $result = $this->meta->sendInteractiveList(
                $account,
                $phone,
                $form->body_text ?? __('messages.forms.fill_form_prompt'),
                $form->header_text ?? __('messages.forms.form_header_default'),
                $sections
            );

            if (!empty($result['messages'])) {
                $sent++;
            } else {
                $failed++;
            }

            usleep(500000);
        }

        return back()->with('success', __('messages.success.form_sent_result', ['sent' => $sent, 'failed' => $failed]));
    }

    public function submissions(WaForm $form)
    {
        abort_if($form->user_id !== Auth::id(), 403);

        $submissions = $form->submissions()->with('contact')->latest()->paginate(30);

        return view('forms.submissions', compact('form', 'submissions'));
    }

    public function exportSubmissions(WaForm $form)
    {
        abort_if($form->user_id !== Auth::id(), 403);

        $submissions = $form->submissions()->with('contact')->get();
        $components = $form->components ?? [];

        $csv = fopen('php://temp', 'r+');
        $headers = array_merge(['#', 'Phone', 'Nama', 'Tanggal'], array_column($components, 'label'));
        fputcsv($csv, $headers);

        foreach ($submissions as $i => $sub) {
            $row = [$i + 1, $sub->phone, $sub->contact?->name ?? '-', $sub->created_at->format('d/m/Y H:i')];
            foreach ($components as $comp) {
                $key = $comp['label'] ?? '';
                $row[] = $sub->data[$key] ?? '';
            }
            fputcsv($csv, $row);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="submissions-' . $form->id . '.csv"',
        ]);
    }

    protected function buildFormSections(WaForm $form): array
    {
        $components = $form->components ?? [];
        $rows = [];

        foreach ($components as $comp) {
            $type = $comp['type'] ?? 'text_input';
            $label = $comp['label'] ?? '';
            $required = $comp['required'] ?? false;
            $placeholder = $comp['placeholder'] ?? '';
            $options = $comp['options'] ?? [];

            $row = [
                'id' => 'field_' . uniqid(),
                'title' => $label . ($required ? ' *' : ''),
            ];

            if (in_array($type, ['text_input', 'text_area', 'number', 'email', 'phone_number', 'date_picker'])) {
                $row['description'] = $placeholder ?: __('messages.forms.enter_label', ['label' => strtolower($label)]);
            } elseif (in_array($type, ['dropdown', 'radio'])) {
                $row['description'] = __('messages.forms.select_label', ['label' => strtolower($label)]);
            } elseif ($type === 'checkbox') {
                $row['description'] = __('messages.forms.check_appropriate');
            }

            $rows[] = $row;
        }

        return [[
            'title' => $form->header_text ?? __('messages.forms.form_header_default'),
            'rows' => $rows,
        ]];
    }
}
