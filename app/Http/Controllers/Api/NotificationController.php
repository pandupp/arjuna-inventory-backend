<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification; // Gunakan model yang baru kita buat

class NotificationController extends Controller
{
    /**
     * Mengambil daftar notifikasi untuk pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil notifikasi, urutkan dari yang terbaru
        $notifications = Notification::where('user_id', $user->id)
                                     ->latest() // Shortcut untuk orderBy('created_at', 'desc')
                                     ->limit(20) // Batasi agar tidak terlalu banyak
                                     ->get();

        // Hitung notifikasi yang belum dibaca
        $unreadCount = Notification::where('user_id', $user->id)
                                   ->whereNull('read_at')
                                   ->count();

        return response()->json([
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Menandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);

        return response()->json(['message' => 'Semua notifikasi ditandai telah dibaca.']);
    }
}
