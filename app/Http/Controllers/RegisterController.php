<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        // Debug: Uncomment to see incoming data
        // dd($request->all());

        $validator = Validator::make($request->all(), [
            'lastname' => 'required|string|max:100',
            'firstname' => 'required|string|max:100',
            'employee_id' => 'required|string|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,staff,encoder,viewer'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::create([
                'lastname' => $request->lastname,
                'firstname' => $request->firstname,
                'employee_id' => $request->employee_id,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);
            
            return redirect()->back()->with('success', 'Registration successful! Welcome, ' . $user->name . '. You will be redirected shortly.');
}
            
        catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Registration Error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during registration. Please try again.');
        }
    }
}