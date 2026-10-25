<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
use App\Models\Pnr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'instructor') {
            return redirect('/login')->withErrors(['error' => 'Instructor access required.']);
        }

        $students = User::where('role', 'student')->get();
        $progress = LessonProgress::all();
        $pnrs = Pnr::orderBy('updated_at', 'desc')->get();

        return view('instructor.index', compact('user', 'students', 'progress', 'pnrs'));
    }

    public function studentsApi(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'instructor') {
            return response()->json(['error' => 'Instructor role required'], 403);
        }

        $students = User::where('role', 'student')->get();
        $progressRecords = LessonProgress::all();
        $pnrs = Pnr::orderBy('updated_at', 'desc')->get();

        return response()->json([
            'students' => $students,
            'progressRecords' => $progressRecords,
            'pnrs' => $pnrs,
        ]);
    }
}
