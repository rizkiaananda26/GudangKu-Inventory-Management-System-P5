<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Tampilkan daftar akun pengguna sistem (Khusus Admin)
     */
    public function index()
    {
        $users = User::withCount('stockTransactions')->orderBy('full_name')->get();

        return view('users.index', compact('users'));
    }

    /**
     * Simpan pengguna baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'  => ['required', 'string', 'max:50', 'unique:users,username'],
            'full_name' => ['required', 'string', 'max:100'],
            'password'  => ['required', 'string', 'min:6'],
            'role'      => ['required', 'in:admin,staff'],
            'email'     => ['nullable', 'email', 'max:120'],
        ], [
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan oleh akun lain.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal terdiri dari 6 karakter.',
            'role.required'      => 'Pilih peran pengguna (admin atau staff).',
        ]);

        User::create([
            'username'  => $validated['username'],
            'full_name' => $validated['full_name'],
            'email'     => $validated['email'] ?? null,
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna "' . $validated['username'] . '" (' . $validated['role'] . ') berhasil ditambahkan.');
    }

    /**
     * Perbarui data pengguna
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:100'],
            'role'         => ['required', 'in:admin,staff'],
            'new_password' => ['nullable', 'string', 'min:6'],
            'email'        => ['nullable', 'email', 'max:120'],
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'new_password.min'   => 'Password baru minimal 6 karakter.',
        ]);

        $updateData = [
            'full_name' => $validated['full_name'],
            'role'      => $validated['role'],
            'email'     => $validated['email'] ?? null,
        ];

        if (!empty($validated['new_password'])) {
            $updateData['password'] = Hash::make($validated['new_password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna "' . $user->username . '" berhasil diperbarui.');
    }

    /**
     * Hapus akun pengguna dengan proteksi audit trail
     */
    public function destroy(User $user)
    {
        // Proteksi: Tidak boleh menghapus akun diri sendiri saat login
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Aksi ditolak: Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        // Proteksi Integritas: Tidak boleh menghapus pengguna yang sudah punya riwayat transaksi
        $trxCount = $user->stockTransactions()->count();
        if ($trxCount > 0) {
            return redirect()->route('users.index')
                ->with('error', 'Pengguna tidak dapat dihapus karena telah mencatat ' . $trxCount . ' transaksi stok. Ini untuk menjaga jejak audit transaksi inventaris.');
        }

        $username = $user->username;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna "' . $username . '" berhasil dihapus.');
    }
}
