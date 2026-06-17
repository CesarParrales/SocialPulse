<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /** @var list<string> */
    private const SUPPORTED = ['es', 'en'];

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:es,en'],
        ]);

        $locale = $validated['locale'];

        if ($request->user() !== null) {
            $request->user()->update(['locale' => $locale]);
        }

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return back();
    }
}
