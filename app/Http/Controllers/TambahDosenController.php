<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TambahDosenController extends Controller
{
    /**
     * Halaman tambah dosen
     */
    public function index()
    {
        // Ambil semua dosen yang sudah ada
        $dosenList = User::where('role', 'dosen')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('tambah-dosen.index', compact('dosenList'));
    }

    /**
     * Simpan dosen baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'nip' => 'required|string|max:20|unique:users,nip',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8'
        ], [
            'name.required' => 'Nama dosen harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'username.required' => 'Username harus diisi',
            'username.unique' => 'Username sudah digunakan',
            'nip.required' => 'NIP harus diisi',
            'nip.unique' => 'NIP sudah terdaftar',
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
            // Buat user dosen baru
            $dosen = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'nip' => $request->nip,
                'phone_number' => $request->phone_number,
                'role' => 'dosen',
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'status' => 'active'
            ]);

            return redirect()->route('tambah-dosen.index')
                ->with('success', 'Dosen berhasil ditambahkan: ' . $dosen->name);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan dosen: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hapus dosen
     */
    public function destroy($id)
    {
        try {
            $dosen = User::where('role', 'dosen')->findOrFail($id);
            $dosenName = $dosen->name;
            $dosen->delete();

            return redirect()->route('tambah-dosen.index')
                ->with('success', 'Dosen berhasil dihapus: ' . $dosenName);

        } catch (\Exception $e) {
            return redirect()->route('tambah-dosen.index')
                ->with('error', 'Gagal menghapus dosen: ' . $e->getMessage());
        }
    }

    /**
     * Edit dosen
     */
    public function edit($id)
    {
        $dosen = User::where('role', 'dosen')->findOrFail($id);
        return view('tambah-dosen.edit', compact('dosen'));
    }

    /**
     * Update dosen
     */
    public function update(Request $request, $id)
    {
        $dosen = User::where('role', 'dosen')->findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'nip' => 'required|string|max:20|unique:users,nip,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string|min:8'
        ], [
            'name.required' => 'Nama dosen harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'username.required' => 'Username harus diisi',
            'username.unique' => 'Username sudah digunakan',
            'nip.required' => 'NIP harus diisi',
            'nip.unique' => 'NIP sudah terdaftar',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update data dosen
            $dosen->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'nip' => $request->nip,
                'phone_number' => $request->phone_number
            ]);

            // Update password jika diisi
            if ($request->filled('password')) {
                $dosen->update([
                    'password' => Hash::make($request->password)
                ]);
            }

            return redirect()->route('tambah-dosen.index')
                ->with('success', 'Data dosen berhasil diupdate: ' . $dosen->name);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengupdate dosen: ' . $e->getMessage())
                ->withInput();
        }
    }
} 