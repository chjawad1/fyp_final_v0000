<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SupervisorProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Fetch all users with supervisor profiles, newest first, and paginate the results
        $users = User::with('supervisorProfile')->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $user->load('supervisorProfile');
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:student,supervisor,admin'],
        ];

        // Add supervisor-specific validation
        if ($request->role === 'supervisor') {
            $rules['available_slots'] = ['required', 'integer', 'min:0', 'max:20'];
            $rules['research_interests'] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);

        // Update user basic info
        $user->update([
            'name' => $validated['name'],
            'role' => $validated['role'],
        ]);

        // Handle supervisor profile creation/update/deletion
        if ($validated['role'] === 'supervisor') {
            // Create or update supervisor profile
            $user->supervisorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'available_slots' => $validated['available_slots'],
                    'research_interests' => $validated['research_interests'] ?? 'Please update your research interests.',
                ]
            );
        } else {
            // If user is no longer a supervisor, delete their profile
            $user->supervisorProfile()->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'User updated successfully! ');
    }

    public function toggleStatus(Request $request, User $user)
    {
        // Prevent admin from deactivating themselves
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $newStatus = $user->status === 'active' ?  'deactivated' : 'active';
        $user->update(['status' => $newStatus]);

        $message = "User has been successfully " . ($newStatus === 'active' ? 'reactivated' : 'deactivated') . ".";

        return redirect()->route('admin.users.index')->with('status', $message);
    }

    /**
     * Reset user password to default
     */
    public function resetPassword(Request $request, User $user)
    {
        // Prevent admin from resetting their own password this way
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot reset your own password using this method.']);
        }

        $defaultPassword = 'password123'; // You can change this default
        
        $user->update([
            'password' => Hash::make($defaultPassword),
            'password_reset_required' => true, // Optional: force password change on next login
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', "Password reset successfully for {$user->name}.  New password:  {$defaultPassword}");
    }

    /**
     * Update supervisor slots (dedicated endpoint)
     */
    public function updateSlots(Request $request, User $user)
    {
        if ($user->role !== 'supervisor') {
            return back()->withErrors(['error' => 'User is not a supervisor.']);
        }

        $validated = $request->validate([
            'available_slots' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $user->supervisorProfile()->updateOrCreate(
            ['user_id' => $user->id],
            ['available_slots' => $validated['available_slots']]
        );

        return redirect()->route('admin.users.index')
            ->with('status', "Available slots updated to {$validated['available_slots']} for {$user->name}.");
    }

    // Existing methods... 
    public function showUploadForm()
    {
        return view('admin.users.upload');
    }

    public function processUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');

        // Skip the header row
        $header = fgetcsv($file);

        $createdCount = 0;
        $errors = [];

        while (($row = fgetcsv($file)) !== false) {
            // Combine header and row to create an associative array
            $data = array_combine($header, $row);

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => ['required', 'string', Rule::in(['student', 'supervisor'])],
            ]);

            if ($validator->fails()) {
                $errors[] = "Invalid data for email {$data['email']}: " . $validator->errors()->first();
                continue;
            }

            // Create the user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // If the created user is a supervisor, also create their profile
            if ($user->role === 'supervisor') {
                $user->supervisorProfile()->create([
                    'available_slots' => 8, // Default slots
                    'research_interests' => 'Please update your profile.',
                ]);
            }

            $createdCount++;
        }

        fclose($file);

        if (count($errors) > 0) {
            return redirect()->route('admin.users.upload.form')
                ->with('error', "Process finished with errors. Created {$createdCount} users. Errors: " . implode('; ', $errors));
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Successfully created {$createdCount} new users.");
    }

    public function downloadTemplate()
    {
        $filename = "user_import_template.csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add the header row
            fputcsv($file, ['name', 'email', 'role']);
            
            // Add some example data
            fputcsv($file, ['Sample Student', 'student@example.com', 'student']);
            fputcsv($file, ['Sample Supervisor', 'supervisor@example.com', 'supervisor']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}