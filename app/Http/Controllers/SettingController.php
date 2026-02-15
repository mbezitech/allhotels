<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the settings.
     */
    public function index()
    {
        // Only super admins can manage settings
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $settings = \App\Models\Setting::all()->groupBy('group');
        return view('settings.index', compact('settings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        // Only super admins can manage settings
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            \App\Models\Setting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
