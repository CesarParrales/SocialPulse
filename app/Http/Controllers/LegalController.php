<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function privacy(): Response
    {
        return $this->render('privacy');
    }

    public function terms(): Response
    {
        return $this->render('terms');
    }

    private function render(string $document): Response
    {
        $key = "app.legal.{$document}";

        return Inertia::render('Legal/Show', [
            'document' => $document,
            'title' => __("$key.title"),
            'updated' => __("$key.updated"),
            'intro' => __("$key.intro"),
            'sections' => trans("$key.sections"),
            'contactEmail' => config('app.legal_contact_email'),
        ]);
    }
}
