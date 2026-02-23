<?php

namespace App\Http\Controllers\Asesmen;

use App\Http\Controllers\Controller;
use App\Models\SyaratAkreditasi;
use App\Repositories\SyaratAkreditasiRepository;
use Illuminate\Http\Request;

class SyaratAkreditasiController extends Controller
{
    public function __construct(
        private readonly SyaratAkreditasiRepository $syaratRepo
    ) {}

    // =========================================================
    // INDEX
    // =========================================================

    public function index()
    {
        $syarats = SyaratAkreditasi::aktif()
            ->orderBy('kelompok')
            ->orderBy('kunci')
            ->get()
            ->groupBy('kelompok');

        $config = $this->syaratRepo->getAllConfig();

        return view('admin.syarat-akreditasi.index', compact('syarats', 'config'));
    }

    // =========================================================
    // UPDATE SATU NILAI
    // =========================================================

    public function update(Request $request, SyaratAkreditasi $syarat)
    {
        $request->validate([
            'nilai'  => 'required',
            'alasan' => 'nullable|string|max:500',
        ]);

        // Validasi tambahan berdasarkan tipe
        $nilai = $this->parseNilai($request->nilai, $syarat->tipe);

        if ($nilai === null) {
            return back()->withErrors([
                'nilai' => "Format nilai tidak sesuai tipe '{$syarat->tipe}'.",
            ]);
        }

        $this->syaratRepo->updateSyarat(
            kelompok: $syarat->kelompok,
            kunci: $syarat->kunci,
            nilaiBaru: $nilai,
            alasan: $request->alasan,
            userId: auth()->id()
        );

        return back()->with('success', "Syarat [{$syarat->label}] berhasil diperbarui.");
    }

    // =========================================================
    // HISTORY LOG
    // =========================================================

    public function history(SyaratAkreditasi $syarat)
    {
        $logs = $syarat->logs()
            ->with('changedBy')
            ->orderByDesc('changed_at')
            ->paginate(20);

        return view('admin.syarat-akreditasi.history', compact('syarat', 'logs'));
    }

    // =========================================================
    // API: current config snapshot (untuk debugging / frontend)
    // =========================================================

    public function configSnapshot()
    {
        return response()->json([
            'config'     => $this->syaratRepo->getAllConfig(),
            'checked_at' => now()->toISOString(),
        ]);
    }

    // =========================================================
    // PRIVATE
    // =========================================================

    private function parseNilai(mixed $input, string $tipe): mixed
    {
        return match ($tipe) {
            'integer' => is_numeric($input) ? (int)$input : null,
            'float'   => is_numeric($input) ? (float)$input : null,
            'boolean' => in_array(strtolower((string)$input), ['true', '1', 'yes']) ? 'true' : 'false',
            'array'   => $this->parseArray($input),
            default   => (string)$input,
        };
    }

    private function parseArray(mixed $input): ?array
    {
        // Terima JSON string atau comma-separated
        if (is_array($input)) {
            return array_values(array_filter(array_map('trim', $input)));
        }

        if (is_string($input)) {
            // Coba JSON dulu
            $decoded = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : null;
            }

            // Fallback: comma-separated
            return array_values(array_filter(array_map('trim', explode(',', $input))));
        }

        return null;
    }
}
