<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Exception;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        // ✨ dd() sudah dihapus dari sini ✨
        $users = User::latest()->get();
        return response()->json($users);
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@arjuna\.com$/i'
            ],
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['Manager Operasional', 'Staff'])],
        ]);

        try {
            $user = User::create($validatedData);
            return response()->json($user, 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan pengguna baru.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ... (method lainnya tetap sama)

    /**
     * Menampilkan satu pengguna spesifik.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
                'regex:/^[a-zA-Z0-9._%+-]+@arjuna\.com$/i'
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['Manager Operasional', 'Staff'])],
        ]);

        try {
            if (!$request->filled('password')) {
                unset($validatedData['password']);
            }
    
            $user->update($validatedData);
    
            return response()->json($user);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui pengguna.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
