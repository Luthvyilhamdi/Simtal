<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        /** @var User $user */
        $user = auth()->user();

        // Cari data karyawan berdasarkan NIK
        $karyawan = $user->nik
            ? Karyawan::with([
                'jabatan', 'direktorat', 'kompartemen',
                'departemen', 'jobGrade', 'personGrade',
                'historyJabatan', 'historyAssessment',
              ])->where('nik', $user->nik)->first()
            : null;

        return view('profile.edit', compact('user', 'karyawan'));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nik'   => 'nullable|string',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'nik'   => $request->nik,
        ]);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'password'              => ['required', 'confirmed', Password::defaults()],
        ]);

        /** @var User $user */
        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('profile.edit')->with('success_password', 'Password berhasil diperbarui!');
    }

    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}