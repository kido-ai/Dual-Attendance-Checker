<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * GET /admin/settings -- route name: admin.settings.index
     */
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => SystemSetting::allKeyed(),
        ]);
    }

    /**
     * PUT /admin/settings -- route name: admin.settings.update
     *
     * Handles all five settings in one form POST. The logo upload is
     * optional -- if no new file is submitted, the existing logo is kept.
     * Removing the logo requires a separate button (see removeLogo()).
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'system_name'         => ['required', 'string', 'max:100'],
            'school_name'         => ['required', 'string', 'max:150'],
            'school_logo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'qr_expiration_days'  => ['required', 'integer', 'min:1', 'max:3650'],
            'face_match_threshold' => ['required', 'numeric', 'min:0.50', 'max:0.99'],
        ]);

        SystemSetting::set('system_name', $request->input('system_name'));
        SystemSetting::set('school_name', $request->input('school_name'));
        SystemSetting::set('qr_expiration_days', (string) $request->input('qr_expiration_days'));
        SystemSetting::set('face_match_threshold', number_format((float) $request->input('face_match_threshold'), 2));

        if ($request->hasFile('school_logo')) {
            $path = $request->file('school_logo')->store('settings', 'public');

            if (! $path) {
                throw new \RuntimeException('The school logo could not be stored.');
            }

            $existing = SystemSetting::get('school_logo');
            SystemSetting::set('school_logo', $path);

            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Settings saved successfully.');
    }

    /**
     * DELETE /admin/settings/logo -- route name: admin.settings.remove-logo
     * Removes the school logo without touching the other settings.
     */
    public function removeLogo(): RedirectResponse
    {
        SystemSetting::removeLogo();

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'School logo removed.');
    }

    public function reset(): RedirectResponse
    {
        SystemSetting::resetToDefaults();

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'All settings restored to their defaults.');
    }
}
