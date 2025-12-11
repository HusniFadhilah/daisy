<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IndikatorPenilaianElemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = Carbon::now();

        // Ambil data elemen_standar untuk mapping
        $elemenStandar = DB::table('elemen_standar')->pluck('id', 'pernyataan_elemen')->toArray();

        // Ambil data jenjang_penilaian untuk mapping
        $jenjangPenilaian = DB::table('jenjang_penilaian')->pluck('id', 'skor')->toArray();

        $data = [
            // Admisi Mahasiswa
            [
                'elemen' => 'Admisi Mahasiswa',
                'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid',
                'skor' => 0
            ],
            [
                'elemen' => 'Admisi Mahasiswa',
                'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan',
                'skor' => 1
            ],
            [
                'elemen' => 'Admisi Mahasiswa',
                'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target',
                'skor' => 2
            ],
            [
                'elemen' => 'Admisi Mahasiswa',
                'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan memenuhi target',
                'skor' => 3
            ],
            [
                'elemen' => 'Admisi Mahasiswa',
                'penilaian' => 'Terdapat bukti rasio keketatan admisi salah satu dari (a) melampaui 1:10, (b) mahsiswa afirmasi lebih dari 20%, (c) mahasiswa asing penuh waktu lebih dari 3%, atau (d) mahasiswa asing tidak penuh waktu lebih dari 10%',
                'skor' => 4
            ],

            // Dosen dan Tenaga Kependidikan
            [
                'elemen' => 'Dosen dan Tenaga Kependidikan',
                'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid',
                'skor' => 0
            ],
            [
                'elemen' => 'Dosen dan Tenaga Kependidikan',
                'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan',
                'skor' => 1
            ],
            [
                'elemen' => 'Dosen dan Tenaga Kependidikan',
                'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target',
                'skor' => 2
            ],
            [
                'elemen' => 'Dosen dan Tenaga Kependidikan',
                'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan memenuhi target',
                'skor' => 3
            ],
            [
                'elemen' => 'Dosen dan Tenaga Kependidikan',
                'penilaian' => 'Wajib adanya keterlibatan signifikan dan berkelanjutan para praktisi  yang direkomendasikan oleh asosiasi profesi yang relevan dalam proses pembelajaran, dan (a) Terdapat pengakuan eksternal dari organisasi profesi atau bidang keilmuan terhadap kualitas dosen dalam bentuk penghargaan tingkat nasional atau internasional, atau (b) Rasio dosen tetap program studi yang memiliki sertifikasi profesi yang relevan lebih dari 50% dan tenaga pendidikan fungsional tersertifikasi lebih dari 20%',
                'skor' => 4
            ],

            // Implementasi Perbaikan Berkelanjutan
            [
                'elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa',
                'skor' => 0
            ],
            [
                'elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik',
                'skor' => 1
            ],
            [
                'elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan',
                'skor' => 2
            ],
            [
                'elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik',
                'skor' => 3
            ],
            [
                'elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'penilaian' => 'Terdapat bukti perguruan tinggi, unit pengelola program studi, dan program studi menjadi rujukan implementasi penjaminan mutu bagi institusi lain',
                'skor' => 4
            ],

            // Kebijakan Penelitian
            [
                'elemen' => 'Kebijakan Penelitian',
                'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa',
                'skor' => 0
            ],
            [
                'elemen' => 'Kebijakan Penelitian',
                'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik',
                'skor' => 1
            ],
            [
                'elemen' => 'Kebijakan Penelitian',
                'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan',
                'skor' => 2
            ],
            [
                'elemen' => 'Kebijakan Penelitian',
                'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik',
                'skor' => 3
            ],
            [
                'elemen' => 'Kebijakan Penelitian',
                'penilaian' => 'Terdapat peta jalan penelitian yang mengacu dan relevan dengan kepentingan publik yang lebih luas (pemerintah daerah, nasional, internasional) yang ditunjukkan dengan keterlibatan mitra tersebut dalam proses penyusunan dan implementasinya yang konsisten dan berkesinambungan.',
                'skor' => 4
            ],

            // Kebijakan Pengabdian kepada Masyarakat
            [
                'elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa',
                'skor' => 0
            ],
            [
                'elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik',
                'skor' => 1
            ],
            [
                'elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan',
                'skor' => 2
            ],
            [
                'elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik',
                'skor' => 3
            ],
            [
                'elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'penilaian' => 'Terdapat peta jalan pengabdian kepada masyarakat yang mengacu dan relevan dengan kepentingan masyarakat lokal yang ditunjukkan dengan keterlibatan mitra tersebut dalam proses penyusunan dan implementasinya secara konsisten dan berkesinambungan',
                'skor' => 4
            ],

            // Kepuasan Mahasiswa dan Alumni
            [
                'elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa',
                'skor' => 0
            ],
            [
                'elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik',
                'skor' => 1
            ],
            [
                'elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan',
                'skor' => 2
            ],
            [
                'elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik',
                'skor' => 3
            ],
            [
                'elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'penilaian' => 'Adanya kontribusi nyata dari alumni seperti bantuan finansial, alat, beasiswa, atau kontribusi nonfinansial lainnya.',
                'skor' => 4
            ],

            // Kerja Sama dan Kemitraan
            [
                'elemen' => 'Kerja Sama dan Kemitraan',
                'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid',
                'skor' => 0
            ],
            [
                'elemen' => 'Kerja Sama dan Kemitraan',
                'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan',
                'skor' => 1
            ],
            [
                'elemen' => 'Kerja Sama dan Kemitraan',
                'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target atau tidak konsisten',
                'skor' => 2
            ],
            [
                'elemen' => 'Kerja Sama dan Kemitraan',
                'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan sebagian besar memenuhi target dan konsisten',
                'skor' => 3
            ],
            [
                'elemen' => 'Kerja Sama dan Kemitraan',
                'penilaian' => 'Terdapat pengakuan dari mitra UPPS dan/atau program studi secara resiprokal yang ditandai dengan adanya keterlibatan mahasiswa sendiri dan/atau mitra secara konsisten',
                'skor' => 4
            ],

            // Kesejahteraan Kerja
            [
                'elemen' => 'Kesejahteraan Kerja',
                'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa',
                'skor' => 0
            ],
            [
                'elemen' => 'Kesejahteraan Kerja',
                'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik',
                'skor' => 1
            ],
            [
                'elemen' => 'Kesejahteraan Kerja',
                'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan',
                'skor' => 2
            ],
            [
                'elemen' => 'Kesejahteraan Kerja',
                'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik',
                'skor' => 3
            ],
            [
                'elemen' => 'Kesejahteraan Kerja',
                'penilaian' => 'Terdapat organisasi K3L dengan petugas yang tersertifikasi dan adanya keikutsertaan pada jaminan kesehatan dan kesejahteraan yang relevan untuk semua pegawai tetap (100%) dengan kepuasan terhadap layanan minimal 80%',
                'skor' => 4
            ],

            // Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan
            [
                'elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa',
                'skor' => 0
            ],
            [
                'elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik',
                'skor' => 1
            ],
            [
                'elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan',
                'skor' => 2
            ],
            [
                'elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik',
                'skor' => 3
            ],
            [
                'elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'penilaian' => 'Institusi dan/atau UPPS memiliki ahli tersertifikasi dan memiliki infrastruktur penanggulangan bencana yang terstandar dan melibatkan pihak berwenang dalam melakukan verifikasi',
                'skor' => 4
            ],

            // Kesesuaian Visi Keilmuan
            ['elemen' => 'Kesesuaian Visi Keilmuan', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Kesesuaian Visi Keilmuan', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Kesesuaian Visi Keilmuan', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Kesesuaian Visi Keilmuan', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Kesesuaian Visi Keilmuan', 'penilaian' => '1) UPPS dan Program Studi mampu menunjukkan Sustainability dan keberlanjutan visi keilmuan,untuk menjamin relevansi serta keberlanjutan Visi Keilmuan. dan bukti keterlibatan aktif dari pengampu kepentingan eksternal (asosiasi, lembaga pemerintah, lembaga nonpemerintah yang secara spesifik relevan) dalam rangka memastikan relevansi visi misi dan 2) Terdapat bukti rekognisi eksternal yang ditandai dengan undangan atau kunjungan dari lembaga atau prodi di luar perguruan tinggi untuk berbagi pengalaman baik (sebagai responden studi tiru atau contoh baik)', 'skor' => 4],

            // Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal
            ['elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal', 'penilaian' => 'Terdapat bukti dukungan nyata dari organisasi profesi berupa kegiatan bersama atau pendampingan yang berkesinambungan dan konsisten.', 'skor' => 4],

            // Keuangan, Keberlanjutan, dan Mitigasi Risiko
            ['elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko', 'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid', 'skor' => 0],
            ['elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko', 'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan', 'skor' => 1],
            ['elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target', 'skor' => 2],
            ['elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan sebagian besar memenuhi target', 'skor' => 3],
            ['elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko', 'penilaian' => 'Institusi atau penyelenggara perguruan tinggi melaporkan hasil audit yang dilakukan oleh akuntan publik, didiseminasikan secara terbuka, dan memiliki rencana kontinjensi mitigasi risiko.', 'skor' => 4],

            // Kompetensi Lulusan dan Capaian Pembelajaran
            ['elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran', 'penilaian' => 'Program studi dapat menunjukkan hasil pembelajaran mahasiswa (portofolio) kepada publik dan organisasi profesi serta mendapat respons baik yang mengonfirmasi ketercapaian hasil belajarnya', 'skor' => 4],

            // Kurikulum
            ['elemen' => 'Kurikulum', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Kurikulum', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Kurikulum', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Kurikulum', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Kurikulum', 'penilaian' => 'Terdapat bukti rekognisi eksternal yang ditandai dengan undangan atau kunjungan dari prodi sejenis di luar perguruan tinggi untuk studi tiru khusus terkait kurikulum dan bukti pendampingan implementasinya atau dalam bentuk akreditasi atau sertifikasi internasional yang secara eksplisit menilai kurikulum', 'skor' => 4],

            // Legalitas Program dan Tata Pamong
            ['elemen' => 'Legalitas Program dan Tata Pamong', 'penilaian' => 'Tidak ada dokumen legalitas atau bukti yang disampaikan, atau tidak valid, atau kedaluarsa. Dokumen kunci legalitas institusi, UPPS dan Program Studi harus tersedia dan apabila tidak tersedia maka proses akreditasi dapat dibatalkan', 'skor' => 0],
            ['elemen' => 'Legalitas Program dan Tata Pamong', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Legalitas Program dan Tata Pamong', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Legalitas Program dan Tata Pamong', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Legalitas Program dan Tata Pamong', 'penilaian' => '1) UPPS dan Program Studi mampu menunjukkan Sustainability dan keberlanjutan program, yang ditunjukkan dengan legalitas pergantian struktur organisasi (untuk menjawab tantangan) dan atau restrukturisasi organisasi dan atau regenerasi/pergantian Pimpinan pada UPPS dan Program Studi. dan 2) Terdapat dokumen pengakuan dari eksternal misalnya berupa pengakuan reputasi seperti gelar ganda dengan perguruan tinggi internasional, atau 3) Terdapat Penghargaan terhadap institusi dari Kementerian atau asosiasi, sertifikasi manajemen dari lembaga bereputasi dan relevan untuk melakukan penilaian, atau lembaga pemeringkatan nasional atau internasional yang spesifik sesuai bidang', 'skor' => 4],

            // Luaran dan Dampak Penelitian
            ['elemen' => 'Luaran dan Dampak Penelitian', 'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid', 'skor' => 0],
            ['elemen' => 'Luaran dan Dampak Penelitian', 'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan', 'skor' => 1],
            ['elemen' => 'Luaran dan Dampak Penelitian', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target', 'skor' => 2],
            ['elemen' => 'Luaran dan Dampak Penelitian', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan sebagian besar memenuhi target', 'skor' => 3],
            ['elemen' => 'Luaran dan Dampak Penelitian', 'penilaian' => 'Terdapat karya ilmiah/karya inovatif dosen dan/atau mahasiswa yang mendapatkan penghargaan nasional dan internasional', 'skor' => 4],

            // Luaran dan Dampak Pengabdian kepada Masyarakat
            ['elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat', 'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid', 'skor' => 0],
            ['elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat', 'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan', 'skor' => 1],
            ['elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target', 'skor' => 2],
            ['elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan sebagian besar memenuhi target', 'skor' => 3],
            ['elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat', 'penilaian' => 'Terdapat kegiatan atau program yang mendapatkan penghargaan nasional dan/atau internasional', 'skor' => 4],

            // Lulusan, Kajian Telusur, dan Kepuasan Pengguna
            ['elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna', 'penilaian' => 'Adanya kontribusi nyata dari pengguna lulusan seperti bantuan finansial, alat, beasiswa, atau kontribusi nonfinansial lainnya seperti rekrutasi lulusan langsung, akses pelatihan, magang, penggunaan alat dan fasilitas dan sejenisnya', 'skor' => 4],

            // Organisasi dan Tata Kelola
            ['elemen' => 'Organisasi dan Tata Kelola', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Organisasi dan Tata Kelola', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Organisasi dan Tata Kelola', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target', 'skor' => 2],
            ['elemen' => 'Organisasi dan Tata Kelola', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan sebagian besar memenuhi target', 'skor' => 3],
            ['elemen' => 'Organisasi dan Tata Kelola', 'penilaian' => 'UPPS mendapatkan pengakuan terkait tata kelola dari pihak eksternal baik berupa studi tiru/banding, undangan diseminasi, pendampingan, penghargaan, atau hibah yang relevan', 'skor' => 4],

            // Pengembangan Kapasitas
            ['elemen' => 'Pengembangan Kapasitas', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Pengembangan Kapasitas', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Pengembangan Kapasitas', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Pengembangan Kapasitas', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Pengembangan Kapasitas', 'penilaian' => 'Terdapat pengakuan dari lembaga eksternal terhadap program pengembangan kapasitas berupa pemberian hibah atau penghargaan yang relevan.', 'skor' => 4],

            // Penilaian dan Evaluasi
            ['elemen' => 'Penilaian dan Evaluasi', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Penilaian dan Evaluasi', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Penilaian dan Evaluasi', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Penilaian dan Evaluasi', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Penilaian dan Evaluasi', 'penilaian' => 'Terdapat bukti penilaian eksternal berupa: (a) melibatkan organisasi profesi secara sistemik dalam proses penilaian dan evaluasi, atau (b) terakreditasi oleh lembaga akreditasi internasional berbasis hasil yang bereputasi dan mendapat pengakuan sesuai peraturan perundangan', 'skor' => 4],

            // Proses Penelitian
            ['elemen' => 'Proses Penelitian', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Proses Penelitian', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Proses Penelitian', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Proses Penelitian', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Proses Penelitian', 'penilaian' => 'Terdapat kegiatan diseminasi proses atau hasil penelitian yang terbuka dan dihadiri oleh masyarakat industri atau publik secara konsisten dan berkesinambungan', 'skor' => 4],

            // Proses Pengabdian kepada Masyarakat
            ['elemen' => 'Proses Pengabdian kepada Masyarakat', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Proses Pengabdian kepada Masyarakat', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Proses Pengabdian kepada Masyarakat', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Proses Pengabdian kepada Masyarakat', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Proses Pengabdian kepada Masyarakat', 'penilaian' => 'Terdapat kegiatan diseminasi proses atau hasil pengabdian kepada masyarakat yang terbuka sehingga terjadi proses adoProgram Studii di tempat lain yang dilaksanakan secara konsisten dan berkesinambungan', 'skor' => 4],

            // Proses dan Siklus Pembelajaran
            ['elemen' => 'Proses dan Siklus Pembelajaran', 'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid', 'skor' => 0],
            ['elemen' => 'Proses dan Siklus Pembelajaran', 'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan', 'skor' => 1],
            ['elemen' => 'Proses dan Siklus Pembelajaran', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target', 'skor' => 2],
            ['elemen' => 'Proses dan Siklus Pembelajaran', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan memenuhi target', 'skor' => 3],
            ['elemen' => 'Proses dan Siklus Pembelajaran', 'penilaian' => 'Terdapat bukti: (a) rasio keberhasilan studi melampaui 80% (b) kepuasan mahasiswa terhadap proses pembelajaran minimal 80% (c) rasio mahasiswa tepat waktu sesuai Waktu Tempuh Kurikulum minimal 50% (d) karya mahasiswa mendapat penghargaan eksternal minimal di level nasional', 'skor' => 4],

            // Sarana dan Prasarana Belajar
            ['elemen' => 'Sarana dan Prasarana Belajar', 'penilaian' => '(a) Kualitatif: tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa; (b) Kuantitatif: tidak ada data atau data tidak valid', 'skor' => 0],
            ['elemen' => 'Sarana dan Prasarana Belajar', 'penilaian' => '(a) Kualitatif: 1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik; (b) Kuantitatif: data tidak dapat divalidasi dengan meyakinkan', 'skor' => 1],
            ['elemen' => 'Sarana dan Prasarana Belajar', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan; (b) Kuantitatif: data valid namun tidak memenuhi target', 'skor' => 2],
            ['elemen' => 'Sarana dan Prasarana Belajar', 'penilaian' => '(a) Kualitatif: 4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik; (b) Kuantitatif: data valid dan sebagian besar memenuhi target', 'skor' => 3],
            ['elemen' => 'Sarana dan Prasarana Belajar', 'penilaian' => 'Terdapat pengakuan dari institusi lain berupa kunjungan studi tiru/banding, pemanfaatan sarana dan prasarana bersama, pemagangan terkait pengelolaan sarana prasarana, proyek kemitraan dengan industri, atau hibah peningkatan kapasitas dan kualitas.', 'skor' => 4],

            // Sarana dan Prasarana Kerja
            ['elemen' => 'Sarana dan Prasarana Kerja', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Sarana dan Prasarana Kerja', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Sarana dan Prasarana Kerja', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Sarana dan Prasarana Kerja', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Sarana dan Prasarana Kerja', 'penilaian' => 'Terdapat pengakuan dari lembaga eksternal yang melakukan penilaian terhadap kualitas sarana dan prasarana seperti Sertifikat Bangunan Hijau, Sertifikat Laik Fungsi, atau yang sejenis', 'skor' => 4],

            // Sistem Penjaminan Mutu Internal
            ['elemen' => 'Sistem Penjaminan Mutu Internal', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Sistem Penjaminan Mutu Internal', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Sistem Penjaminan Mutu Internal', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Sistem Penjaminan Mutu Internal', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Sistem Penjaminan Mutu Internal', 'penilaian' => 'Terdapat pengakuan dari lembaga eksternal terhadap reputasi penjaminan mutu internal berupa akreditasi, sertifikasi, validasi, penghargaan atau keanggotaan pada lembaga internasional terkait penjaminan mutu yang relevan', 'skor' => 4],

            // Sistem dan Manajemen Informasi
            ['elemen' => 'Sistem dan Manajemen Informasi', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Sistem dan Manajemen Informasi', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Sistem dan Manajemen Informasi', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Sistem dan Manajemen Informasi', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Sistem dan Manajemen Informasi', 'penilaian' => 'Institusi dan/atau UPPS mengembangkan sistem yang tervalidasi oleh ahli tersertifikasi dan memiliki sistem dan metode penanggulangan bencana termasuk disaster recovery center yang terstandar', 'skor' => 4],

            // Sumber Pengetahuan
            ['elemen' => 'Sumber Pengetahuan', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Sumber Pengetahuan', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Sumber Pengetahuan', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Sumber Pengetahuan', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Sumber Pengetahuan', 'penilaian' => 'Terdapat koleksi yang dapat menjadi rujukan bagi institusi lain dalam cacah yang signifikan dan berkesinambungan, pengakuan eksternal atas kualitas layanan, akreditasi perpustakaan, atau penghargaan lain terhadap perpustakaan yang relevan', 'skor' => 4],

            // Visi, Misi, Tujuan, dan Strategi
            ['elemen' => 'Visi, Misi, Tujuan, dan Strategi', 'penilaian' => 'Tidak ada aspek yang dipenuhi atau dokumen atau bukti yang disampaikan tidak valid atau kedaluarsa', 'skor' => 0],
            ['elemen' => 'Visi, Misi, Tujuan, dan Strategi', 'penilaian' => '1 atau 2 saja yang aspek terpenuhi dan masih belum dapat dipakai sebagai landasan penilaian dengan baik', 'skor' => 1],
            ['elemen' => 'Visi, Misi, Tujuan, dan Strategi', 'penilaian' => '4 aspek terpenuhi namun masih terdapat kekurangan atau  ketidaklengkapan yang tidak substansial, atau implementasi yang kurang meyakinkan', 'skor' => 2],
            ['elemen' => 'Visi, Misi, Tujuan, dan Strategi', 'penilaian' => '4 aspek terpenuhi didukung dokumen valid, lengkap, transparan, mudah diverifikasi, dan dikelola dengan baik', 'skor' => 3],
            ['elemen' => 'Visi, Misi, Tujuan, dan Strategi', 'penilaian' => '1) Terdapat bukti keterlibatan aktif dari pengampu kepentingan eksternal (asosiasi, lembaga pemerintah, lembaga nonpemerintah yang secara spesifik relevan) dalam rangka memastikan relevansi visi misi dan 2) PT-UPPS-Program Studi mampu menunjukkan proses evaluasi untuk menjamin relevansi serta keberlanjutan penyelenggaraan pendidikan tinggi. dan bukti keterlibatan aktif dari pengampu kepentingan eksternal (asosiasi, lembaga pemerintah, lembaga nonpemerintah yang secara spesifik relevan) dalam rangka memastikan relevansi visi misi', 'skor' => 4],
        ];

        $insertData = [];
        $skipped = 0;

        foreach ($data as $item) {
            // Cari elemen standar berdasarkan nama (pernyataan_elemen mengandung nama elemen)
            $elemenId = null;
            foreach ($elemenStandar as $pernyataan => $id) {
                if (stripos($pernyataan, $item['elemen']) !== false) {
                    $elemenId = $id;
                    break;
                }
            }

            $jenjangId = $jenjangPenilaian[$item['skor']] ?? null;

            if ($elemenId && $jenjangId) {
                $insertData[] = [
                    'id_elemen' => $elemenId,
                    'id_jenjang_penilaian' => $jenjangId,
                    'deskripsi_penilaian' => $item['penilaian'],
                    'keterangan' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            } else {
                $skipped++;
                $this->command->warn("Elemen '{$item['elemen']}' dengan skor {$item['skor']} tidak ditemukan");
            }
        }

        if (!empty($insertData)) {
            foreach (array_chunk($insertData, 100) as $chunk) {
                DB::table('indikator_penilaian_elemen')->insert($chunk);
            }
            $this->command->info("Berhasil: " . count($insertData) . " record, Dilewati: {$skipped}");
        }
    }
}
