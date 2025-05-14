<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function uploadGrade(Request $request)
    {
        $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'grade_file' => 'required|file|mimes:pdf',
        ]);
        $supervisorId = Auth::user()->id;
        $internship = Internship::findOrFail($request->internship_id);
        if ($internship->supervisor_id != $supervisorId) {
            return response()->json([
                'status' => 403,
                'message' => 'Anda tidak berhak memberikan nilai untuk magang ini.',
            ], 403);
        }
        $folderPath = "grades/{$request->internship_id}";
        $filePath = $request->file('grade_file')->store($folderPath, 'public');
        $grade = Grade::updateOrCreate(
            [
                'internship_id' => $request->internship_id,
            ],
            [
                'grade_url' => url("storage/$filePath"),
            ]
        );
        return response()->json([
            'status' => 200,
            'message' => 'Nilai berhasil diupload.',
            'data' => $grade,
        ]);
    }

    public function getGrade($internshipId)
    {
        if (!$internshipId) {
            return response()->json([
                'status' => 400,
                'message' => 'ID magang tidak valid.',
            ], 400);
        }
        try {
            $grade = Grade::where('internship_id', $internshipId)
                ->with('internship.project')
                ->first();

            if (!$grade) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Nilai belum diupload untuk magang ini.',
                ], 404);
            }
            return response()->json([
                'status' => 200,
                'message' => 'Data nilai berhasil diambil.',
                'data' => $grade,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan internal. Silakan coba lagi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
