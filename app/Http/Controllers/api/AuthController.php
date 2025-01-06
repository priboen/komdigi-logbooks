<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function studentRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required',
            'name' => 'required',
            'password' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);


        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'The email is already registered',
            ], 400);
        }

        if (User::where('phone', $request->phone)->exists()) {
            return response()->json([
                'message' => 'The phone number is already registered',
            ], 400);
        }

        $student = $request->all();
        $student['password'] = Hash::make($student['password']);
        $student['roles'] = 'student';

        $user = User::create($student);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photo_name = time() . '.' . $photo->getClientOriginalExtension();
            $filePath = $photo->storeAs('images/student', $photo_name, 'public');
            $user->photo = $filePath;
            $user->save();
        }

        return response()->json([
            'message' => 'Student registered successfully',
            'data' => $user
        ], 200);
    }

    public function supervisorRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required',
            'name' => 'required',
            'password' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);


        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'The email is already registered',
            ], 400);
        }

        if (User::where('phone', $request->phone)->exists()) {
            return response()->json([
                'message' => 'The phone number is already registered',
            ], 400);
        }

        $supervisor = $request->all();
        $supervisor['password'] = Hash::make($supervisor['password']);
        $supervisor['roles'] = 'supervisor';

        $user = User::create($supervisor);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photo_name = time() . '.' . $photo->getClientOriginalExtension();
            $filePath = $photo->storeAs('images/supervisor', $photo_name, 'public');
            $user->photo = $filePath;
            $user->save();
        }

        return response()->json([
            'message' => 'Supervisor registered successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->roles,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 200);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->roles,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout success'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|unique:users,phone,' . $user->id,
            'name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photo_name = time() . '.' . $photo->getClientOriginalExtension();
            $folder = match ($user->roles) {
                'supervisor' => 'images/supervisor',
                'student' => 'images/student',
                'admin' => 'images/admin',
                default => 'images/others',
            };
            $filePath = $photo->storeAs($folder, $photo_name, 'public');
            Log::info('Role: ' . $user->roles);
            Log::info('File path: ' . $filePath);
            if (Storage::disk('public')->exists($filePath)) {
                Log::info('File successfully saved at: ' . $filePath);
            } else {
                Log::info('Failed to save file.');
            }
            $user->photo = $filePath;
        }
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->roles,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'email_verified_at' => $user->email_verified_at,
            ],
        ], 200);
    }



    public function getSupervisor()
    {
        $supervisors = User::where('role', 'supervisor')->get();
        return response()->json([
            'message' => 'Supervisors fetched successfully',
            'data' => $supervisors
        ], 200);
    }
}
