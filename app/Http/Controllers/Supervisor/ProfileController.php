<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SupervisorProfile;

class ProfileController extends Controller
{
    public function edit()
    {
        // Get the currently logged-in user
        $user = Auth::user();

        // Find the supervisor profile or create a new one if it doesn't exist
        $profile = $user->supervisorProfile()->firstOrCreate(
            ['user_id' => $user->id], // Condition to find the profile
            [] // Default values if creating (none needed here)
        );

        return view('supervisor.profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the supervisor's profile in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'research_interests' => ['required', 'string', 'max:1000'],
            'available_slots' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $user = Auth::user();
        $user->supervisorProfile->update($request->all());

        return redirect()->route('supervisor.profile.edit')->with('status', 'Profile updated successfully!');
    }
}
