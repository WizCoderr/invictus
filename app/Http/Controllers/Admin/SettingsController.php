<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const KEYS = ['member_fee', 'visitor_fee', 'gst_percent'];

    public function index()
    {
        $values = [];

        foreach (self::KEYS as $key) {
            $values[$key] = (float) Setting::get($key, config("registration.{$key}"));
        }

        return view('admin.settings', ['values' => $values]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_fee' => ['required', 'numeric', 'min:0'],
            'visitor_fee' => ['required', 'numeric', 'min:0'],
            'gst_percent' => ['required', 'numeric', 'between:0,100'],
        ], [
            '*_fee.*' => 'Enter an amount of 0 or more.',
            'gst_percent.*' => 'Enter a percentage between 0 and 100.',
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, round((float) $value, 2));
        }

        return back()->with('status', 'Settings saved.');
    }
}
