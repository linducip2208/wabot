<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Language;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->determineLocale($request);

        App::setLocale($locale);

        if ($language = $this->getLanguage($locale)) {
            Session::put('locale', $locale);
            Session::put('language_rtl', $language->isRtl());
        }

        return $next($request);
    }

    protected function determineLocale(Request $request): string
    {
        if (Session::has('locale')) {
            return Session::get('locale');
        }

        if (auth()->check() && auth()->user()->language_id) {
            $lang = Language::find(auth()->user()->language_id);
            if ($lang && $lang->is_active) {
                return $lang->iso;
            }
        }

        $default = Language::where('is_default', true)->where('is_active', true)->first();
        if ($default) {
            return $default->iso;
        }

        $browserLocales = $request->getLanguages();
        foreach ($browserLocales as $browserLocale) {
            $short = substr($browserLocale, 0, 2);
            $lang = Language::where('iso', $short)->where('is_active', true)->first();
            if ($lang) {
                return $lang->iso;
            }
        }

        return config('app.fallback_locale', 'id');
    }

    protected function getLanguage(?string $locale): ?Language
    {
        return Language::where('iso', $locale)->first();
    }
}
