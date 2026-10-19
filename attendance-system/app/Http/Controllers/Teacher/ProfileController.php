<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Update teacher profile (phone, email, photo, password).
     * Returns JSON for the modal AJAX request.
     */
    public function update(Request $request)
    {
        $teacher = $this->currentTeacher();
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('teachers', 'email')->ignore($teacher->id),
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'photo' => ['nullable', 'image', 'max:5120'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $fullName = trim((string) ($validated['name'] ?? $teacher->full_name));
        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = $nameParts[0] ?? $teacher->first_name ?? '';
        $lastName = $nameParts[count($nameParts) - 1] ?? $teacher->last_name ?? '';
        $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : ($teacher->middle_name ?? null);

        $teacher->fill([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'phone' => $validated['phone'] ?? $teacher->phone,
            'email' => $validated['email'] ?? $teacher->email,
        ]);

        if ($request->hasFile('photo')) {
            if ($teacher->profile_photo) {
                Storage::disk('public')->delete($teacher->profile_photo);
            }
            $teacher->profile_photo = $request->file('photo')->store('profile-photos', 'public');
        }

        $teacher->save();

        $user->name = $fullName;
        $user->email = $validated['email'] ?? $user->email;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    private function currentTeacher(): Teacher
    {
        $teacher = Auth::user()->teacher;
        abort_if($teacher === null, 403, 'No teacher profile linked.');
        return $teacher;
    }
}
