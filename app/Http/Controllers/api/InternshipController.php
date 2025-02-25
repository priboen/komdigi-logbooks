<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternshipController extends Controller
{
    public function personalRegister(Request $request)
    {
        $request->validate([
            'leader_email' => 'required|email|exists:users,email',
            'project_id' => 'required|exists:projects,id',
            'supervisor_id' => 'required|exists:users,id',
            'campus' => 'required',
            'letter_file' => 'required|file|mimes:pdf|max:2048',
            'member_photo_file' => 'required|file|mimes:pdf|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $leader = User::where('email', $request->leader_email)->first();
        if (!$leader) {
            return response()->json([
                'status' => 422,
                'message' => 'Email leader belum terdaftar.',
            ], 422);
        }

        $folderPath = "internship/" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $leader->email) . "/pdf";
        $letterPath = $request->file('letter_file')->store($folderPath, 'public');
        $memberPhotoPath = $request->file('member_photo_file')->store($folderPath, 'public');

        try {
            $internship = Internship::create([
                'leader_id' => $leader->id,
                'project_id' => $request->project_id,
                'supervisor_id' => $request->supervisor_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'campus' => $request->campus,
                'status' => 'pending',
                'letter_url' => url("storage/$letterPath"),
                'member_photo_url' => url("storage/$memberPhotoPath"),
            ]);
            $internship->load(['leader', 'project', 'supervisor']);
            return response()->json([
                'message' => 'Pendaftaran magang berhasil.',
                'data' => $internship,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan saat menyimpan data magang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'leader_email' => 'required|email|exists:users,email',
            'member_emails' => 'nullable|array',
            'member_emails.*' => 'email|exists:users,email',
            'project_id' => 'required|exists:projects,id',
            'supervisor_id' => 'required|exists:users,id',
            'campus' => 'required',
            'letter_file' => 'required|file|mimes:pdf|max:2048',
            'member_photo_file' => 'required|file|mimes:pdf|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        $memberEmails = $request->member_emails ?? [];
        $memberIds = !empty($memberEmails) ? User::whereIn('email', $memberEmails)->pluck('id') : collect();
        $leader = User::where('email', $request->leader_email)->first();
        if (!$leader) {
            return response()->json([
                'status' => 422,
                'message' => 'Email leader belum terdaftar.',
            ], 422);
        }
        $invalidEmails = [];
        if (!empty($request->member_emails)) {
            $invalidEmails = array_diff($request->member_emails, User::whereIn('email', $request->member_emails)->pluck('email')->toArray());
        }
        if (!empty($invalidEmails)) {
            return response()->json([
                'status' => 422,
                'message' => 'Beberapa email anggota belum terdaftar.',
                'invalid_emails' => $invalidEmails,
            ], 422);
        }
        $folderPath = "internship/" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $leader->email) . "/pdf";
        $letterPath = $request->file('letter_file')->store($folderPath, 'public');
        $memberPhotoPath = $request->file('member_photo_file')->store($folderPath, 'public');
        try {
            $memberIds = User::whereIn('email', $request->member_emails)->pluck('id');

            $internship = Internship::create([
                'leader_id' => $leader->id,
                'project_id' => $request->project_id,
                'supervisor_id' => $request->supervisor_id,
                'campus' => $request->campus,
                'status' => 'pending',
                'letter_url' => url("storage/$letterPath"),
                'member_photo_url' => url("storage/$memberPhotoPath"),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            $internship->members()->attach($memberIds);
            $internship->load(['leader', 'project', 'supervisor', 'members']);
            return response()->json([
                'message' => 'Pendaftaran magang berhasil.',
                'data' => $internship,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan saat menyimpan data magang.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
        return response()->json(['status' => 201, 'message' => 'Status magang berhasil diubah.', 'data' => $internship], 200);
    }

    // public function destroy($id)
    // {
    //     $internship = Internship::where('leader_id', $id->leader_id)->first();

    //     if (!$internship) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Data magang tidak ditemukan atau sudah dihapus.',
    //         ], 404);
    //     }

    //     if ($id->user()->id !== $internship->leader_id) {
    //         return response()->json([
    //             'status' => 403,
    //             'message' => 'Hanya ketua kelompok yang dapat melakukan pembatalan.',
    //         ], 403);
    //     }

    //     try {
    //         $internship->delete();

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Pendaftaran magang berhasil dibatalkan.',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 500,
    //             'message' => 'Terjadi kesalahan saat membatalkan pendaftaran.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function destroy($id)
    {
        $internship = Internship::where('id', $id)->first();
        if (!$internship) {
            return response()->json([
                'status' => 404,
                'message' => 'Data magang tidak ditemukan atau sudah dihapus.',
            ], 404);
        }

        if (Auth::user()->id !== $internship->leader_id) {
            return response()->json([
                'status' => 403,
                'message' => 'Hanya ketua kelompok yang dapat melakukan pembatalan.',
            ], 403);
        }

        try {
            $internship->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Pendaftaran magang berhasil dibatalkan.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan saat membatalkan pendaftaran.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
}
