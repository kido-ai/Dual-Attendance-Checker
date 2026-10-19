<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Admin CRUD for teachers.
 */
class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = Teacher::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($where) use ($search) {
                    $where->where('employee_id', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', strtolower($request->status));
            }, function ($query) {
                $query->where('status', '!=', 'archived');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $teachers->getCollection()->transform(function ($teacher) {
            return [
                'id' => $teacher->id,
                'employee_id' => $teacher->employee_id,
                'full_name' => $teacher->full_name,
                'email' => $teacher->email ?? ($teacher->user->email ?? 'N/A'),
                'phone' => $teacher->phone ?? 'N/A',
                'status' => ucfirst($teacher->status ?? 'active'),
                'profile_photo' => $teacher->profile_photo ? Storage::url($teacher->profile_photo) : null,
                'created_at' => $teacher->created_at?->format('M d, Y'),
            ];
        });

        $stats = [
            ['icon' => 'briefcase', 'tone' => 'emerald', 'label' => 'Total Teachers', 'value' => number_format(Teacher::count()), 'trend' => 'up', 'trendValue' => '+2.1%'],
            ['icon' => 'check-circle', 'tone' => 'blue', 'label' => 'Active Teachers', 'value' => number_format(Teacher::where('status', 'active')->count()), 'trend' => 'up', 'trendValue' => '+1.4%'],
            ['icon' => 'no-symbol', 'tone' => 'amber', 'label' => 'Inactive Teachers', 'value' => number_format(Teacher::where('status', 'inactive')->count()), 'trend' => 'down', 'trendValue' => '-0.8%'],
            ['icon' => 'user-plus', 'tone' => 'indigo', 'label' => 'New This Month', 'value' => number_format(Teacher::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count()), 'trend' => 'up', 'trendValue' => '+3'],
        ];

        return view('admin.teachers.index', compact('teachers', 'stats'));
    }

    public function create(): View
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:30', 'unique:teachers,employee_id'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
                'password' => Hash::make($validated['employee_id']),
                'role' => 'teacher',
            ]);

            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('teachers/photos', 'public');
            }

            Teacher::create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'profile_photo' => $photoPath,
                'status' => $validated['status'] ?? 'active',
            ]);
        });

        return redirect()
            ->route('admin.teachers.index')
            ->with('status', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher): View
    {
        $teacher->load('user');
        $teacher->setRelation('attendances', $teacher->attendances()->latest()->take(5)->get());

        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load('user');

        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:30', Rule::unique('teachers', 'employee_id')->ignore($teacher->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($teacher->profile_photo) {
                Storage::disk('public')->delete($teacher->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('teachers/photos', 'public');
        }

        $teacher->update($validated);

        if ($teacher->user) {
            $teacher->user->update([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
            ]);
        }

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('status', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['status' => 'archived']);

        return redirect()
            ->route('admin.teachers.index')
            ->with('status', 'Teacher archived.');
    }

    public function archived(Request $request): View
    {
        $archivedTeachers = Teacher::with('user')
            ->where('status', 'archived')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($where) use ($search) {
                    $where->where('employee_id', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $archivedTeachers->getCollection()->transform(function ($teacher) {
            return [
                'id' => $teacher->id,
                'employee_id' => $teacher->employee_id,
                'full_name' => $teacher->full_name,
                'email' => $teacher->email ?? ($teacher->user->email ?? 'N/A'),
                'phone' => $teacher->phone ?? 'N/A',
                'status' => ucfirst($teacher->status ?? 'active'),
                'profile_photo' => $teacher->profile_photo ? Storage::url($teacher->profile_photo) : null,
                'created_at' => $teacher->created_at?->format('M d, Y'),
            ];
        });

        return view('admin.teachers.archived', compact('archivedTeachers'));
    }

    public function archive(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['status' => 'archived']);

        return redirect()->route('admin.teachers.index')
            ->with('status', 'Teacher archived successfully.');
    }

    public function restore(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['status' => 'active']);

        return redirect()->route('admin.teachers.archived')
            ->with('status', 'Teacher restored successfully.');
    }

    public function forceDelete(Teacher $teacher): RedirectResponse
    {
        $user = $teacher->user;
        $teacher->delete();

        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.teachers.archived')
            ->with('status', 'Teacher permanently deleted.');
    }
}
