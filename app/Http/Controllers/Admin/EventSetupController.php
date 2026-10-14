<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventSetupController extends Controller
{
    public function index()
    {
        return view('admin.event-setup', [
            'chapters' => Chapter::withCount('registrations')->orderBy('name')->get(),
            'businessCategories' => BusinessCategory::withCount('registrations')->orderBy('name')->get(),
        ]);
    }

    public function storeChapter(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('chapter', ['name' => ['required', 'string', 'max:120', 'unique:chapters,name']]);

        Chapter::create($data);

        return back()->with('status', "Chapter \"{$data['name']}\" added.");
    }

    public function destroyChapter(Chapter $chapter): RedirectResponse
    {
        if ($chapter->registrations()->exists()) {
            return back()->with('status', "Can't delete \"{$chapter->name}\" — it already has registrations against it.");
        }

        $chapter->delete();

        return back()->with('status', "Chapter \"{$chapter->name}\" deleted.");
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('category', ['name' => ['required', 'string', 'max:120', 'unique:business_categories,name']]);

        BusinessCategory::create($data);

        return back()->with('status', "Business category \"{$data['name']}\" added.");
    }

    public function destroyCategory(BusinessCategory $businessCategory): RedirectResponse
    {
        if ($businessCategory->registrations()->exists()) {
            return back()->with('status', "Can't delete \"{$businessCategory->name}\" — it already has registrations against it.");
        }

        $businessCategory->delete();

        return back()->with('status', "Business category \"{$businessCategory->name}\" deleted.");
    }
}
