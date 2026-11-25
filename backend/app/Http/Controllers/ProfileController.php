<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Show the signed-in user's profile + change-password form. */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['me' => $request->user()]);
    }

    /** Change the signed-in user's own password. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        // The User model casts "password" => "hashed", so assign the plain value.
        $request->user()->forceFill(['password' => $data['password']])->save();

        return back()->with('ok', 'Your password has been changed.');
    }
}
