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
            'D.1',
            'D.2',
            'D.3',
            'E.1',
            'E.2',
            'E.3',
            'E.4',
            'E.5',
            'P.1',
            'P.2',
            'P.3',
            'P.4',
            'I.1',
            'I.2',
            'I.3',
            'L.1',
            'L.2',
            'L.3',
            'L.4',
            'A.1',
            'A.2',
            'A.3',
            'A.4',
            'A.5',
            'R.1',
            'R.2',
            'R.3',
            'R.4',
            'R.5',
            'R.6'
        ])
            // ->whereNotIn('id_elemen', [1, 3, 7]) // Skip duplicate entries
            ->pluck('id', 'kode_elemen')
            ->toArray();

        $indikator = [
            // D.1 - Legalitas Program dan Tata Pamong
            [
                'id_elemen' => $elemenMap['D.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-1.1.1',
                'deskripsi_indikator' => "Institusi, UPPS dan Program Studi memiliki dokumen legalitas yang valid, tidak meragukan, serta menunjukkan perwujudan Good University Governance (GUG). Penilaian berfokus pada:\n(1). Legalitas Institusi, UPPS dan Program Studi yang lengkap;\n(termasuk dalam hal ini kerjasama dengan PS di dalam negeri dan atau Luar negeri)\n(2). Kebijakan dan dokumentasi Struktur Organisasi dan Tata Kelola yang lengkap dan implementatif;\n(3). Dokumen tata pamong yang dapat menjalankan fungsi secara efektif, (Misalnya dokumen tatacara pengisian jabatan/personil pada struktur organisasi yang di tetapkan, dan atau dilakukan perubahan pada point 2\n(4). Pelaporan pada PDDIKTI lengkap ",
            ],

            // D.2 - Visi, Misi, Tujuan, dan Strategi
            [
                'id_elemen' => $elemenMap['D.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-1.2.1',
                'deskripsi_indikator' => "Diferensiasi misi dalam visi, misi, dan rencana strategis menunjukkan bahwa Institusi, Unit UPPS dan Program Studi (Program Studi) menunjukkan:\n(1). memiliki visi-misi yang jelas, koheren, dan inspiratif;\n(2). terukur dan diukur dengan metode yang sahih;\n(3). diimplementasikan melalui perencanaan strategis dan perencanaan pelaksanaan yang baik, dan\n(4). mencerminkan prinsip Good University Governance (GUG)  dan menjaga keberlangsungan organisasi  organisasi misalnya PT-UPPS-Program Studi mampu menunjukkan proses evaluasi dan atau tidak lanjut hasil evaluasi VMTS untuk menjamin relevansi serta keberlanjutan penyelenggaraan pendidikan tinggi.",
            ],

            // D.3 - Kesesuaian Visi Keilmuan
            [
                'id_elemen' => $elemenMap['D.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-1.3.1',
                'deskripsi_indikator' => "Visi dan misi yang dimiliki oleh Institusi, UPPS, dan Program Studi selaras dan koheren dan efektif menjadi acuan pengembangan.\nVisi keilmuan program studi memenuhi aspek:\n(1). menunjukkan diferensiasi dan fokus pengembangan keilmuan;\n(2). mempunyai metode pengukuran yang jelas dan relevan;\n(3). adanya analisis yang menunjukkan dukungan sumber daya yang memadai, dan\n(4). menunjukkan adanya strategi dan program untuk mencapai daya saing/keunggulan dalam skala regional/ nasional/ internasional sesuai fokus misi.",
            ],

            // E.1 - Kurikulum
            [
                'id_elemen' => $elemenMap['E.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.1.1',
                'deskripsi_indikator' => "Program Studi dapat menunjukkan:\n(1). dokumen kurikulum yang memuat desain yang telah mengikuti pedoman terbaru dan berbasis hasil (outcomes based education);\n(2). bukti keterlibatan pihak eksternal terutama organisasi profesi, representasi dari pengguna lulusan atau industri, ahli, dan alumni;\n(3). bukti implementasinya secara konsisten dan berkesinambungan;\n(4). adanya bukti evaluasi terhadap kurikulum yang dilaksanakan secara berkesinambungan dan konsisten sesuai siklus pendidikan",
            ],

            // E.2 - Admisi Mahasiswa
            [
                'id_elemen' => $elemenMap['E.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.2.1',
                'deskripsi_indikator' => "Institusi, Unit Pengelola Program studi, atau Program Studi telah menjalankan proses admisi yang memastikan efektifitas dan keberlanjutan masukan, proses, dan luaran yang ditandai:\n(1). memiliki kebijakan rekrutasi akademik disusun lengkap dan sistematis;\n(2). mempunyai kebijakan afirmasi dan beasiswa yang proporsional;\n(3). dilaksanakan secara tertib, transparan, dan akuntabel, dan terpublikasi;\n(4). terdokumentasi dengan baik dan dievaluasi untuk perbaikan berkelanjutan.",
            ],
            [
                'id_elemen' => $elemenMap['E.2'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-2.2.2',
                'deskripsi_indikator' => "Program studi menyajikan data:\n(1). Reputasi pendidikan yang ditunjukkan dengan rasio keketatan admisi;\n(2). Inklusifitas pendidikan yang ditunjukkan dengan rasio mahasiswa reguler dan mahasiswa afirmasi;\n(3). Cacah mahasiswa asing dan/atau keterlibatan mahasiswa ke dalam kegiatan internasional/ke luar negeri dalam beragam bentuk kegiatan pembelajaran (berkredit penuh waktu, berkredit tidak penuh waktu tetapi lebih dari 1 semester, berkredit periode singkat, tanpa kredit)",
            ],

            // E.3 - Proses dan Siklus Pembelajaran
            [
                'id_elemen' => $elemenMap['E.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.3.1',
                'deskripsi_indikator' => "Program Studi menunjukkan adanya kebijakan, pelaksanaan, dan evaluasi siklus pendidikan yang efektif yang ditandai:\n(1). dokumen kurikulum disusun sesuai panduan/pedoman penyusunan kurikulum perguruan tinggi dan rujukan keilmuan, diperbarui berkelanjutan, dan lengkap;\n(2). pelaksanakan pendidikan dan pembelajaran yang tertib dan konsisten;\n(3). dokumentasi siklus pembelajaran yang sistemik dan data yang terjaga integritasnya;\n(4). evaluasi konsisten dan adanya upaya perbaikan berkelanjutan.",
            ],
            [
                'id_elemen' => $elemenMap['E.3'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-2.3.2',
                'deskripsi_indikator' => "Efektifitas pendidikan yang ditunjukkan dengan data:\n(1). masa studi sesuai desain kurikulum\n(2). prestasi akademik mahasiswa ",
            ],

            // E.4 - Penilaian dan Evaluasi
            [
                'id_elemen' => $elemenMap['E.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.4.1',
                'deskripsi_indikator' => "Program Studi dapat menunjukkan sistem penilaian dan evaluasi yang berbasis capaian secara efektif yang ditandai:\n(1). kebijakan penilaian dan evaluasi yang disusun berbasis pada rujukan terbaru termasuk pendidikan berbasis capaian;\n(2). dokumentasi proses evaluasi dan penilaian yang lengkap sebagai bukti pelaksanaan yang efektif;\n(3). terdapat sistem penilaian untuk menguji sesuai capaian pembelajaran dengan melibatkan organisasi profesi dalam prosesnya;\n(4). dilakukan evaluasi konsisten dan serta selalu diupayakan adanya perbaikan berkelanjutan.",
            ],

            // E.5 - Kompetensi Lulusan dan Capaian Pembelajaran
            [
                'id_elemen' => $elemenMap['E.5'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-2.5.1',
                'deskripsi_indikator' => "(1) adanya sistem pengukuran terhadap capaian pembelajaran dan memublikasikannya secara sistematis termasuk dalam surat keterangan pendamping ijazah\n(2). adanya matriks kesesuaian antara mata kuliah dan model pembelajarannya dengan capaian pembelajaran yang dituju yang jelas, koheren, dan efektif untuk mencapai tujuan pembelajaran;\n(3). adanya keterlibatan organisasi profesi / industri / pengguna lulusan dalam proses evaluasi;\n(4). adanya lembaga atau unit untuk melakukan evaluasi, perbaikan, peningkatan kompetensi secara konsisten, berkelanjutan, terstruktur, dan sistematis yang melibatkan beragam pengampu kepentingan",
            ],

            // P.1 - Dosen dan Tenaga Kependidikan
            [
                'id_elemen' => $elemenMap['P.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.1.1',
                'deskripsi_indikator' => "Unit Pengelola Program Studi (UPPS) perlu memastikan ketersediaan sumber daya manusia yang ditandai dengan:\n(1). ketercukupan dosen baik dari sisi cacah, kompetensi sebagai pendidik, dan kompetensi keahlian;\n(2). ketercukupan tenaga kependidikan yang mampu memastikan proses berjalan efektif;\n(3). adanya program pengembangan kompetensi pembelajaran, keahlian, riset, dan pengabdian untuk dosen;\n(4). adanya program pengembangan kompetensi dan profesionalitas untuk tenaga kependidikan termasuk tenaga fungsional.",
            ],
            [
                'id_elemen' => $elemenMap['P.1'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-3.1.1',
                'deskripsi_indikator' => "Program Studi dapat menunjukkan data:\n(1). daftar dosen yang mempunyai sertifikat keprofesian atau keahlian\n(2). daftar keterlibatan praktisi dalam pembelajaran\n(3). daftar keterlibatan asosiasi dalam pendidikan\n(4). daftar tenaga kependidikan bersertifikat keahlian dan/atau fungsional",
            ],

            // P.2 - Sarana dan Prasarana Kerja
            [
                'id_elemen' => $elemenMap['P.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.2.1',
                'deskripsi_indikator' => "Unit Pengelola Program Studi (UPPS) perlu memastikan ketersediaan sarana dan prasarana yang ditandai dengan:\n(1). ketercukupan ruang, laboratorium, alat, dan fasilitas pendukung untuk kegiatan tridarma bagi dosen dan pendidik lainnya;\n(2). ketercukupan ruang, alat, dan fasilitas kerja bagi tenaga kependidikan sesuai dengan bidang kerja dan layanannya;\n(3). ketersediaan fasilitas umum, sosial, kesehatan, kantin, sarana diffabel, dan kegiatan informal, termasuk ruang kemahasiswaan;\n(4). adanya program kerja untuk pemeliharaan dan peningkatan ketercukupan sarana dan prasarana kerja berbasis survei kepuasan dan standar-standar yang berlaku",
            ],

            // P.3 - Pengembangan Kapasitas
            [
                'id_elemen' => $elemenMap['P.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.3.1',
                'deskripsi_indikator' => "UUProgram Studi menunjukkan kebijakan, proses, dan evaluasi pengembangan kapasitas institusi yang ditandai dengan:\n(1). kebijakan rekrutasi dosen dan tenaga kependidikan berbasis perundangan, norma sistem prestasi (merit system), berkeadilan, dan nondiskriminatif;\n(2). kebijakan pengembangan kapasitas dosen dan tenaga kependidikan serta bukti implementasinya;\n(3). sistem penghargaan atas prestasi dan sanksi atas pelanggaran dan bukti implementasinya; dan\n(4). sistem pengembangan kapasitas manajerial untuk pengelolaan kelembagaan.",
            ],

            // P.4 - Kesejahteraan Kerja
            [
                'id_elemen' => $elemenMap['P.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-3.4.1',
                'deskripsi_indikator' => "UUProgram Studi menunjukkan kebijakan, proses, dan evaluasi terkait kesejahteraan dosen, tenaga kependidikan, dan mahasiswa yang ditandai dengan:\n(1). kebijakan remunerasi yang layak untuk semua unsur yang terlibat dalam institusi (dosen, dosen tidak tetap, tutor, instruktur, tenaga kependidikan, tenaga kontrak, asisten dll.)\n(2). kebijakan untuk menjamin kesehatan, keamanan, dan keselamatan kerja beserta bukti implementasinya\n(3). kebijakan penjaminan pencegahan dan penanganan kekerasan seksual dan bukti implementasinya\n(4). program peningkatan kesejahteraan berbasis pada survei kepuasan dan bukti implementasinya",
            ],

            // I.1 - Sistem Penjaminan Mutu Internal
            [
                'id_elemen' => $elemenMap['I.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-4.1.1',
                'deskripsi_indikator' => "UPPS dapat menunjukkan sistem penjaminan mutu internal tidak hanya dijalankan secara teknis, tetapi mendorong internalisasi budaya mutu yang berkelanjutan yang ditunjukkan sebagai berikut:\n(1). unit penjaminan mutu yang berjenjang dan efektif menjalankan fungsinya;\n(2). dokumen Sistem Penjaminan Mutu Internal (SPMI) yang terdiri atas kebijakan mutu, manual mutu, standar mutu, prosedur operasional standar, instrumen evaluasi dan monitoring, serta laporan monitoring dan evaluasi;\n(3). bukti proses penjaminan mutu yang berkelanjutan dengan melibatkan auditor yang bersifat independen, pelaksanaan audit mutu internal berkelanjutan, dan Rapat Tinjauan Manajemen (RTM) yang efektif dalam pengambilan keputusan;\n(4). kebijakan dan dokumen survei dan umpan balik dari seluruh pengampu kepentingan yang tersistem dengan baik.",
            ],

            // I.2 - Implementasi Perbaikan Berkelanjutan
            [
                'id_elemen' => $elemenMap['I.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-4.2.1',
                'deskripsi_indikator' => "Keterlaksanaan Sistem Penjaminan Mutu Internal (SPMI) yang memenuhi aspek berikut:\n(1). tersedianya dokumen indikator kinerja (dapat berupa Indikator Kinerja Utama dan Indikator Kinerja Tambahan) yang paling tidak mencakup elemen pada perundangan (terutama aspek tata pamong, tata kelola, kerjasama, mahasiswa, sumber daya manusia, keuangan, sarana dan prasarana, pendidikan, penelitian, pengabdian kepada masyarakat, luaran dan capaian tridharma perguruan tinggi;\n(2). terlaksananya siklus penjaminan mutu (siklus PPEPP);\n(3). tersedianya bukti sahih efektivitas pelaksanaan penjaminan mutu;\n(4). tersedianya bukti peningkatan standar.",
            ],

            // I.3 - Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal
            [
                'id_elemen' => $elemenMap['I.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-4.3.1',
                'deskripsi_indikator' => "UPPS dan Program Studi dapat menunjukkan adanya keterlibatan pengampu kepentingan eksternal dalam penjaminan mutu untuk memastikan relevansi yang ditunjukkan dengan:\n(1). adanya kajian yang melibatkan ahli eksternal, studi tiru atau banding, atau mengikutsertakan pengelola SPMI dalam pelatihan eksternal dalam rangka pengembangan SPMI;\n(2). adanya kajian yang melibatkan ahli eksternal, studi tiru atau banding, atau mengikutsertakan pengelola program studi atau dosen pengajar pada pelatihan eksternal dalam rangka pengembangan kualitas kurikulum dan pembelajaran;\n(3). adanya proses kaji ulang terhadap proses penjaminan mutu dan manajemen risiko yang hasilnya disosialisasikan pada pihak yang internal yang relevan; dan\n(4). adanya pengakuan eksternal terhadap sistem sistem penjaminan mutu dan implementasinya yang ditunjukkan berupa pengakuan mutu dari lembaga audit eksternal, lembaga akreditasi, lembaga sertifikasi, atau lembaga pemeringkatan.",
            ],

            // L.1 - Sarana dan Prasarana Belajar
            [
                'id_elemen' => $elemenMap['L.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.1.1',
                'deskripsi_indikator' => "Ketersediaan sarana dan prasarana belajar bagi mahasiswa yang ditandai dengan:\n(1). ketercukupan ruang dan laboratorium beserta alat pendukung untuk kegiatan belajar terstruktur dan/atau riset bagi mahasiswa;\n(2). ketersediaan sistem manajemen belajar (learning management system) dalam rangka mendukung akses pembelajaran\n(3). ketersediaan ruang informal untuk kegiatan belajar di luar jam kelas bagi mahasiswa\n(4). adanya program kerja untuk pemeliharaan dan peningkatan ketercukupan sarana dan prasarana belajar berbasis survei kepuasan dan kajian perkembangan keilmuan.",
            ],
            [
                'id_elemen' => $elemenMap['L.1'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-5.1.2',
                'deskripsi_indikator' => "Program Studi dapat menunjukkan data:\n(1). rasio antara ruang dan mahasiswa pengguna\n(2). rasio antara laboratorium dan mahasiswa pengguna\n(3). rasio antara alat utama pembelajaran dan mahasiswa pengguna",
            ],

            // L.2 - Sumber Pengetahuan
            [
                'id_elemen' => $elemenMap['L.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.2.1',
                'deskripsi_indikator' => "Ketersediaan sumber belajar bagi mahasiswa yang ditandai dengan:\n(1). ketersediaan buku referensi untuk setiap rencana pembelajaran semester yang selalu diperbarui dan dapat diakses oleh mahasiswa;\n(2). ketersediaan jurnal dan majalah ilmiah yang dapat diakses dengan mudah oleh mahasiswa;\n(3). ketersediaan aplikasi atau perangkat lunak yang dapat dipakai oleh mahasiswa untuk pengembangan kompetensi; dan\n(4). adanya program kerja untuk pemeliharaan dan peningkatan ketersediaan sumber belajar berbasis survei kepuasan dan kajian perkembangan keilmuan.",
            ],

            // L.3 - Kepuasan Mahasiswa dan Alumni
            [
                'id_elemen' => $elemenMap['L.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.3.1',
                'deskripsi_indikator' => "UPPS dan Program Studi perlu menunjukkan sistem umpan balik yang efektif dalam rangka memastikan proses belajar efektif yang ditandai dengan:\n(1). tersedianya instrumen pengukuran kepuasan mahasiswa terhadap proses belajar dan mengajar yang dilaksanakan secara konsisten;\n(2). tersedianya instrumen pengukuran kepuasan alumni terhadap proses belajar dan kesesuaian materi belajar dengan bidang kerja, masa tunggu berkarya, variasi pekerjaan atau karier yang ditempuh lulusan yang dilaksanakan secara konsisten;\n(3). instrumen memenuhi aspek kesahihan, validitas, konsistensi, integritas data, kedalaman dan ketajaman analisis untuk pengambilan keputusan; dan\n(4). adanya program kerja sebagai tindak lanjut temuan dalam rangka peningkatan berkelanjutan.",
            ],

            // L.4 - Lulusan, Kajian Telusur, dan Kepuasan Pengguna
            [
                'id_elemen' => $elemenMap['L.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-5.4.1',
                'deskripsi_indikator' => "Ketersediaan sistem umpan balik yang efektif dalam rangka memastikan proses belajar efektif yang ditandai dengan:\n(1). tersedianya instrumen pengukuran kepuasan pengguna lulusan terkait sikap, pengetahuan, keterampilan, serta kompetensi pendukung lainnya dari para lulusan;\n(2). terlaksananya pengukuran secara konsisten untuk lulusan TS+1;\n(3). instrumen memenuhi aspek kesahihan, validitas, konsistensi, integritas data, kedalaman dan ketajaman analisis untuk pengambilan keputusan; dan\n(4). adanya program kerja sebagai tindak lanjut temuan dalam rangka peningkatan berkelanjutan.",
            ],

            // A.1 - Organisasi dan Tata Kelola
            [
                'id_elemen' => $elemenMap['A.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.1.1',
                'deskripsi_indikator' => "Akuntabilitas publik yang ditandai dengan:\n(1). adanya susunan organisasi baik di UPPS maupun Program Studi yang diisi oleh dosen/tenaga kependidikan yang tersosialisasi dengan jelas peran, tugas, dan wewenangnya;\n(2). adanya kebijakan dan mekanisme penugasan berbasis pada prinsip kompetensi, integritas, kredibilitas, transparansi, akuntabilitas, dan berkeadilan yang diimplementasikan secara konsisten;\n(3). kepemimpinan manajerial efektif dan kepemimpinan publik yang kuat untuk mendorong reputasi program studi;\n(4). adanya prosedur eksekusi yang lengkpa dan dapat dibuktikan efektifitasnya dalam seluruh proses tridarma",
            ],

            // A.2 - Kerja Sama dan Kemitraan
            [
                'id_elemen' => $elemenMap['A.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.2.1',
                'deskripsi_indikator' => "Kerjasama dan kemitraan efektif yang ditunjukkan:\n(1). adanya kebijakan, sistem, mekanisme dan prosedur, serta penanggungjawab untuk membina kerjasama eksternal baik dalam hal kelembagaan, pendidikan, riset, pengabdian, dan kemahasiswaan;\n(2). terdapat bukti legalitas kerjasama dan implementasi kegiatan yang melibatkan sumber daya eksternal;\n(3). terdapat bukti implementasi kerjasama dalam beragam lingkup: kelembagaan, pendidikan, riset, pengabdian, dan kemahasiswaan;\n(4). terdapat bukti legalitas kemitraan setara (resiprokal) dalam bentuk gelar ganda dan sejenisnya yang berkelanjutan dan implementasinya.",
            ],
            [
                'id_elemen' => $elemenMap['A.2'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-6.2.2',
                'deskripsi_indikator' => "Efektivitas kerjasama yang ditandai dengan data:\n(1). daftar kerjasama, bentuk realisasi, lingkup (dalam negeri, luar negeri), jenis mitra (pemerintah, institusi pendidikan, perusahaan/industri, komunitas, dll), relevansi (pendidikan, riset, pengabdian, kelembagaan, kemahasiswaan)\n(2). daftar kemitraan resiprokal dan rincian mahasiswa yang terlibat",
            ],

            // A.3 - Sistem dan Manajemen Informasi
            [
                'id_elemen' => $elemenMap['A.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.3.1',
                'deskripsi_indikator' => "Efektifitas dan keandalan sistem yang ditunjukkan:\n(1). adanya sistem manajemen data dan informasi untuk merekam transaksi akademik yang telah dipakai dengan konsisten;\n(2). adanya sistem manajemen data dan informasi untuk beragam jenis pengelolaan yang telah dipakai dengan konsisten;\n(3). adanya bukti keandalan dalam pengelolaan data dan efektifitas sebagai bahan pembuatan kebijakan UPPS dan Program Studi; dan\n(4). adanya program kerja dalam rangka pengembangan keandalan, integrasi, efektifitas sistem secara berkelanjutan.",
            ],

            // A.4 - Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan
            [
                'id_elemen' => $elemenMap['A.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.4.1',
                'deskripsi_indikator' => "Efektifitas dan keandalan pengelolaan keamanan dan keselamatan kerja dan kelestarian lingkungan yang ditunjukkan:\n(1). adanya kebijakan, pengelola, sistem, dan alat untuk menjamin  keselamatan, kesehatan, dan keamanan kerja yang diterapkan secara konsisten;\n(2). adanya kebijakan, pengelola, sistem, dan alat untuk mendukung kelestarian lingkungan dan diterapkan secara konsisten;\n(3). adanya bukti keandalan dan hasil yang efektif dalam pengelolaan keselamatan, kesehatan, dan keamanan kerja dan kelestarian lingkungan; dan\n(4). adanya program kerja dalam rangka peningkatan keandalan dan efektifitas secara berkelanjutan berbasis umpan balik pengampu kepentingan dan/atau standar eksternal.",
            ],

            // A.5 - Keuangan, Keberlanjutan, dan Mitigasi Risiko
            [
                'id_elemen' => $elemenMap['A.5'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-6.5.1',
                'deskripsi_indikator' => "Penilaian pembiayaan termasuk pembiayaan difokuskan:\n(1). adanya kebijakan dan sistem pengelolaan keuangan yang pruden meliputi perencanaan, pengorganisasian, pengarahan, pengendalian kegiatan, evaluasi;\n(2). adanya bukti kecukupan, keefektifan, efisiensi, dan akuntabilitas, serta keberlanjutan pembiayaan untuk menunjang penyelenggaraan pendidikan, penelitian, dan pengabdian kepada masyarakat;\n(3). adanya audit yang dilaksanakan oleh auditor independen dan/atau auditor publik.\n(4). adanya upaya untuk menjaga keseimbangan antara penerimaan dan pembiayaan agar terjaga keberlanjutannya termasuk di dalamnya upaya mengelola risiko dengan analisis yang sahih dan mitigasi yang adekuat.",
            ],

            // R.1 - Kebijakan Penelitian
            [
                'id_elemen' => $elemenMap['R.1'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.1.1',
                'deskripsi_indikator' => "Relevansi kebijakan penelitian yang ditunjukkan:\n(1). adanya peta jalan yang memayungi tema penelitian dosen dan mahasiswa serta pengembangan keilmuan program studi;\n(2). adanya bukti sosialisasi dan koordinasi baik antar dosen maupun dengan mahasiswa sebagai bagian dari implementasi peta jalan;\n(3). adanya bukti pendanaan atau upaya pencarian dana eksternal dalam rangka pelaksanaan penelitian; dan\n(4). adanya kaji ulang (review) terhadap keterlaksanaan program, hambatan, tingkat keberhasilan dan dampak.",
            ],

            // R.2 - Proses Penelitian
            [
                'id_elemen' => $elemenMap['R.2'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.2.1',
                'deskripsi_indikator' => "Efektifitas proses penelitian yang ditunjukkan dengan:\n(1). adanya dokumen proses, laporan penelitian dan/atau luaran penelitian yang dilakukan dosen yang sesuai dengan peta jalan:\n(2). adanya dokumen proses, laporan penelitian, dan/atau luaran penelitian yang merupakan hasil kolaboratif dosen - mahasiswa;\n(3). adanya bukti manfaat penelitian bagi UPPS atau program studi, bagi dosen sebagai bagian pengembangan karier dan pembaruan materi pembelajaran dan bagi mahasiswa sebagai bagian dari proses pembelajaran;\n(4). adanya diseminasi atas hasil penelitian dalam rangka menciptakan iklim akademik yang berkelanjutan",
            ],

            // R.3 - Luaran dan Dampak Penelitian
            [
                'id_elemen' => $elemenMap['R.3'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.3.1',
                'deskripsi_indikator' => "Dampak atas luaran yang diukur melalui:\n(1). relevansi publikasi ilmiah atau karya inovatif dosen maupun mahasiswa terhadap peningkatan reputasi program studi dan perwujudan visi dan misi;\n(2). adanya pemanfaatan hasil penelitian atau karya inovasi dosen maupun mahasiswa dalam pengembangan dan pemberdayaan sosial, ekonomi, dan kesejahteraan masyarakat;\n(3). adanya pemanfaatan hasil penelitian atau karya inovasi dosen maupun mahasiswa yang diadoProgram Studii oleh industri; dan\n(3). adanya pemanfaatan hasil penelitian atau karya inovasi dosen maupun mahasiswa yang menjadi modal kewirausahaan mahasiswa.",
            ],
            [
                'id_elemen' => $elemenMap['R.3'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-7.3.2',
                'deskripsi_indikator' => "Cacah luaran baik berupa publikasi ilmiah atau karya inovatif yang relevan yang diukur melalui:\n(1). rasio antara publikasi ilmiah/karya inovatif dengan cacah dosen tetap program studi (DTPS);\n(2). rasio antara publikasi ilmiah/karya inovatif mahasiswa baik mandiri maupun kolaborasi dengan dosen dengan cacah mahasiswa dalam program studi;\n(3). bobot publikasi ilmiah/karya inovatif dosen dengan judul yang relevan dengan bidang program studi yang diukur melalui reputasi media dan lingkup publikasinya serta faktor dampaknya;\n(4). bobot penghargaan atas karya ilmiah/karya inovatif mahasiswa",
            ],

            // R.4 - Kebijakan Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['R.4'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.4.1',
                'deskripsi_indikator' => "Relevansi kebijakan program pengabdian kepada masyarakat (PkM) yang ditunjukkan:\n(1). adanya peta jalan yang memayungi tema (PkM)  dosen dan mahasiswa sesuai dengan visi misi program studi;\n(2). adanya bukti sosialisasi dan koordinasi baik antar dosen maupun dengan mahasiswa sebagai bagian dari implementasi peta jalan (PkM);\n(3). adanya bukti pendanaan atau upaya pencarian dana eksternal atau kemitraan dalam rangka pelaksanaan (PkM); dan\n(4). adanya kaji ulang (review) terhadap keterlaksanaan program, hambatan, tingkat keberhasilan dan dampak.",
            ],

            // R.5 - Proses Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['R.5'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.5.1',
                'deskripsi_indikator' => "Efektifitas proses pengabdian kepada masyarakat (PkM) yang ditunjukkan dengan:\n(1). adanya dokumen proses, laporan penelitian dan/atau luaran (PkM) yang dilakukan dosen yang sesuai dengan peta jalan dan visi misi:\n(2). adanya dokumen proses, laporan, dan/atau luaran (PkM) yang merupakan hasil kolaboratif dosen - mahasiswa;\n(3). adanya bukti manfaat (PkM) bagi UPPS atau program studi, bagi dosen sebagai bagian pengembangan karier dan pembaruan materi pembelajaran dan bagi mahasiswa sebagai bagian dari proses pembelajaran;\n(4). adanya diseminasi atas hasil (PkM) dalam rangka menciptakan iklim akademik yang berkelanjutan",
            ],

            // R.6 - Luaran dan Dampak Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['R.6'] ?? null,
                'id_jenis' => 1, // Kualitatif
                'kode_indikator' => 'IK-7.6.1',
                'deskripsi_indikator' => "Dampak atas luaran pengabdian kepada masyarakat (PkM) yang diukur melalui:\n(1). relevansi kegiatan (PkM) dosen maupun mahasiswa terhadap peningkatan reputasi program studi dan perwujudan visi dan misi;\n(2). adanya bukti manfaat (PkM) dosen maupun mahasiswa dalam pengembangan dan pemberdayaan sosial, ekonomi, dan kesejahteraan masyarakat;\n(3). adanya program PkM yang direplikasi oleh institusi/lembaga lain; dan\n(3). adanya pemanfaatan hasil PkM dosen maupun mahasiswa yang menjadi modal wirausaha sosial mahasiswa.",
            ],
            [
                'id_elemen' => $elemenMap['R.6'] ?? null,
                'id_jenis' => 2, // Kuantitatif
                'kode_indikator' => 'IKn-7.6.2',
                'deskripsi_indikator' => "Cacah kegiatan yang relevan yang diukur melalui:\n(1). rasio antara cacah dosen yang terlibat dalam kegiatan dengan cacah dosen tetap program studi (DTPS);\n(2). rasio antara cacah mahasiswa yang terlibat dalam kegiatan PkM dengan cacah mahasiswa dalam program studi;\n(3). bobot dampak kegiatan yang diukur melalui keterlibatan dan kontribusi pihak eksternal;\n(4). bobot penghargaan atas kegiatan dari pihak eksternal",
            ],
        ];

        foreach ($indikator as $item) {
            if ($item['id_elemen'] !== null) {
                Indikator::create($item);
            }
        }
    }
}
