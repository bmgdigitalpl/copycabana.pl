<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CmsSectionRequest;
use App\Models\CmsSection;
use App\Services\CmsContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function index(CmsContent $content): RedirectResponse
    {
        return redirect()->route('admin.cms.edit', array_key_first($content->sections()));
    }

    public function edit(string $section, CmsContent $content): View
    {
        abort_unless(array_key_exists($section, $content->sections()), 404);

        return view('admin.cms.edit', [
            'section' => $section,
            'sections' => $content->sections(),
            'label' => $content->label($section),
            'payload' => $content->section($section),
        ]);
    }

    public function update(CmsSectionRequest $request, string $section, CmsContent $content): RedirectResponse
    {
        abort_unless(array_key_exists($section, $content->sections()), 404);

        CmsSection::query()->updateOrCreate(
            ['key' => $section],
            [
                'name' => $content->label($section),
                'payload' => $request->validated('payload'),
            ],
        );

        return redirect()->route('admin.cms.edit', $section)->with('status', 'Treści CMS zostały zapisane.');
    }
}
