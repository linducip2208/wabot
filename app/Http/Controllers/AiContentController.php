<?php

namespace App\Http\Controllers;

use App\Models\WaAiKey;
use App\Models\WaAiTemplate;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiContentController extends Controller
{
    public function index()
    {
        $templates = WaAiTemplate::where(function ($q) {
            $q->where('user_id', Auth::id())->orWhere('is_public', true);
        })->latest()->get();

        $history = session('ai_content_history', []);

        return view('ai-content.index', compact('templates', 'history'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:5000',
            'platform' => 'nullable|string|in:whatsapp,instagram,facebook,twitter,telegram,email,sms,general',
            'tone' => 'nullable|string|in:professional,casual,humorous,persuasive,urgent',
            'length' => 'nullable|string|in:short,medium,long',
            'language' => 'nullable|string|in:id,en,auto',
            'template_id' => 'nullable|integer|exists:wa_ai_templates,id',
        ]);

        $aiKey = WaAiKey::where('user_id', Auth::id())->where('is_active', true)->first();
        if (!$aiKey) {
            return back()->with('error', __('aistudio.no_active_ai_key'));
        }

        $prompt = $this->buildSystemPrompt($validated);
        $userMessage = $validated['prompt'];

        if (!empty($validated['template_id'])) {
            $template = WaAiTemplate::find($validated['template_id']);
            if ($template && ($template->user_id === Auth::id() || $template->is_public)) {
                $userMessage = str_replace(
                    ['{prompt}', '{platform}', '{tone}', '{language}'],
                    [$userMessage, $validated['platform'] ?? 'general', $validated['tone'] ?? 'professional', $validated['language'] ?? 'id'],
                    $template->prompt_template
                );
            }
        }

        $aiService = app(AiService::class);
        $result = $aiService->rawPrompt($aiKey, $prompt . "\n\nUser: " . $userMessage);

        if ($result === null) {
            return back()->with('error', __('messages.error.ai_response_failed'))->withInput();
        }

        $history = session('ai_content_history', []);
        array_unshift($history, [
            'prompt' => $userMessage,
            'result' => $result,
            'platform' => $validated['platform'] ?? 'general',
            'tone' => $validated['tone'] ?? 'professional',
            'created_at' => now()->toDateTimeString(),
        ]);
        $history = array_slice($history, 0, 20);
        session(['ai_content_history' => $history]);

        return back()->with('success', __('aistudio.content_generated'))
            ->with('generated_content', $result)
            ->with('generated_prompt', $userMessage)
            ->withInput();
    }

    public function templates()
    {
        $templates = WaAiTemplate::where('user_id', Auth::id())->latest()->get();
        $publicTemplates = WaAiTemplate::where('is_public', true)->where('user_id', '!=', Auth::id())->latest()->get();

        return view('ai-content.templates', compact('templates', 'publicTemplates'));
    }

    public function templateStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prompt_template' => 'required|string|max:10000',
            'category' => 'nullable|string|max:100',
            'is_public' => 'boolean',
        ]);

        WaAiTemplate::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'prompt_template' => $validated['prompt_template'],
            'category' => $validated['category'] ?? 'general',
            'is_public' => $validated['is_public'] ?? false,
        ]);

        return back()->with('success', __('aistudio.template_created'));
    }

    public function templateUpdate(Request $request, WaAiTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prompt_template' => 'required|string|max:10000',
            'category' => 'nullable|string|max:100',
            'is_public' => 'boolean',
        ]);

        $template->update([
            'name' => $validated['name'],
            'prompt_template' => $validated['prompt_template'],
            'category' => $validated['category'] ?? 'general',
            'is_public' => $validated['is_public'] ?? false,
        ]);

        return back()->with('success', __('aistudio.template_updated'));
    }

    public function templateDestroy(WaAiTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);
        $template->delete();
        return back()->with('success', __('aistudio.template_deleted'));
    }

    protected function buildSystemPrompt(array $options): string
    {
        $platform = $options['platform'] ?? 'general';
        $tone = $options['tone'] ?? 'professional';
        $length = $options['length'] ?? 'medium';
        $lang = $options['language'] ?? 'id';

        $platformGuides = [
            'whatsapp' => 'Format untuk WhatsApp: singkat, friendly, pakai emoji secukupnya, maksimal 3-4 baris.',
            'instagram' => 'Format untuk Instagram caption: storytelling, hashtag relevan di akhir, engaging.',
            'facebook' => 'Format untuk Facebook post: bisa lebih panjang, storytelling, ajak diskusi.',
            'twitter' => 'Format untuk X/Twitter: maksimal 280 karakter, to the point, catchy.',
            'telegram' => 'Format untuk Telegram: bisa panjang dengan formatting, informatif.',
            'email' => 'Format untuk email marketing: subject line menarik, body terstruktur, CTA jelas.',
            'sms' => 'Format untuk SMS: sangat singkat, maksimal 160 karakter, langsung ke poin.',
        ];

        $toneGuides = [
            'professional' => 'Gunakan tone formal dan profesional.',
            'casual' => 'Gunakan tone santai, friendly, seperti ngobrol dengan teman.',
            'humorous' => 'Gunakan tone lucu dan menghibur.',
            'persuasive' => 'Gunakan tone persuasif untuk meyakinkan pembaca.',
            'urgent' => 'Gunakan tone urgensi, FOMO, batas waktu.',
        ];

        $lengthGuides = [
            'short' => 'Jawaban singkat, maksimal 2-3 kalimat.',
            'medium' => 'Jawaban sedang, 1-2 paragraf.',
            'long' => 'Jawaban panjang dan detail, 3+ paragraf.',
        ];

        $langGuide = $lang === 'auto' ? '' : ($lang === 'en' ? 'Tulis dalam Bahasa Inggris.' : 'Tulis dalam Bahasa Indonesia.');

        $guide = ($platformGuides[$platform] ?? '') . "\n"
            . ($toneGuides[$tone] ?? '') . "\n"
            . ($lengthGuides[$length] ?? '') . "\n"
            . $langGuide;

        return "Kamu adalah AI content writer profesional. {$guide}\n\nTulis konten berdasarkan briefing user berikut.";
    }
}
