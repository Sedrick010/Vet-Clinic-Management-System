<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FirebaseAuthController extends Controller
{
    /**
     * Show the Firebase login view
     */
    public function showLoginForm()
    {
        return view('auth.firebase-login');
    }
    
    /**
     * Handle Firebase authentication for admins
     */
    public function handleCallback(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'firebase_uid' => 'required|string',
            'id_token' => 'required|string',
        ]);
        
        // Verify this is an admin email
        $adminEmails = config('firebase.admin_emails', []);
        
        if (!in_array($request->email, $adminEmails)) {
            return response()->json([
                'success' => false,
                'message' => 'This email is not authorized for admin access'
            ], 403);
        }
        
        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // Create new user with admin role
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make(Str::random(24)); // Random secure password
            $user->role = 'admin'; // Set role to admin
            $user->firebase_uid = $request->firebase_uid;
            $user->save();
        } else {
            // Update existing user
            $user->firebase_uid = $request->firebase_uid;
            $user->save();
        }
        
        // Log the user in
        Auth::login($user);
        
        return response()->json([
            'success' => true,
            'redirect' => route('admin.dashboard')
        ]);
    }
    
    /**
     * Log the user out
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
