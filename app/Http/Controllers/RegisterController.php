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
        $rules = [
            'lastname'    => 'required|string|max:100',
            'firstname'   => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6|confirmed',
            'position_id' => 'nullable|exists:positions,id',
            'department'  => 'nullable|string|max:255',
            'office'      => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => implode(' ', $validator->errors()->all()),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $fullName = trim($request->firstname . ' ' . $request->lastname);
            $employeeId = explode('@', $request->email)[0];
            $user = User::create([
                'lastname'    => $request->lastname,
                'firstname'   => $request->firstname,
                'name'        => $fullName,
                'employee_id' => $employeeId,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'role'        => 'staff',
                'approved'    => false,
                'position_id' => $request->position_id,
                'department'  => $request->department,
                'office'      => $request->office,
            ]);

            $msg = 'Registration successful! Your account is pending admin approval.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            \Log::error('Registration Error: ' . $e->getMessage());
            $err = 'An error occurred during registration. Please try again.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $err], 500);
            }
            return redirect()->back()->with('error', $err);
        }
    }
}