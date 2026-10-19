<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Update student profile (photo, password).
     * Returns JSON for the modal AJAX request.
     */
    public function update(Request $request)
    {
        $student = $this->currentStudent();
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'photo' => ['nullable', 'image', 'max:5120'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $fullName = trim((string) ($validated['name'] ?? $student->full_name));
        $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = $nameParts[0] ?? $student->first_name ?? '';
        $lastName = $nameParts[count($nameParts) - 1] ?? $student->last_name ?? '';
        $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : ($student->middle_name ?? null);

        $student->fill([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
        ]);

        if ($request->hasFile('photo')) {
            if ($student->profile_photo) {
                Storage::disk('public')->delete($student->profile_photo);
            }
            $student->profile_photo = $request->file('photo')->store('profile-photos', 'public');
        }

        $student->save();

        $user->name = $fullName;
        $user->email = $validated['email'] ?? $user->email;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    private function currentStudent(): Student
    {
        $student = Auth::user()->student;
        abort_if($student === null, 403, 'No student profile linked.');
        return $student;
    }
}
