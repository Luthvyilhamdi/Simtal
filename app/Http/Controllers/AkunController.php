<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\MenuAccess;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AkunController extends Controller
{
    use LogsActivity;

    /**
     * Batas dianggap "sedang online" (menit sejak permintaan terakhir).
     *
     * Status dibaca dari tabel `sessions` (SESSION_DRIVER=database), jadi tidak
     * perlu kolom tambahan di users. Konsekuensinya: `last_activity` hanya
     * diperbarui saat ada request — tab yang dibiarkan diam akan terhitung
     * offline. Baris sesi juga dibersihkan setelah SESSION_LIFETIME (120 menit),
     * sehingga "terakhir aktif" hanya terbaca dalam rentang itu.
     */
    private const AMBANG_ONLINE_MENIT = 5;

    public function index(Request $request)
    {
        $query = User::query();

        // Pencarian satu kotak: nama, NIK, atau email.
        if ($request->search) {
            $cari = $request->search;
            $query->where(function ($q) use ($cari) {
                $q->where('name', 'like', '%' . $cari . '%')
                  ->orWhere('nik', 'like', '%' . $cari . '%')
                  ->orWhere('email', 'like', '%' . $cari . '%');
            });
        }

        if (in_array($request->role, ['user', 'admin', 'super_admin'], true)) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        $batas = now()->subMinutes(self::AMBANG_ONLINE_MENIT)->getTimestamp();

        // Ringkasan dihitung dari SELURUH akun, bukan hasil saringan,
        // supaya angkanya tetap menggambarkan kondisi sistem.
        $stats = [
            'total'       => User::count(),
            'super_admin' => User::where('role', 'super_admin')->count(),
            'admin'       => User::where('role', 'admin')->count(),
            'user'        => User::where('role', 'user')->count(),
            'online'      => DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', $batas)
                ->distinct()
                ->count('user_id'),
        ];

        return view('akun.index', [
            'users'      => $users,
            'menuGroups' => MenuAccess::grouped(),
            'stats'      => $stats,
            'totalMenu'  => count(MenuAccess::keys()),
            'statusAkun' => $this->statusOnline($users->getCollection()->pluck('id'), $batas),
        ]);
    }

    /**
     * Status online per akun untuk baris yang sedang ditampilkan.
     *
     * Satu akun bisa punya beberapa sesi (banyak perangkat) — yang dipakai
     * adalah aktivitas terbaru. Dikembalikan sebagai array biasa agar model
     * User tidak perlu diberi atribut palsu yang tidak ada kolomnya.
     *
     * @param  \Illuminate\Support\Collection<int, int>  $idAkun
     * @return array<int, array{online: bool, keterangan: string}>
     */
    private function statusOnline($idAkun, int $batas): array
    {
        if ($idAkun->isEmpty()) {
            return [];
        }

        $terakhir = DB::table('sessions')
            ->whereIn('user_id', $idAkun)
            ->groupBy('user_id')
            ->selectRaw('user_id, MAX(last_activity) as terakhir')
            ->pluck('terakhir', 'user_id');

        $hasil = [];
        foreach ($idAkun as $id) {
            $waktu = $terakhir[$id] ?? null;

            $hasil[$id] = [
                'online'     => $waktu !== null && $waktu >= $batas,
                'keterangan' => $waktu !== null ? $this->jarakWaktu((int) $waktu) : '',
            ];
        }

        return $hasil;
    }

    /**
     * Jarak waktu dalam Bahasa Indonesia. Ditulis sendiri karena locale aplikasi
     * masih 'en' — mengubahnya akan ikut mengubah halaman lain.
     */
    private function jarakWaktu(int $timestamp): string
    {
        $detik = max(0, time() - $timestamp);

        return match (true) {
            $detik < 60   => 'baru saja',
            $detik < 3600 => intdiv($detik, 60) . ' menit lalu',
            $detik < 86400 => intdiv($detik, 3600) . ' jam lalu',
            default        => intdiv($detik, 86400) . ' hari lalu',
        };
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nik'           => 'required|string|max:30|unique:users,nik',
            'email'         => 'required|email|unique:users,email',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'role'          => 'required|in:user,admin,super_admin',
            'menu_access'   => ['array'],
            'menu_access.*' => [Rule::in(MenuAccess::keys())],
        ]);

        User::create([
            'name'        => $request->name,
            'nik'         => $request->nik,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'menu_access' => $request->role === 'admin' ? ($request->input('menu_access', [])) : null,
        ]);

        $this->log('tambah', 'Akun', $request->name, 'Role: ' . $request->role);

        return redirect()->route('akun.index')->with('success', 'Akun berhasil ditambahkan!');
    }

    public function update(Request $request, User $akun)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'nik'           => 'required|string|max:30|unique:users,nik,' . $akun->id,
            'email'         => 'required|email|unique:users,email,' . $akun->id,
            'role'          => 'required|in:user,admin,super_admin',
            'menu_access'   => ['array'],
            'menu_access.*' => [Rule::in(MenuAccess::keys())],
        ]);

        $data = [
            'name'        => $request->name,
            'nik'         => $request->nik,
            'email'       => $request->email,
            'role'        => $request->role,
            // Akses menu hanya relevan untuk admin; role lain dikosongkan.
            'menu_access' => $request->role === 'admin' ? ($request->input('menu_access', [])) : null,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $akun->update($data);

        $this->log('edit', 'Akun', $akun->name, 'Role: ' . $akun->role);

        return redirect()->route('akun.index')->with('success', 'Akun berhasil diupdate!');
    }

    public function destroy(User $akun)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($akun->id === $currentUser->id) {
            return redirect()->route('akun.index')->with('error', 'Tidak bisa menghapus akun sendiri!');
        }

        $nama = $akun->name;
        $akun->delete();

        $this->log('hapus', 'Akun', $nama, 'Hapus akun user');

        return redirect()->route('akun.index')->with('success', 'Akun berhasil dihapus!');
    }
}