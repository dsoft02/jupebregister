<?php

namespace App\Http\Controllers;

use App\Actions\Students\CreateStudent;
use App\Http\Requests\Student\PublicRegistrationRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.student-register', [
            'subjects' => Subject::active()->orderBy('name')->get(),
        ]);
    }

    public function store(PublicRegistrationRequest $request, CreateStudent $createStudent): RedirectResponse
    {
        $student = $createStudent->run($request->validated(), public: true);

        return redirect()->route('register.success', ['student' => $student])
            ->with('status', 'Registration submitted successfully.');
    }

    public function success(\App\Models\Student $student): View
    {
        return view('auth.student-register-success', [
            'student' => $student->load('subjectOne', 'subjectTwo', 'subjectThree'),
        ]);
    }
}
