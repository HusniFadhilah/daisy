<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Indikator;
use App\Models\ElemenStandar;

class IndikatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get elemen_standar IDs mapping
        $elemenMap = ElemenStandar::whereIn('kode_elemen', [
            'E1.1', 'E1.2', 'E1.3',
            'E2.1', 'E2.2', 'E2.3', 'E2.4', 'E2.5',
            'E3.1', 'E3.2', 'E3.3', 'E3.4',
            'E4.1', 'E4.2', 'E4.3',
            'E5.1', 'E5.2', 'E5.3', 'E5.4',
            'E6.1', 'E6.2', 'E6.3', 'E6.4', 'E6.5',
            'E7.1', 'E7.2', 'E7.3', 'E7.4', 'E7.5', 'E7.6'
        ])
        ->whereNotIn('id_elemen', [1, 3, 7]) // Skip duplicate entries
        ->pluck('id_elemen', 'kode_elemen')
        ->toArray();

        $indikator = [
            // E1.1 - Legalitas Program dan Tata Pamong
            [
                'id_elemen' => $elemenMap['E1.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-1.1.1',
                'deskripsi_indikator' => "Institusi, UPPS dan Program Studi memiliki dokumen legalitas yang valid, tidak meragukan, serta menunjukkan perwujudan Good University Governance (GUG). Penilaian berfokus pada:\n(1) Legalitas Institusi, UPPS dan Program Studi yang lengkap (termasuk dalam hal ini kerjasama dengan PS di dalam negeri dan atau Luar negeri)\n(2) Kebijakan dan dokumentasi Struktur Organisasi dan Tata Kelola yang lengkap dan implementatif\n(3) Dokumen tata pamong yang dapat menjalankan fungsi secara efektif (Misalnya untuk dokumen tatacara pengisian jabatan/personil pada struktur organisasi yang di tetapkan, dan atau dilakukan perubahan pada point 2)\n(4) Pelaporan pada PDDIKTI lengkap",
            ],

            // E1.2 - Visi, Misi, Tujuan, dan Strategi
            [
                'id_elemen' => $elemenMap['E1.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-1.2.1',
                'deskripsi_indikator' => "Diferensiasi misi dalam visi, misi, dan rencana strategis menunjukkan bahwa Institusi, Unit Pengelola Program Studi (UPPS), dan Program Studi menunjukkan:\n(1) Memiliki visi-misi yang jelas, koheren, dan inspiratif\n(2) Terukur dan diukur dengan metode yang sahih\n(3) Diimplementasikan melalui perencanaan strategis dan perencanaan pelaksanaan yang baik\n(4) PT-UPPS-Program Studi mampu menunjukkan proses evaluasi dan atau tindak lanjut hasil evaluasi VMTS untuk menjamin relevansi serta keberlanjutan penyelenggaraan pendidikan tinggi",
            ],

            // E1.3 - Kesesuaian Visi Keilmuan
            [
                'id_elemen' => $elemenMap['E1.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-1.3.1',
                'deskripsi_indikator' => "Visi dan misi yang dimiliki oleh Institusi, UPPS, dan Program Studi selaras dan koheren dan efektif menjadi acuan pengembangan. Visi keilmuan program studi memenuhi aspek:\n(1) Menunjukkan diferensiasi dan fokus pengembangan keilmuan\n(2) Mempunyai metode pengukuran yang jelas dan relevan\n(3) Adanya analisis yang menunjukkan dukungan sumber daya yang memadai\n(4) Menunjukkan adanya strategi dan program untuk mencapai daya saing/keunggulan dalam skala regional/nasional/internasional sesuai fokus misi",
            ],

            // E2.1 - Kurikulum
            [
                'id_elemen' => $elemenMap['E2.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.1.1',
                'deskripsi_indikator' => "Program Studi dapat menunjukkan:\n(1) Dokumen kurikulum yang memuat desain yang telah mengikuti pedoman terbaru dan berbasis hasil (outcomes based education)\n(2) Bukti keterlibatan pihak eksternal terutama organisasi profesi, representasi dari pengguna lulusan atau industri, ahli, dan alumni\n(3) Bukti implementasinya secara konsisten dan berkesinambungan\n(4) Adanya bukti evaluasi terhadap kurikulum yang dilaksanakan secara berkesinambungan dan konsisten sesuai siklus pendidikan",
            ],

            // E2.2 - Admisi Mahasiswa
            [
                'id_elemen' => $elemenMap['E2.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.2.1',
                'deskripsi_indikator' => "Institusi, Unit Pengelola Program studi, atau Program Studi telah menjalankan proses admisi yang memastikan efektifitas dan keberlanjutan masukan, proses, dan luaran yang ditandai:\n(1) Memiliki kebijakan rekrutasi akademik disusun lengkap dan sistematis\n(2) Mempunyai kebijakan afirmasi dan beasiswa yang proporsional\n(3) Dilaksanakan secara tertib, transparan, dan akuntabel, dan terpublikasi\n(4) Terdokumentasi dengan baik dan dievaluasi untuk perbaikan berkelanjutan",
            ],
            [
                'id_elemen' => $elemenMap['E2.2'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-2.2.1',
                'deskripsi_indikator' => "Program studi menyajikan data:\n(1) Reputasi pendidikan yang ditunjukkan dengan rasio keketatan admisi\n(2) Inklusifitas pendidikan yang ditunjukkan dengan rasio mahasiswa reguler dan mahasiswa afirmasi\n(3) Cacah mahasiswa asing dan/atau keterlibatan mahasiswa ke dalam kegiatan internasional/ke luar negeri dalam beragam bentuk kegiatan pembelajaran (berkredit penuh waktu, berkredit tidak penuh waktu tetapi lebih dari 1 semester, berkredit periode singkat, tanpa kredit)",
            ],

            // E2.3 - Proses dan Siklus Pembelajaran
            [
                'id_elemen' => $elemenMap['E2.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.3.1',
                'deskripsi_indikator' => "Program Studi menunjukkan adanya kebijakan, pelaksanaan, dan evaluasi siklus pendidikan yang efektif yang ditandai:\n(1) Dokumen kurikulum disusun sesuai panduan/pedoman penyusunan kurikulum perguruan tinggi dan rujukan keilmuan, diperbarui berkelanjutan, dan lengkap\n(2) Pelaksanakan pendidikan dan pembelajaran yang tertib dan konsisten\n(3) Dokumentasi siklus pembelajaran yang sistemik dan data yang terjaga integritasnya\n(4) Evaluasi konsisten dan adanya upaya perbaikan berkelanjutan",
            ],
            [
                'id_elemen' => $elemenMap['E2.3'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-2.3.1',
                'deskripsi_indikator' => "Efektifitas pendidikan yang ditunjukkan dengan data:\n(1) Masa studi sesuai desain kurikulum\n(2) Prestasi akademik mahasiswa",
            ],

            // E2.4 - Penilaian dan Evaluasi
            [
                'id_elemen' => $elemenMap['E2.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.4.1',
                'deskripsi_indikator' => "Program studi menunjukkan adanya:\n(1) Kebijakan dan pedoman yang jelas tentang penilaian hasil pembelajaran dan evaluasi proses pembelajaran\n(2) Pelaksanaan penilaian dan evaluasi yang konsisten dan berkesinambungan\n(3) Adanya prosedur yang jelas untuk penyelesaian keluhan terkait nilai dan evaluasi\n(4) Adanya dokumentasi hasil penilaian yang valid dan mudah diakses",
            ],
            [
                'id_elemen' => $elemenMap['E2.4'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-2.4.1',
                'deskripsi_indikator' => "Data hasil penilaian dan evaluasi yang ditunjukkan dengan:\n(1) Tingkat kelulusan tepat waktu\n(2) Data capaian nilai mata kuliah kunci\n(3) Proporsi nilai A/B pada mata kuliah kunci",
            ],

            // E2.5 - Kompetensi Lulusan dan Capaian Pembelajaran
            [
                'id_elemen' => $elemenMap['E2.5'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.5.1',
                'deskripsi_indikator' => "Program studi menunjukkan:\n(1) Capaian pembelajaran yang ditetapkan mencakup seluruh profil lulusan, koheren, dan relevan dengan kebutuhan pengguna\n(2) Adanya asesmen capaian pembelajaran yang konsisten, berkesinambungan, dan terukur\n(3) Adanya bukti karya/tugas akhir/publikasi/inovasi mahasiswa yang merefleksikan capaian pembelajaran\n(4) Adanya umpan balik dari pihak eksternal yang dipertimbangkan dalam evaluasi capaian pembelajaran",
            ],
            [
                'id_elemen' => $elemenMap['E2.5'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-2.5.1',
                'deskripsi_indikator' => "Data capaian pembelajaran:\n(1) Cacah karya/publikasi/inovasi mahasiswa yang mendapat pengakuan/penghargaan (regional, nasional, internasional) selama masa studi\n(2) Cacah mahasiswa yang memperoleh sertifikasi kompetensi profesi\n(3) Rata-rata IPK lulusan",
            ],

            // E3.1 - Dosen dan Tenaga Kependidikan
            [
                'id_elemen' => $elemenMap['E3.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.1.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan perencanaan dan pengelolaan SDM yang terpadu dan berkesinambungan\n(2) Ketercukupan jumlah dan kualifikasi dosen dan tendik sesuai standar\n(3) Adanya prosedur rekrutmen, penempatan, dan evaluasi kinerja yang adil\n(4) Adanya program retensi dan regenerasi SDM yang efektif",
            ],
            [
                'id_elemen' => $elemenMap['E3.1'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-3.1.1',
                'deskripsi_indikator' => "Data SDM:\n(1) Rasio Dosen Tetap Program Studi (DTPS) terhadap mahasiswa\n(2) Persentase DTPS berkualifikasi S3\n(3) Persentase DTPS yang memiliki sertifikat profesi/kompetensi\n(4) Rasio tenaga kependidikan terhadap DTPS",
            ],

            // E3.2 - Sarana dan Prasarana Kerja
            [
                'id_elemen' => $elemenMap['E3.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.2.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan pengadaan dan pemeliharaan sarana prasarana kerja yang jelas\n(2) Ketersediaan sarana dan prasarana kerja yang layak dan memadai bagi dosen dan tendik\n(3) Pemanfaatan sarana prasarana kerja yang optimal untuk mendukung tridarma\n(4) Adanya evaluasi berkala terhadap sarana prasarana kerja",
            ],

            // E3.3 - Pengembangan Kapasitas
            [
                'id_elemen' => $elemenMap['E3.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.3.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan dan program pengembangan kapasitas yang merata dan berkelanjutan\n(2) Adanya bukti keterlibatan dosen dan tendik dalam kegiatan peningkatan kompetensi (pelatihan, studi lanjut)\n(3) Adanya sistem penghargaan/insentif bagi SDM yang berprestasi\n(4) Pengembangan kapasitas selaras dengan kebutuhan strategis program studi",
            ],
            [
                'id_elemen' => $elemenMap['E3.3'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-3.3.1',
                'deskripsi_indikator' => "Data pengembangan kapasitas:\n(1) Rasio DTPS yang mengikuti pelatihan/studi lanjut per tahun\n(2) Jumlah dana yang dialokasikan untuk pengembangan kapasitas SDM\n(3) Persentase tendik yang mengikuti pelatihan/sertifikasi per tahun",
            ],

            // E3.4 - Kesejahteraan Kerja
            [
                'id_elemen' => $elemenMap['E3.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.4.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan kesejahteraan dan keamanan kerja yang terpadu dan koheren\n(2) Pemberian remunerasi yang adil dan sesuai ketentuan\n(3) Adanya program kesehatan dan keselamatan kerja yang efektif (termasuk asuransi/jaminan sosial)\n(4) Tingkat kepuasan SDM terhadap kesejahteraan kerja",
            ],

            // E4.1 - Sistem Penjaminan Mutu Internal
            [
                'id_elemen' => $elemenMap['E4.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-4.1.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Dokumen SPMI yang sahih, terintegrasi, dan melampaui SN Dikti\n(2) Struktur organisasi dan tata kelola unit penjaminan mutu yang fungsional\n(3) Adanya peraturan, prosedur, dan manual (PPEPP) yang jelas\n(4) Konsistensi dan efektifitas pelaksanaan SPMI dalam seluruh proses tridarma",
            ],

            // E4.2 - Implementasi Perbaikan Berkelanjutan
            [
                'id_elemen' => $elemenMap['E4.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-4.2.1',
                'deskripsi_indikator' => "Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul secara terpadu dan koheren harus membuktikan bahwa sistem penjaminan mutu internal dijalankan dalam siklus yang konsisten dan berkelanjutan. Penilaian mencakup:\n(1) Tersedianya organisasi dan aktor yang melaksanakan, mendokumentasikan, serta menerapkan siklus PPEPP secara konsisten\n(2) Audit Mutu Internal dilaksanakan secara rutin sebagai mekanisme objektif untuk menilai efektivitas pelaksanaan PPEPP\n(3) Adanya temuan, tindak lanjut, serta bukti pengukuran dan evaluasi yang ditindaklanjuti secara berkelanjutan\n(4) Mekanisme menunjukkan adanya peningkatan standar mutu menuju budaya mutu yang diharapkan",
            ],

            // E4.3 - Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal
            [
                'id_elemen' => $elemenMap['E4.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-4.3.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi Pengusul menunjukkan:\n(1) Keterlibatan aktif pemangku kepentingan eksternal (asosiasi, pengguna) dalam sistem penjaminan mutu\n(2) Adanya dewan akademik atau forum sejenis yang melibatkan unsur eksternal untuk evaluasi pembelajaran\n(3) Upaya penjaminan mutu untuk aspek non-akademik (audit keuangan, K3, data)\n(4) Hasil evaluasi eksternal digunakan sebagai dasar perbaikan berkelanjutan",
            ],

            // E5.1 - Sarana dan Prasarana Belajar
            [
                'id_elemen' => $elemenMap['E5.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.1.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Standar ketersediaan sarana dan prasarana belajar (fisik dan non-fisik) yang memadai dan mutakhir\n(2) Bukti pengelolaan, pemeliharaan, dan pembaruan sarana prasarana yang berkala\n(3) Sarana prasarana mematuhi standar K3L dan mendukung inklusivitas\n(4) Pemanfaatan sarana prasarana yang optimal untuk mendukung pembelajaran",
            ],
            [
                'id_elemen' => $elemenMap['E5.1'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-5.1.1',
                'deskripsi_indikator' => "Data sarana dan prasarana:\n(1) Rasio luas ruang kuliah/studio/laboratorium terhadap cacah mahasiswa\n(2) Rasio peralatan utama terhadap cacah mahasiswa/dosen\n(3) Tingkat pemanfaatan sarana prasarana (misalnya jam penggunaan lab/studio)",
            ],

            // E5.2 - Sumber Pengetahuan
            [
                'id_elemen' => $elemenMap['E5.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.2.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan ketersediaan dan aksesibilitas sumber pengetahuan (koleksi cetak/digital) yang relevan dan mutakhir\n(2) Layanan pengelolaan sumber pengetahuan yang profesional dan berkelanjutan\n(3) Adanya investasi berkelanjutan untuk pembaruan koleksi\n(4) Sistem perlindungan HKI atas karya sivitas akademika",
            ],
            [
                'id_elemen' => $elemenMap['E5.2'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-5.2.1',
                'deskripsi_indikator' => "Data sumber pengetahuan:\n(1) Rasio cacah koleksi (buku, e-jurnal) terhadap cacah mahasiswa/dosen\n(2) Rasio anggaran perolehan sumber pengetahuan terhadap total anggaran",
            ],

            // E5.3 - Kepuasan Mahasiswa dan Alumni
            [
                'id_elemen' => $elemenMap['E5.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.3.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Sistem yang andal untuk mengidentifikasi dan mengukur kepuasan mahasiswa dan alumni (akademik dan non-akademik)\n(2) Hasil pengukuran dianalisis secara sistematis dan terdokumentasi\n(3) Bukti tindak lanjut dan kebijakan yang dihasilkan dari evaluasi kepuasan\n(4) Tingkat kepuasan mahasiswa dan alumni yang tinggi",
            ],
            [
                'id_elemen' => $elemenMap['E5.3'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-5.3.1',
                'deskripsi_indikator' => "Data kepuasan:\n(1) Tingkat kepuasan mahasiswa (skala 1-4) terhadap layanan akademik/non-akademik\n(2) Tingkat kepuasan alumni (skala 1-4) terhadap layanan",
            ],

            // E5.4 - Lulusan, Kajian Telusur, dan Kepuasan Pengguna
            [
                'id_elemen' => $elemenMap['E5.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.4.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Mekanisme terpadu untuk melaksanakan survei dan kajian penelusuran alumni (tracer study)\n(2) Hasil tracer study dianalisis secara mendalam dan dijadikan dasar perumusan kebijakan\n(3) Bukti adanya umpan balik dari pengguna lulusan yang dipertimbangkan\n(4) Relevansi kompetensi lulusan yang tinggi dengan kebutuhan masyarakat dan profesi",
            ],
            [
                'id_elemen' => $elemenMap['E5.4'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-5.4.1',
                'deskripsi_indikator' => "Data lulusan dan pengguna:\n(1) Persentase lulusan yang memperoleh pekerjaan dalam 6 bulan pertama\n(2) Rata-rata waktu tunggu kerja lulusan\n(3) Persentase lulusan yang bekerja di bidang yang sesuai\n(4) Tingkat kepuasan pengguna lulusan (skala 1-4)",
            ],

            // E6.1 - Organisasi dan Tata Kelola
            [
                'id_elemen' => $elemenMap['E6.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.1.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Struktur organisasi yang jelas, sah, dan diimplementasikan secara efektif\n(2) Dokumen tata kelola (Statuta/Peraturan/SOP) yang lengkap dan mutakhir\n(3) Kepemimpinan yang menjamin terlaksananya tridarma secara adil, transparan, dan akuntabel\n(4) Mekanisme pengambilan keputusan yang partisipatif dan bebas dari konflik kepentingan",
            ],

            // E6.2 - Kerja Sama dan Kemitraan
            [
                'id_elemen' => $elemenMap['E6.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.2.1',
                'deskripsi_indikator' => "Program Studi menunjukkan:\n(1) Adanya kebijakan dan tata kelola yang jelas untuk kerja sama dan kemitraan\n(2) Bukti pelaksanaan kerja sama dan kemitraan yang berdampak pada peningkatan mutu tridarma\n(3) Adanya evaluasi dan tindak lanjut atas hasil kerja sama\n(4) Keterlibatan dan kontribusi aktif pihak eksternal dalam kegiatan tridarma",
            ],
            [
                'id_elemen' => $elemenMap['E6.2'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-6.2.1',
                'deskripsi_indikator' => "Data kerja sama dan kemitraan:\n(1) Rasio cacah kerja sama/kemitraan aktif per tahun\n(2) Nilai investasi/pendanaan eksternal dari kerja sama/kemitraan\n(3) Jumlah luaran yang dihasilkan dari kerja sama/kemitraan",
            ],

            // E6.3 - Sistem dan Manajemen Informasi
            [
                'id_elemen' => $elemenMap['E6.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.3.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Adanya kebijakan dan tata kelola sistem informasi yang terintegrasi dan aman\n(2) Sistem informasi mendukung seluruh proses tridarma (akademik, keuangan, SDM)\n(3) Data yang disajikan valid, akurat, dan mudah diakses\n(4) Adanya upaya berkelanjutan untuk pengembangan dan pemeliharaan sistem",
            ],

            // E6.4 - Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan
            [
                'id_elemen' => $elemenMap['E6.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.4.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Adanya kebijakan K3L yang jelas dan terimplementasi\n(2) Program K3L yang mencakup pencegahan, penanggulangan, dan pemulihan\n(3) Bukti upaya nyata kontribusi terhadap kelestarian lingkungan (misalnya manajemen limbah, efisiensi energi)\n(4) Adanya pelatihan dan sosialisasi K3L secara berkala",
            ],

            // E6.5 - Keuangan, Keberlanjutan, dan Mitigasi Risiko
            [
                'id_elemen' => $elemenMap['E6.5'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.5.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan keuangan yang transparan dan akuntabel\n(2) Kecukupan dan keberlanjutan pendanaan untuk pelaksanaan tridarma\n(3) Adanya sistem mitigasi risiko keuangan dan operasional yang terukur\n(4) Adanya audit keuangan eksternal secara berkala",
            ],

            // E7.1 - Kebijakan Penelitian
            [
                'id_elemen' => $elemenMap['E7.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.1.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan penelitian tertulis yang selaras dengan visi misi dan etika\n(2) Adanya alokasi pendanaan internal dan strategi pendanaan eksternal\n(3) Mekanisme pemantauan, evaluasi, dan hilirisasi penelitian yang jelas\n(4) Kebijakan penelitian mendukung peran serta mahasiswa",
            ],

            // E7.2 - Proses Penelitian
            [
                'id_elemen' => $elemenMap['E7.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.2.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Mekanisme seleksi, pelaksanaan, dan diseminasi penelitian yang transparan dan akuntabel\n(2) Adanya penegakan etika penelitian secara konsisten\n(3) Keterlibatan aktif mahasiswa dalam kegiatan penelitian (tugas akhir, publikasi)\n(4) Repositori hasil penelitian yang mudah diakses",
            ],
            [
                'id_elemen' => $elemenMap['E7.2'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-7.2.1',
                'deskripsi_indikator' => "Cacah kegiatan yang relevan yang diukur melalui:\n(1) Rasio antara cacah dosen yang terlibat dalam kegiatan dengan cacah dosen tetap program studi (DTPS)\n(2) Rasio antara cacah mahasiswa yang terlibat dalam kegiatan penelitian dengan cacah mahasiswa dalam program studi\n(3) Bobot dampak kegiatan yang diukur melalui keterlibatan dan kontribusi pihak eksternal\n(4) Bobot penghargaan atas kegiatan dari pihak eksternal",
            ],

            // E7.3 - Luaran dan Dampak Penelitian
            [
                'id_elemen' => $elemenMap['E7.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.3.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Sistem perekaman luaran penelitian yang terpadu\n(2) Adanya bukti nyata dampak akademik, profesional, kebijakan, dan sosial-ekonomi dari penelitian\n(3) Bukti adanya layanan lanjut penelitian (hilirisasi, HKI, inkubasi)\n(4) Adanya skema penghargaan atas capaian penelitian",
            ],
            [
                'id_elemen' => $elemenMap['E7.3'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-7.3.1',
                'deskripsi_indikator' => "Data luaran penelitian:\n(1) Cacah publikasi ilmiah/paten/HKI/buku/luaran lain per DTPS\n(2) Cacah sitasi per DTPS\n(3) Nilai dana eksternal yang diperoleh dari pemanfaatan hasil penelitian",
            ],

            // E7.4 - Kebijakan Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['E7.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.4.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Kebijakan PkM terpadu yang selaras dengan visi misi dan etika\n(2) Adanya alokasi pendanaan internal dan strategi pendanaan eksternal\n(3) Mekanisme pemantauan, evaluasi, dan penciptaan dampak berkelanjutan\n(4) Kebijakan PkM berorientasi pada integrasi pendidikan dan kebutuhan masyarakat",
            ],

            // E7.5 - Proses Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['E7.5'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.5.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Mekanisme seleksi, pelaksanaan, dan evaluasi PkM yang transparan dan akuntabel\n(2) Adanya penegakan etika PkM\n(3) Keterlibatan aktif mahasiswa dalam kegiatan PkM sebagai bagian pencapaian pembelajaran\n(4) Proses PkM berbasis pada kompetensi SDM dan kebutuhan masyarakat",
            ],
            [
                'id_elemen' => $elemenMap['E7.5'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-7.5.1',
                'deskripsi_indikator' => "Cacah kegiatan yang relevan yang diukur melalui:\n(1) Rasio antara cacah dosen yang terlibat dalam kegiatan dengan cacah dosen tetap program studi (DTPS)\n(2) Rasio antara cacah mahasiswa yang terlibat dalam kegiatan PkM dengan cacah mahasiswa dalam program studi\n(3) Bobot dampak kegiatan yang diukur melalui keterlibatan dan kontribusi pihak eksternal\n(4) Bobot penghargaan atas kegiatan dari pihak eksternal",
            ],

            // E7.6 - Luaran dan Dampak Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['E7.6'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.6.1',
                'deskripsi_indikator' => "Institusi, UPPS, dan Program Studi menunjukkan:\n(1) Sistem perekaman luaran PkM yang terpadu\n(2) Bukti adanya dampak akademik, profesional, kebijakan, dan sosial-ekonomi-lingkungan dari PkM\n(3) Adanya bukti manfaat PkM dosen maupun mahasiswa dalam pengembangan dan pemberdayaan sosial, ekonomi, dan kesejahteraan masyarakat\n(4) Rekaman pemanfaatan luaran PkM dalam pembelajaran mahasiswa",
            ],
            [
                'id_elemen' => $elemenMap['E7.6'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-7.6.1',
                'deskripsi_indikator' => "Data luaran PkM:\n(1) Cacah publikasi/karya/inovasi yang diaplikasikan di masyarakat per DTPS\n(2) Cacah kegiatan PkM yang direplikasi oleh institusi/lembaga lain\n(3) Nilai dana eksternal yang diperoleh dari PkM",
            ],
        ];

        foreach ($indikator as $item) {
            if ($item['id_elemen'] !== null) {
                Indikator::create($item);
            }
        }
    }
}
