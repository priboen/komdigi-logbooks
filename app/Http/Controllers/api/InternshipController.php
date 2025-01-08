<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'leader_email' => 'required|email|exists:users,email',
    //         'member_emails' => 'nullable|array',
    //         'member_emails.*' => 'email|exists:users,email',
    //         'project_id' => 'required|exists:projects,id',
    //         'supervisor_id' => 'required|exists:users,id',
    //         'campus' => 'required',
    //         'letter_file' => 'required|file|mimes:pdf|max:2048',
    //         'member_photo_file' => 'required|file|mimes:pdf|max:2048',
    //     ]);

    //     // Cek apakah leader email valid
    //     $leader = User::where('email', $request->leader_email)->first();
    //     if (!$leader) {
    //         return response()->json([
    //             'status' => 422,
    //             'message' => 'Email leader belum terdaftar.',
    //         ], 422);
    //     }
    //     $invalidEmails = [];
    //     if (!empty($request->member_emails)) {
    //         foreach ($request->member_emails as $email) {
    //             $user = User::where('email', $email)->first();
    //             if (!$user) {
    //                 $invalidEmails[] = $email;
    //             }
    //         }
    //     }
    //     if (!empty($invalidEmails)) {
    //         return response()->json([
    //             'status' => 422,
    //             'message' => 'Beberapa email anggota belum terdaftar.',
    //             'invalid_emails' => $invalidEmails,
    //         ], 422);
    //     }

    //     // Semua email valid, lanjut ke penyimpanan file
    //     $folderPath = "internship/{$leader->email}/pdf";
    //     $letterPath = $request->file('letter_file')->store($folderPath, 'public');
    //     $memberPhotoPath = $request->file('member_photo_file')->store($folderPath, 'public');

    //     // Buat data magang
    //     $memberIds = User::whereIn('email', $request->member_emails)->pluck('id');
    //     $internship = Internship::create([
    //         'leader_id' => $leader->id,
    //         'project_id' => $request->project_id,
    //         'supervisor_id' => $request->supervisor_id,
    //         'status' => 'pending',
    //         'letter_url' => url("storage/$letterPath"),
    //         'member_photo_url' => url("storage/$memberPhotoPath"),
    //     ]);

    //     $internship->members()->attach($memberIds);

    //     return response()->json([
    //         'status' => 201,
    //         'message' => 'Pendaftaran magang berhasil.',
    //         'data' => $internship,
    //     ], 201);
    // }

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
        ]);

        // Cek apakah leader email valid
        $leader = User::where('email', $request->leader_email)->first();
        if (!$leader) {
            return response()->json([
                'status' => 422,
                'message' => 'Email leader belum terdaftar.',
            ], 422);
        }

        // Validasi email anggota
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

        // Penyimpanan file
        $folderPath = "internship/" . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $leader->email) . "/pdf";
        $letterPath = $request->file('letter_file')->store($folderPath, 'public');
        $memberPhotoPath = $request->file('member_photo_file')->store($folderPath, 'public');

        // Transaksi untuk menyimpan data magang
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
            ]);

            $internship->members()->attach($memberIds);

            // Muat hubungan yang diperlukan
            $internship->load(['leader', 'project', 'supervisor', 'members']);

            // Response dengan format yang sama seperti GET by user id
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

    public function getNearestSchedule() {}
}
