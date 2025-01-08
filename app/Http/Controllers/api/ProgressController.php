<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\Progress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function getProgressDetailsByInternshipId(Request $request, $internshipId)
{
    // Dapatkan user yang sedang login
    $user = $request->user();

    // Validasi apakah internship_id valid
    $internship = Internship::with(['leader', 'project', 'supervisor'])
        ->find($internshipId);

    if (!$internship) {
        return response()->json([
            'status' => 404,
            'message' => 'Data magang tidak ditemukan.',
        ], 404);
    }

    // Verifikasi apakah user login adalah supervisor terkait
    if ($internship->supervisor_id !== $user->id) {
        return response()->json([
            'status' => 403,
            'message' => 'Anda tidak memiliki izin untuk mengakses data ini.',
        ], 403);
    }

    // Ambil semua progress yang terkait dengan internship_id
    $progressDetails = Progress::where('internship_id', $internshipId)
        ->get()
        ->map(function ($progress) use ($internship) {
            return [
                'id' => $progress->id,
                'meeting' => $progress->meeting,
                'date' => $progress->date,
                'file_url' => $progress->file_url,
                'name' => $progress->name,
                'leader_name' => $internship->leader->name ?? 'Unknown',
                'project_name' => $internship->project->name ?? 'Unknown',
                'supervisor_name' => $internship->supervisor->name ?? 'Unknown',
            ];
        });

    return response()->json([
        'status' => 200,
        'message' => 'Detail progress berhasil diambil.',
        'data' => $progressDetails,
    ], 200);
}

    public function getUserProgressForSupervisor(Request $request)
    {
        // Ambil ID pengguna yang sedang login
        $loggedInUserId = $request->user()->id;

        // Validasi apakah user adalah supervisor yang sesuai
        $isValidSupervisor = Internship::where('supervisor_id', $loggedInUserId)->exists();

        if (!$isValidSupervisor) {
            return response()->json([
                'status' => 403,
                'message' => 'Anda tidak memiliki akses untuk data ini.',
            ], 403);
        }

        // Query untuk mendapatkan progress berdasarkan supervisor_id
        $progress = Progress::with(['internship.leader', 'internship.project'])
            ->whereHas('internship', function ($query) use ($loggedInUserId) {
                $query->where('supervisor_id', $loggedInUserId);
            })
            ->get();

        // Mengelompokkan progress berdasarkan internship_id
        $groupedProgress = $progress->groupBy('internship_id')->map(function ($progressGroup) {
            return [
                'internship_id' => $progressGroup->first()->internship->id ?? null,
                'leader_name' => $progressGroup->first()->internship->leader->name ?? 'Unknown',
                'project_name' => $progressGroup->first()->internship->project->name ?? 'Unknown',
                'progress' => $progressGroup->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'meeting' => $item->meeting,
                        'date' => $item->date,
                        'file_url' => $item->file_url,
                        'name' => $item->name,
                    ];
                })->values(),
            ];
        })->values();

        // Response JSON
        return response()->json([
            'status' => 200,
            'message' => 'Data progress berhasil diambil.',
            'data' => $groupedProgress,
        ], 200);
    }





    // public function getUserProgressForUser($userId)
    // {
    //     $progress = Progress::whereHas('internship', function ($query) use ($userId) {
    //         $query->where('leader_id', $userId)
    //             ->orWhereHas('members', function ($query) use ($userId) {
    //                 $query->where('users.id', $userId);
    //             });
    //     })->get();
    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Data progress berhasil diambil.',
    //         'data' => $progress,
    //     ], 200);
    // }

    public function getUserProgressForUser($userId)
    {
        $progress = Progress::with(['internship.project']) // Memuat relasi internship dan project
            ->whereHas('internship', function ($query) use ($userId) {
                $query->where('leader_id', $userId)
                    ->orWhereHas('members', function ($query) use ($userId) {
                        $query->where('users.id', $userId);
                    });
            })->get();

        return response()->json([
            'status' => 200,
            'message' => 'Data progress berhasil diambil.',
            'data' => $progress,
        ], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'meeting' => 'required',
            'name' => 'required',
            'date' => 'required|date',
            'file_url' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        try {
            // Ambil data magang berdasarkan internship_id
            $internship = Internship::find($request->internship_id);
            if (!$internship) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Data magang tidak ditemukan.',
                ], 404);
            }

            // Dapatkan data leader (ketua kelompok)
            $leader = $internship->leader;

            // Penyimpanan file berdasarkan email leader
            $fileUrl = null;
            if ($request->hasFile('file_url')) {
                $folderPath = "internship/" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $leader->email) . "/progress";
                $filePath = $request->file('file_url')->store($folderPath, 'public');
                $fileUrl = url("storage/$filePath");
            }

            // Simpan data progress
            $progress = Progress::create([
                'internship_id' => $request->internship_id,
                'meeting' => $request->meeting,
                'date' => $request->date,
                'file_url' => $fileUrl,
                'name' => $request->name,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Progress berhasil ditambahkan.',
                'data' => $progress,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan saat menyimpan progress.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
