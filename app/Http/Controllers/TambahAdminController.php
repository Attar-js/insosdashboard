<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TambahAdminController extends Controller
{
    /**
     * Halaman tambah admin
     */
    public function index()
    {
        // Ambil semua admin yang sudah ada
        $adminList = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('tambah-admin.index', compact('adminList'));
    }

    /**
     * Simpan admin baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8'
        ], [
            'name.required' => 'Nama admin harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'username.required' => 'Username harus diisi',
            'username.unique' => 'Username sudah digunakan',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Buat user admin baru
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'role' => 'admin',
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'status' => 'active'
            ]);

            return redirect()->route('tambah-admin.index')
                ->with('success', 'Admin berhasil ditambahkan: ' . $admin->name);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan admin: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hapus admin
     */
    public function destroy($id)
    {
        try {
            $admin = User::where('role', 'admin')->findOrFail($id);
            $adminName = $admin->name;
            $admin->delete();

            return redirect()->route('tambah-admin.index')
                ->with('success', 'Admin berhasil dihapus: ' . $adminName);

        } catch (\Exception $e) {
            return redirect()->route('tambah-admin.index')
                ->with('error', 'Gagal menghapus admin: ' . $e->getMessage());
        }
    }

    /**
     * Edit admin
     */
    public function edit($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        return view('tambah-admin.edit', compact('admin'));
    }

    /**
     * Update admin
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string|min:8'
        ], [
            'name.required' => 'Nama admin harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'username.required' => 'Username harus diisi',
            'username.unique' => 'Username sudah digunakan',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update data admin
            $admin->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_number
            ]);

            // Update password jika diisi
            if ($request->filled('password')) {
                $admin->update([
                    'password' => Hash::make($request->password)
                ]);
            }

            return redirect()->route('tambah-admin.index')
                ->with('success', 'Data admin berhasil diupdate: ' . $admin->name);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate admin: ' . $e->getMessage())
                ->withInput();
        }
    }
} 