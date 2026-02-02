<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenilaianElemenAk;
use App\Models\Asesmen;
use App\Models\ElemenStandar;
use App\Models\User;

class PenilaianAsesorDummySeeder extends Seeder
{
    /**
     * Seed penilaian elemen untuk 2 asesor dengan skor realistis
     * Sesuai dengan rubrik penilaian pada gambar
     */
    public function run(): void
    {
        // Ambil asesmen pertama atau buat baru
        $asesmen = Asesmen::first();
        if (!$asesmen) {
            $this->command->error('Tidak ada asesmen. Jalankan AsesmenSeeder terlebih dahulu.');
            return;
        }

        // Ambil asesor 1 dan asesor 2
        $asesor1 = User::where('email', 'asesor1@daisy.lamdepilar.or.id')->first();
        $asesor2 = User::where('email', 'asesor2@daisy.lamdepilar.or.id')->first();

        if (!$asesor1 || !$asesor2) {
            $this->command->error('Asesor tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        // Ambil semua elemen standar
        $elemens = ElemenStandar::all();
        if ($elemens->isEmpty()) {
            $this->command->error('Tidak ada elemen standar.');
            return;
        }

        // Contoh penilaian realistis untuk setiap kriteria
        // Berdasarkan gambar rubrik yang Anda berikan
        $penilaianAsesor1 = [
            // Kriteria D - Diferensiasi Misi
            'D.1' => ['skor' => 3, 'komentar' => 'Program studi memiliki legalitas program dan tata pamong yang jelas, visi misi tercatat dengan baik, struktur organisasi lengkap, dan dokumen pendukung valid serta mudah diverifikasi.'],
            'D.2' => ['skor' => 3, 'komentar' => 'Visi, misi, tujuan program studi sudah sesuai dengan visi misi institusi dan tertuang dalam dokumen resmi yang dapat diakses publik.'],
            'D.3' => ['skor' => 2, 'komentar' => 'Kesesuaian visi keilmuan sudah cukup baik namun masih ada beberapa aspek yang perlu diperkuat dalam implementasinya.'],
            'D.4' => ['skor' => 3, 'komentar' => 'Kurikulum program studi sudah selaras dengan visi keilmuan dan kebutuhan stakeholder, didukung dokumen yang valid.'],

            // Kriteria E - Edukasi, Sistem Evaluasi, dan Capaian Pembelajaran
            'E.1' => ['skor' => 3, 'komentar' => 'Admisi mahasiswa memiliki prosedur yang jelas dan transparan, data calon mahasiswa tercatat dengan baik dan memenuhi target.'],
            'E.2' => ['skor' => 3, 'komentar' => 'Proses dan siklus pembelajaran terstruktur dengan baik, RPP lengkap, dan evaluasi pembelajaran dilakukan secara berkala.'],
            'E.3' => ['skor' => 2, 'komentar' => 'Penilaian dan evaluasi sudah dilakukan namun masih ada aspek dokumentasi yang perlu dilengkapi.'],
            'E.4' => ['skor' => 3, 'komentar' => 'Kompetensi lulusan dan capaian pembelajaran terdokumentasi dengan baik dan sesuai dengan standar KKNI.'],
            'E.5' => ['skor' => 2, 'komentar' => 'Data lulusan tersedia namun sistem pelacakan alumni masih perlu ditingkatkan.'],

            // Kriteria P - Pengembangan Sumber Daya Manusia
            'P.1' => ['skor' => 3, 'komentar' => 'Program studi memiliki dosen dan tenaga kependidikan yang memadai dengan kualifikasi sesuai standar.'],
            'P.2' => ['skor' => 3, 'komentar' => 'Terdapat program pengembangan kapasitas SDM yang terencana dan terdokumentasi dengan baik.'],
            'P.3' => ['skor' => 2, 'komentar' => 'Sarana dan prasarana tersedia namun perlu peningkatan untuk beberapa fasilitas.'],
            'P.4' => ['skor' => 2, 'komentar' => 'Program kesejahteraan kerja ada namun belum optimal mencakup seluruh aspek.'],

            // Kriteria I - Internalisasi Penjaminan Mutu
            'I.1' => ['skor' => 3, 'komentar' => 'Sistem penjaminan mutu internal sudah berjalan dengan baik dan terdokumentasi.'],
            'I.2' => ['skor' => 3, 'komentar' => 'Implementasi perbaikan berkelanjutan dapat dilihat dari dokumen SPMI dan pelaksanaannya.'],
            'I.3' => ['skor' => 3, 'komentar' => 'Keterlibatan pemangku kepentingan dalam penjaminan mutu sudah baik dan melibatkan stakeholder eksternal.'],

            // Kriteria L - Lingkungan dan Sumber Belajar serta Pendukung Mahasiswa
            'L.1' => ['skor' => 3, 'komentar' => 'Sarana dan prasarana pembelajaran memadai, ruang kelas, lab, dan perpustakaan dalam kondisi baik.'],
            'L.2' => ['skor' => 2, 'komentar' => 'Sumber pengetahuan tersedia namun perlu penambahan koleksi digital dan jurnal internasional.'],
            'L.3' => ['skor' => 3, 'komentar' => 'Program kepuasan mahasiswa dan alumni sudah berjalan dengan survei yang rutin dilakukan.'],
            'L.4' => ['skor' => 3, 'komentar' => 'Lulusan, kajian telusur, dan kepuasan pengguna terdokumentasi dengan baik melalui tracer study.'],

            // Kriteria A - Akuntabilitas, Tata Kelola, dan Kerjasama
            'A.1' => ['skor' => 3, 'komentar' => 'Organisasi dan tata kelola program studi terstruktur dengan baik dan transparan.'],
            'A.2' => ['skor' => 3, 'komentar' => 'Kerjasama dengan pemangku kepentingan terjalin baik dan terdokumentasi dalam MoU.'],
            'A.3' => ['skor' => 3, 'komentar' => 'Sistem dan manajemen informasi sudah terkelola dengan baik menggunakan SIAKAD.'],
            'A.4' => ['skor' => 2, 'komentar' => 'Keselamatan dan kesehatan kerja serta kelestarian lingkungan sudah ada namun perlu peningkatan implementasi.'],
            'A.5' => ['skor' => 3, 'komentar' => 'Keuangan, keberlanjutan, dan mitigasi risiko terkelola dengan baik dan transparan.'],

            // Kriteria R - Riset, Pengabdian, dan Suasana Ilmiah
            'R.1' => ['skor' => 3, 'komentar' => 'Kebijakan penelitian tertuang dalam dokumen yang jelas dan mendukung kegiatan penelitian dosen.'],
            'R.2' => ['skor' => 2, 'komentar' => 'Proses penelitian berjalan namun output publikasi masih perlu ditingkatkan.'],
            'R.3' => ['skor' => 3, 'komentar' => 'Luaran dan dampak penelitian dapat dilihat dari publikasi di jurnal nasional terakreditasi.'],
            'R.4' => ['skor' => 3, 'komentar' => 'Kebijakan pengabdian kepada masyarakat jelas dan terprogram dengan baik.'],
            'R.5' => ['skor' => 2, 'komentar' => 'Proses pengabdian masyarakat terdokumentasi namun perlu peningkatan keterlibatan mahasiswa.'],
            'R.6' => ['skor' => 3, 'komentar' => 'Luaran dan dampak pengabdian kepada masyarakat terlihat dari program yang berkelanjutan.'],
        ];

        $penilaianAsesor2 = [
            // Asesor 2 dengan penilaian sedikit berbeda (untuk menguji konsistensi)
            'D.1' => ['skor' => 3, 'komentar' => 'Legalitas program dan tata pamong sudah sangat baik dengan dokumen lengkap dan terverifikasi.'],
            'D.2' => ['skor' => 3, 'komentar' => 'Visi, misi, tujuan sangat jelas dan tersosialisasi dengan baik kepada sivitas akademika.'],
            'D.3' => ['skor' => 3, 'komentar' => 'Kesesuaian visi keilmuan sudah baik dan terimplementasi dalam kurikulum dan kegiatan akademik.'],
            'D.4' => ['skor' => 3, 'komentar' => 'Kurikulum relevan dan update mengikuti perkembangan ilmu pengetahuan terkini.'],

            'E.1' => ['skor' => 3, 'komentar' => 'Sistem admisi mahasiswa transparan dan terukur dengan baik.'],
            'E.2' => ['skor' => 3, 'komentar' => 'Proses pembelajaran terstruktur dan menggunakan metode yang variatif.'],
            'E.3' => ['skor' => 3, 'komentar' => 'Sistem penilaian dan evaluasi jelas dan terdokumentasi dengan baik.'],
            'E.4' => ['skor' => 3, 'komentar' => 'Capaian pembelajaran lulusan sesuai dengan profil lulusan yang ditetapkan.'],
            'E.5' => ['skor' => 2, 'komentar' => 'Kompetensi lulusan terdata namun sistem monitoring karir alumni perlu diperkuat.'],

            'P.1' => ['skor' => 3, 'komentar' => 'Dosen dan tenaga kependidikan kompeten dengan kualifikasi memadai.'],
            'P.2' => ['skor' => 3, 'komentar' => 'Program pengembangan SDM terencana dan konsisten dilaksanakan.'],
            'P.3' => ['skor' => 3, 'komentar' => 'Sarana prasarana memadai untuk mendukung proses pembelajaran.'],
            'P.4' => ['skor' => 3, 'komentar' => 'Program kesejahteraan karyawan baik dengan jaminan kesehatan yang memadai.'],

            'I.1' => ['skor' => 3, 'komentar' => 'SPMI berjalan efektif dengan siklus PPEPP yang konsisten.'],
            'I.2' => ['skor' => 3, 'komentar' => 'Perbaikan berkelanjutan terlihat dari hasil audit internal yang ditindaklanjuti.'],
            'I.3' => ['skor' => 3, 'komentar' => 'Stakeholder terlibat aktif dalam evaluasi dan pengembangan program.'],

            'L.1' => ['skor' => 3, 'komentar' => 'Fasilitas pembelajaran lengkap dan terawat dengan baik.'],
            'L.2' => ['skor' => 3, 'komentar' => 'Akses ke sumber belajar digital dan perpustakaan memadai.'],
            'L.3' => ['skor' => 3, 'komentar' => 'Tingkat kepuasan mahasiswa tinggi berdasarkan survei berkala.'],
            'L.4' => ['skor' => 3, 'komentar' => 'Tracer study dilakukan rutin dengan respons rate yang baik.'],

            'A.1' => ['skor' => 3, 'komentar' => 'Struktur organisasi jelas dengan pembagian tugas yang tegas.'],
            'A.2' => ['skor' => 3, 'komentar' => 'Kerjasama dengan industri dan institusi lain sangat baik.'],
            'A.3' => ['skor' => 3, 'komentar' => 'Sistem informasi terintegrasi dan mudah diakses.'],
            'A.4' => ['skor' => 3, 'komentar' => 'Program K3L sudah diimplementasikan dengan baik dan ada petugas tersertifikasi.'],
            'A.5' => ['skor' => 3, 'komentar' => 'Pengelolaan keuangan transparan dan diaudit secara berkala.'],

            'R.1' => ['skor' => 3, 'komentar' => 'Roadmap penelitian jelas dan mendukung pengembangan keilmuan.'],
            'R.2' => ['skor' => 3, 'komentar' => 'Penelitian dosen produktif dengan dukungan dana penelitian yang memadai.'],
            'R.3' => ['skor' => 3, 'komentar' => 'Output penelitian terpublikasi di jurnal nasional dan internasional.'],
            'R.4' => ['skor' => 3, 'komentar' => 'Program pengabdian masyarakat terlaksana rutin setiap semester.'],
            'R.5' => ['skor' => 3, 'komentar' => 'Pengabdian masyarakat melibatkan mahasiswa dan berdampak nyata.'],
            'R.6' => ['skor' => 3, 'komentar' => 'Dampak pengabdian terukur dan diapresiasi oleh masyarakat sasaran.'],
        ];

        // Insert penilaian untuk Asesor 1
        $count1 = 0;
        foreach ($penilaianAsesor1 as $kodeElemen => $nilai) {
            $elemen = ElemenStandar::where('kode_elemen', $kodeElemen)->first();
            if ($elemen) {
                PenilaianElemenAk::updateOrCreate(
                    [
                        'id_asesmen' => $asesmen->id,
                        'id_asesor' => $asesor1->id,
                        'id_elemen' => $elemen->id,
                    ],
                    [
                        'skor' => $nilai['skor'],
                        'komentar' => $nilai['komentar'],
                        'status' => 'submitted',
                        'status_validasi' => 'not_validated',
                    ]
                );
                $count1++;
            }
        }

        // Insert penilaian untuk Asesor 2
        $count2 = 0;
        foreach ($penilaianAsesor2 as $kodeElemen => $nilai) {
            $elemen = ElemenStandar::where('kode_elemen', $kodeElemen)->first();
            if ($elemen) {
                PenilaianElemenAk::updateOrCreate(
                    [
                        'id_asesmen' => $asesmen->id,
                        'id_asesor' => $asesor2->id,
                        'id_elemen' => $elemen->id,
                    ],
                    [
                        'skor' => $nilai['skor'],
                        'komentar' => $nilai['komentar'],
                        'status' => 'submitted',
                        'status_validasi' => 'not_validated',
                    ]
                );
                $count2++;
            }
        }

        $this->command->info("✓ Penilaian Asesor 1: {$count1} elemen");
        $this->command->info("✓ Penilaian Asesor 2: {$count2} elemen");
        $this->command->info('Seeder berhasil dijalankan!');
    }
}
