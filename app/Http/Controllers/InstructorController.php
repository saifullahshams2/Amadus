<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
use App\Models\Pnr;
use App\Models\QueueItem;
use App\Models\Submission;
use App\Models\TerminalActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstructorController extends Controller
{
    /**
     * Dedicated Instructor Hub view.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login')->withErrors(['error' => 'Instructor access required.']);
        }

        if ($user->role !== 'instructor') {
            abort(403, 'Forbidden. Instructor access required.');
        }

        $students = User::where('role', 'student')
            ->where('is_approved', true)
            ->where('office_id', $user->office_id)
            ->select(['id', 'username', 'name', 'email', 'office_id', 'agent_id', 'is_approved', 'created_at'])
            ->get();
        $pendingStudents = User::where('role', 'student')
            ->where('is_approved', false)
            ->where('office_id', $user->office_id)
            ->select(['id', 'username', 'name', 'email', 'office_id', 'agent_id', 'is_approved', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();
        $officeUsernames = $students->pluck('username')->merge($pendingStudents->pluck('username'));
        $progress = LessonProgress::whereIn('user_id', $officeUsernames)->get();
        $pnrs = Pnr::where('office_id', $user->office_id)->orderBy('updated_at', 'desc')->limit(100)->get();

        return view('instructor.index', compact('user', 'students', 'pendingStudents', 'progress', 'pnrs'));
    }

    /**
     * API: Get all enrolled students and their learning progress.
     */
    public function studentsApi(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'instructor') {
            return response()->json(['error' => 'Instructor role required'], 403);
        }

        $students = User::where('role', 'student')
            ->where('is_approved', true)
            ->where('office_id', $user->office_id)
            ->select(['id', 'username', 'name', 'email', 'office_id', 'agent_id', 'is_approved', 'created_at'])
            ->get();
        $pendingStudents = User::where('role', 'student')
            ->where('is_approved', false)
            ->where('office_id', $user->office_id)
            ->select(['id', 'username', 'name', 'email', 'office_id', 'agent_id', 'is_approved', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();
        $officeUsernames = $students->pluck('username')->merge($pendingStudents->pluck('username'));
        $progressRecords = LessonProgress::whereIn('user_id', $officeUsernames)->get();
        $pnrs = Pnr::where('office_id', $user->office_id)->orderBy('updated_at', 'desc')->limit(100)->get();

        return response()->json([
            'students' => $students,
            'pendingStudents' => $pendingStudents,
            'progressRecords' => $progressRecords,
            'pnrs' => $pnrs,
        ]);
    }

    /**
     * Create a single new student account from the instructor dashboard.
     */
    public function storeStudent(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'instructor') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Instructor role required'], 403);
            }
            abort(403, 'Forbidden. Instructor access required.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_\-\.]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'office_id' => ['nullable', 'string', 'max:20'],
            'agent_id' => ['nullable', 'string', 'max:10'],
        ]);

        $officeId = ! empty($validated['office_id'])
            ? strtoupper(trim($validated['office_id']))
            : ($user->office_id ?? 'LON1A0980');

        $cleanUsername = strtolower(trim($validated['username']));

        $agentId = ! empty($validated['agent_id'])
            ? strtoupper(trim($validated['agent_id']))
            : strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $cleanUsername), 0, 4).rand(10, 99));

        $student = User::create([
            'name' => trim($validated['name']),
            'username' => $cleanUsername,
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => 'student', // Strictly enforced as student
            'is_approved' => true,
            'office_id' => $officeId,
            'agent_id' => $agentId,
        ]);

        $studentPayload = [
            'id' => $student->id,
            'username' => $student->username,
            'name' => $student->name,
            'email' => $student->email,
            'office_id' => $student->office_id,
            'agent_id' => $student->agent_id,
            'created_at' => $student->created_at->format('d M Y'),
            'status' => 'Idle',
            'last_command' => 'None',
            'last_active' => 'Never',
            'total_commands' => 0,
            'submissions_count' => 0,
            'active_pnr_locator' => null,
            'error_rate' => 0,
        ];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Student account '{$student->username}' created successfully.",
                'student' => $studentPayload,
            ], 201);
        }

        return redirect()->back()->with('success', "Student '{$student->username}' created successfully.");
    }

    /**
     * Batch create unlimited student accounts from the instructor dashboard.
     */
    public function batchStoreStudents(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'instructor') {
            return response()->json(['error' => 'Instructor role required'], 403);
        }

        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:50'],
            'prefix' => ['nullable', 'string', 'max:30', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'password' => ['nullable', 'string', 'min:6'],
            'office_id' => ['nullable', 'string', 'max:20'],
        ]);

        $count = (int) $validated['count'];
        $prefix = strtolower(trim($validated['prefix'] ?? 'student'));
        // No calendar-derived default: a predictable shared password lets anyone
        // log in as any provisioned cadet. Each account gets its own random password.
        $instructorPassword = $validated['password'] ?? null;
        $officeId = ! empty($validated['office_id'])
            ? strtoupper(trim($validated['office_id']))
            : ($user->office_id ?? 'LON1A0980');

        $createdStudents = [];
        $seq = 1;

        while (count($createdStudents) < $count) {
            $candidateUsername = "{$prefix}_{$seq}";
            $seq++;

            // If username exists, continue searching
            if (User::where('username', $candidateUsername)->exists()) {
                continue;
            }

            $candidateEmail = "{$candidateUsername}@classroom.local";
            if (User::where('email', $candidateEmail)->exists()) {
                $candidateEmail = "{$candidateUsername}_".Str::random(4).'@classroom.local';
            }

            $agentId = 'ST'.rand(10, 99).strtoupper(Str::random(2));

            $studentPassword = $instructorPassword ?? Str::password(16, symbols: false);
            $hashedPassword = Hash::make($studentPassword);

            $student = User::create([
                'name' => ucwords(str_replace(['_', '-'], ' ', $candidateUsername)),
                'username' => $candidateUsername,
                'email' => $candidateEmail,
                'password' => $hashedPassword,
                'role' => 'student', // Strictly enforced as student
                'is_approved' => true,
                'office_id' => $officeId,
                'agent_id' => $agentId,
            ]);

            $createdStudents[] = [
                'id' => $student->id,
                'username' => $student->username,
                'name' => $student->name,
                'email' => $student->email,
                'plain_password' => $studentPassword,
                'office_id' => $student->office_id,
                'agent_id' => $student->agent_id,
                'created_at' => $student->created_at->format('d M Y'),
                'status' => 'Idle',
                'last_command' => 'None',
                'last_active' => 'Never',
                'total_commands' => 0,
                'submissions_count' => 0,
                'active_pnr_locator' => null,
                'error_rate' => 0,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully provisioned {$count} student accounts.",
            'count' => count($createdStudents),
            'default_password' => $instructorPassword,
            'students' => $createdStudents,
        ], 201);
    }

    /**
     * Approve a pending cadet registration.
     */
    public function approveStudent(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'instructor') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Instructor role required'], 403);
            }
            abort(403, 'Forbidden. Instructor access required.');
        }

        $student = User::where('id', $id)->where('role', 'student')
            ->where('office_id', $user->office_id)
            ->firstOrFail();
        $student->is_approved = true;
        $student->save();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Cadet '{$student->username}' approved successfully.",
                'student' => $student,
            ]);
        }

        return redirect()->back()->with('success', "Cadet '{$student->username}' approved successfully.");
    }

    /**
     * Reject and remove a pending cadet registration.
     */
    public function rejectStudent(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'instructor') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Instructor role required'], 403);
            }
            abort(403, 'Forbidden. Instructor access required.');
        }

        $student = User::where('id', $id)->where('role', 'student')
            ->where('office_id', $user->office_id)
            ->firstOrFail();
        $username = $student->username;
        $student->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Cadet registration for '{$username}' was rejected and removed.",
            ]);
        }

        return redirect()->back()->with('success', "Cadet registration for '{$username}' was rejected.");
    }

    /**
     * Delete an enrolled student account and cascade remove all their simulation data.
     */
    public function destroyStudent(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'instructor') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Instructor role required'], 403);
            }
            abort(403, 'Forbidden. Instructor access required.');
        }

        $student = User::where('id', $id)->where('role', 'student')
            ->where('office_id', $user->office_id)
            ->firstOrFail();
        $username = $student->username;

        // Cascade delete all student simulation and activity records
        Pnr::where('user_id', $username)->delete();
        LessonProgress::where('user_id', $username)->delete();
        Submission::where('user_id', $username)->delete();
        TerminalActivityLog::where('user_id', $username)->delete();
        QueueItem::where('target_user_id', $username)->delete();

        $student->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Student account '{$username}' and associated data have been permanently deleted.",
            ]);
        }

        return redirect()->back()->with('success', "Student account '{$username}' was permanently deleted.");
    }

    /**
     * Direct login into a student's portal without requiring a password.
     */
    public function impersonateStudent(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $currentUser = Auth::user();
        if (! $currentUser || $currentUser->role !== 'instructor') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Instructor role required'], 403);
            }
            abort(403, 'Forbidden. Instructor access required.');
        }

        $student = User::where('id', $id)->where('role', 'student')
            ->where('office_id', $currentUser->office_id)
            ->firstOrFail();
        $instructorId = $currentUser->id;

        Auth::login($student);
        $request->session()->regenerate();

        $request->session()->put('impersonated_by_instructor', $instructorId);
        $request->session()->put('impersonated_student_id', $student->id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Logged in directly as cadet '{$student->username}'.",
                'redirect' => '/dashboard',
            ]);
        }

        return redirect()->route('dashboard')->with('success', "Logged into cadet '{$student->username}' portal.");
    }

    /**
     * Exit cadet impersonation and restore instructor session.
     */
    public function exitImpersonation(Request $request): RedirectResponse
    {
        $instructorId = $request->session()->get('impersonated_by_instructor');

        if (! $instructorId) {
            return redirect()->route('dashboard');
        }

        $instructor = User::where('id', $instructorId)->where('role', 'instructor')->first();

        if ($instructor) {
            $request->session()->forget(['impersonated_by_instructor', 'impersonated_student_id']);
            Auth::login($instructor);
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'Returned to Instructor Operations Hub.');
        }

        $request->session()->forget(['impersonated_by_instructor', 'impersonated_student_id']);

        return redirect()->route('login');
    }
}
