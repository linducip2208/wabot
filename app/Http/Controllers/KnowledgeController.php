<?php

namespace App\Http\Controllers;

use App\Models\WaKnowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KnowledgeController extends Controller
{
    public function index()
    {
        $entries = WaKnowledge::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('knowledge.index', compact('entries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:faq,csv',
            'faqs' => 'nullable|json',
            'summary' => 'nullable|string',
        ]);

        if ($validated['type'] === 'faq') {
            $faqs = json_decode($validated['faqs'] ?? '[]', true) ?: [];
            $rows = [];
            foreach ($faqs as $f) {
                $q = trim($f['question'] ?? '');
                $a = trim($f['answer'] ?? '');
                if ($q && $a) {
                    $rows[] = [
                        'question' => $q,
                        'answer' => $a,
                        'category' => trim($f['category'] ?? ''),
                    ];
                }
            }
            if (!$rows) {
                return back()->with('error', 'FAQ minimal memiliki 1 pasangan pertanyaan & jawaban.');
            }
            $content = json_encode(['rows' => $rows], JSON_UNESCAPED_UNICODE);
        } else {
            $summary = trim($validated['summary'] ?? '');
            if (mb_strlen($summary) < 20) {
                return back()->with('error', 'Summary minimal 20 karakter.');
            }
            $content = json_encode(['rows' => [['content' => $summary]]], JSON_UNESCAPED_UNICODE);
        }

        WaKnowledge::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $content,
            'type' => $validated['type'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Knowledge berhasil ditambahkan.');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            $row = [];
            foreach ($headers as $i => $h) {
                $row[trim(mb_strtolower($h))] = trim($line[$i] ?? '');
            }
            $q = $row['question'] ?? $row['pertanyaan'] ?? '';
            $a = $row['answer'] ?? $row['jawaban'] ?? '';
            if ($q && $a) {
                $rows[] = [
                    'question' => $q,
                    'answer' => $a,
                    'category' => $row['category'] ?? $row['kategori'] ?? '',
                ];
            }
        }
        fclose($handle);

        if (!$rows) {
            return back()->with('error', 'CSV tidak punya baris valid (perlu kolom question & answer).');
        }

        WaKnowledge::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => json_encode(['rows' => $rows], JSON_UNESCAPED_UNICODE),
            'type' => 'csv',
            'is_active' => true,
        ]);

        return back()->with('success', count($rows) . ' baris berhasil diimport.');
    }

    public function toggle(WaKnowledge $knowledge)
    {
        abort_if($knowledge->user_id !== Auth::id(), 403);
        $knowledge->update(['is_active' => !$knowledge->is_active]);

        return back()->with('success', $knowledge->is_active ? 'Diaktifkan.' : 'Dinonaktifkan.');
    }

    public function destroy(WaKnowledge $knowledge)
    {
        abort_if($knowledge->user_id !== Auth::id(), 403);
        $knowledge->delete();

        return back()->with('success', 'Knowledge dihapus.');
    }
}
