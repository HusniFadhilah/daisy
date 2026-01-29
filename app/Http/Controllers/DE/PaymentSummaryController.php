<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentSummaryController extends Controller
{
    public function verifyPassword(Request $request)
    {
        try {
            // Validate
            $validated = $request->validate([
                'password' => 'required|string',
            ]);

            $correctPassword = config('payment.summary_password');
            $inputPassword = $validated['password'];

            // Verify password
            if ($inputPassword !== $correctPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password yang Anda masukkan salah.',
                ], 400);
            }

            // Store verification in session
            session(['payment_summary_verified_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Password benar. Anda dapat melihat detail pembayaran.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', [
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error in verifyPassword', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSummary(Request $request)
    {
        try {
            // Check session manually
            $sessionKey = 'payment_summary_verified_at';
            $timeout = (int) config('payment.session_timeout', 30); // 👈 CAST KE INTEGER

            if (!session()->has($sessionKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password diperlukan untuk mengakses data ini.',
                    'requires_password' => true,
                ], 403);
            }

            // Check if session is expired
            $verifiedAt = session($sessionKey);
            $expiresAt = $verifiedAt->copy()->addMinutes($timeout);

            if (now()->greaterThan($expiresAt)) {
                session()->forget($sessionKey);
                return response()->json([
                    'success' => false,
                    'message' => 'Session telah kadaluarsa. Silakan masukkan password lagi.',
                    'requires_password' => true,
                ], 403);
            }

            // Get verified payments
            $verifiedPayments = PengajuanPembayaran::where('status_pembayaran', 'terverifikasi')
                ->with(['pengajuan.studyProgram.university', 'pengajuan.studyProgram.degreeLevel'])
                ->get();

            // Calculate statistics
            $totalProdi = $verifiedPayments->unique('pengajuan.id_study_program')->count();
            $totalNominal = $verifiedPayments->sum('jumlah_pembayaran');
            $totalTransaksi = $verifiedPayments->count();

            // Group by university
            $byUniversity = $verifiedPayments->groupBy(function ($payment) {
                return $payment->pengajuan->studyProgram->university->name;
            })->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('jumlah_pembayaran'),
                    'prodi_count' => $group->unique('pengajuan.id_study_program')->count(),
                ];
            })->sortByDesc('total');

            // Group by degree level
            $byDegree = $verifiedPayments->groupBy(function ($payment) {
                return $payment->pengajuan->studyProgram->degreeLevel->name;
            })->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('jumlah_pembayaran'),
                ];
            })->sortByDesc('total');

            // Monthly breakdown (last 6 months)
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $payments = $verifiedPayments->filter(function ($payment) use ($date) {
                    return $payment->tanggal_verifikasi &&
                        $payment->tanggal_verifikasi->format('Y-m') === $date->format('Y-m');
                });

                $monthlyData[] = [
                    'month' => $date->format('M Y'),
                    'count' => $payments->count(),
                    'total' => $payments->sum('jumlah_pembayaran'),
                ];
            }

            // Recent verified payments (last 10)
            $recentPayments = PengajuanPembayaran::where('status_pembayaran', 'terverifikasi')
                ->with(['pengajuan.studyProgram'])
                ->orderByDesc('tanggal_verifikasi')
                ->limit(10)
                ->get()
                ->map(function ($payment) {
                    return [
                        'nomor_invoice' => $payment->nomor_invoice,
                        'prodi' => $payment->pengajuan->studyProgram->name,
                        'jumlah' => $payment->jumlah_pembayaran,
                        'tanggal' => $payment->tanggal_verifikasi->format('d M Y H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_prodi' => $totalProdi,
                        'total_nominal' => $totalNominal,
                        'total_transaksi' => $totalTransaksi,
                    ],
                    'by_university' => $byUniversity,
                    'by_degree' => $byDegree,
                    'monthly' => $monthlyData,
                    'recent_payments' => $recentPayments,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting payment summary', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        session()->forget('payment_summary_verified_at');

        return response()->json([
            'success' => true,
            'message' => 'Anda telah keluar dari session.',
        ]);
    }
}
