<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'leader_id' => 'required|exists:users,id',
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'supervisor_id' => 'required|exists:users,id',
        ]);

        $internship = Internship::create([
            'leader_id' => $request->leader_id,
            'project_id' => $request->project_id,
            'supervisor_id' => $request->supervisor_id,
            'status' => 'pending',
        ]);

        $internship->members()->attach($request->member_ids);
        return response()->json(['status' => 201, 'message' => 'Pendaftaran magang berhasil.', 'data' => $internship], 201);
    }

    public function show($id)
    {
        $internship = Internship::with(['leader', 'project', 'supervisor', 'members'])->findOrFail($id);
        if (!$internship) {
            return response()->json(['status' => 404, 'message' => 'Data magang tidak ditemukan.'], 404);
        }
        return response()->json(['status' => 200, 'message' => 'Data magang berhasil didapatkan', 'data' => $internship], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $internship = Internship::findOrFail($id);
        $internship->update($request->only('status'));
        return response()->json(['status' => 201, 'message' => 'Status magang berhasil diubah.', 'data' => $internship], 201);
    }

    public function destroy($id)
    {
        $internship = Internship::findOrFail($id);
        $internship->delete();
        return response()->json(['status' => 201, 'message' => 'Magang berhasil dibatalkan.'], 201);
    }

    public function index()
    {
        $internships = Internship::with(['leader', 'project', 'supervisor', 'members'])->get();
        if ($internships->isEmpty()) {
            return response()->json(['status' => 404, 'message' => 'Belum ada data magang yang tersedia.'], 404);
        }
        return response()->json(['status' => 200, 'message' => 'Data magang berhasil didapatkan', 'data' => $internships], 200);
    }

    public function getInternshipsByUserId($userId)
    {
        $internships = Internship::with(['leader', 'project', 'supervisor', 'members'])
            ->where('leader_id', $userId)
            ->orWhereHas('members', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get();

        if ($internships->isEmpty()) {
            return response()->json(['status' => 404, 'message' => 'Belum ada data magang untuk user ini.'], 404);
        }

        return response()->json(['status' => 200, 'message' => 'Data magang berhasil didapatkan', 'data' => $internships], 200);
    }

    public function getInternshipsBySupervisorId($supervisorId)
    {
        $internships = Internship::with(['leader', 'project', 'members'])
            ->where('supervisor_id', $supervisorId)
            ->get();

        if ($internships->isEmpty()) {
            return response()->json(['status' => 404, 'message' => 'Belum ada data magang untuk supervisor ini.'], 404);
        }

        return response()->json(['status' => 200, 'message' => 'Data magang berhasil didapatkan', 'data' => $internships], 200);
    }

    public function getNearestSchedule(){
        
    }
}
