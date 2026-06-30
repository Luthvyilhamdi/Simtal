<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SimtalApiController extends Controller
{
    // ─────────────────────────────────────────────
    // POST /api/auth/login
    // Body: employee_id, password, client_id, client_secret
    // ─────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|string',
            'password'      => 'required|string',
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
        ]);

        // Verifikasi client
        if (
            $request->client_id     !== config('simtal_api.client_id') ||
            $request->client_secret !== config('simtal_api.client_secret')
        ) {
            return response()->json(['message' => 'Client tidak dikenal.'], 401);
        }

        // Cari user by NIK atau email
        $user = User::where('nik', $request->employee_id)
                    ->orWhere('email', $request->employee_id)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'ID Karyawan atau password salah.'], 401);
        }

        // Hapus token lama, buat token baru
        $user->tokens()->where('name', 'simtal_api')->delete();
        $token = $user->createToken('simtal_api', ['*'], now()->addHours(8))->plainTextToken;

        // Ambil data karyawan terkait
        $karyawan = Karyawan::with(['jobGrade', 'personGrade', 'jabatan', 'departemen', 'kompartemen'])
            ->where('nik', $user->nik)
            ->first();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => 28800, // 8 jam dalam detik
            'user'         => [
                'id'   => $user->id,
                'name' => $user->name,
                'email'=> $user->email,
                'role' => $user->role,
                'nik'  => $user->nik,
            ],
            'karyawan' => $karyawan ? $this->formatKaryawan($karyawan) : null,
        ]);
    }

    // ─────────────────────────────────────────────
    // GET /api/employee/profile
    // Header: Authorization: Bearer {token}
    // ─────────────────────────────────────────────
    public function profile(Request $request)
    {
        $user     = $request->user();
        $karyawan = Karyawan::with(['jobGrade', 'personGrade', 'jabatan', 'departemen', 'kompartemen'])
            ->where('nik', $user->nik)
            ->first();

        return response()->json([
            'data' => [
                'user'     => [
                    'id'   => $user->id,
                    'name' => $user->name,
                    'email'=> $user->email,
                    'role' => $user->role,
                    'nik'  => $user->nik,
                ],
                'karyawan' => $karyawan ? $this->formatKaryawan($karyawan) : null,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // GET /api/employees?search=
    // Header: Authorization: Bearer {token}
    // ─────────────────────────────────────────────
    public function employees(Request $request)
    {
        // Direktori karyawan hanya untuk admin/super_admin — samakan dengan
        // batasan di web app. Token milik role 'user' tidak boleh enumerasi
        // seluruh data karyawan.
        if ($request->user()->isUser()) {
            return response()->json(['message' => 'Tidak diizinkan mengakses direktori karyawan.'], 403);
        }

        $query = Karyawan::with(['jobGrade', 'personGrade', 'jabatan', 'departemen'])
            ->where('status', 'aktif');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nik',  'like', '%'.$request->search.'%');
            });
        }

        $karyawans = $query->orderBy('nama')->paginate(20);

        return response()->json([
            'data' => $karyawans->map(fn($k) => $this->formatKaryawan($k)),
            'meta' => [
                'current_page' => $karyawans->currentPage(),
                'last_page'    => $karyawans->lastPage(),
                'total'        => $karyawans->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // Helper format karyawan
    // ─────────────────────────────────────────────
    private function formatKaryawan(Karyawan $k): array
    {
        return [
            'id'                    => $k->id,
            'nik'                   => $k->nik,
            'nama'                  => $k->nama,
            'jabatan'               => $k->jabatan_saat_ini ?? $k->jabatan->nama_jabatan ?? null,
            'job_grade'             => $k->jobGrade->job_grade ?? null,
            'person_grade'          => $k->personGrade->person_grade ?? null,
            'band'                  => $k->band,
            'departemen'            => $k->departemen->nama_departemen ?? null,
            'kompartemen'           => $k->kompartemen->nama_kompartemen ?? null,
            'struktural_fungsional' => $k->struktural_fungsional,
            'status'                => $k->status,
            'foto_url'              => $k->foto ? asset('storage/'.$k->foto) : null,
        ];
    }
}