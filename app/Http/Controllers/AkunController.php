<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AkunController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $users = User::orderBy('name')->paginate(10);
        return view('akun.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nik'      => 'required|string|max:30|unique:users,nik',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => 'required|in:user,admin,super_admin',
        ]);

        User::create([
            'name'     => $request->name,
            'nik'      => $request->nik,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        $this->log('tambah', 'Akun', $request->name, 'Role: ' . $request->role);

        return redirect()->route('akun.index')->with('success', 'Akun berhasil ditambahkan!');
    }

    public function update(Request $request, User $akun)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'nik'   => 'required|string|max:30|unique:users,nik,' . $akun->id,
            'email' => 'required|email|unique:users,email,' . $akun->id,
            'role'  => 'required|in:user,admin,super_admin',
        ]);

        $data = [
            'name'  => $request->name,
            'nik'   => $request->nik,
            'email' => $request->email,
            'role'  => $request->role,
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