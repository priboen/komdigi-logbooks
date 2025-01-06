<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    public function getSupervisor()
    {
        $supervisor = User::where('roles', 'supervisor')->get();
        return response()->json([
            'message' => 'Success',
            'data' => $supervisor
        ], 200);
    }

    public function destroy($id)
    {
        $supervisor = User::find($id);
        $supervisor->delete();
        return response()->json([
            'message' => 'Supervisor deleted successfully',
            'data' => $supervisor
        ], 200);
    }
}
