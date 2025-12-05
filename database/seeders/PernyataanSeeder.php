<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pernyataan;
use App\Models\ElemenStandar;

class PernyataanSeeder extends Seeder
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
        ->pluck('id_elemen', 'kode_elemen')
        ->toArray();

        $pernyataan = [
            // E1.1 - Legalitas Program dan Tata Pamong
            [
                'id_elemen' => $elemenMap['E1.1'] ?? null,
                'code' => 'P1.1.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi (UPPS), dan Program Studi pengusul harus memiliki landasan hukum yang sah dan dapat dipertanggungjawabkan, sebagaimana ditetapkan oleh pemerintah atau lembaga resmi yang berwenang. Apabila UPPS dan PS mengalami perubahan nama atau status kelembagaan sepanjang sejarah pendiriannya, seluruh dokumen yang menunjukkan kesinambungan dan keabsahan perubahan tersebut wajib tersedia secara lengkap dan terdokumentasi dengan baik. Penggunaan gelar akademik yang melekat pada program studi harus mengacu pada ketentuan peraturan perundang-undangan yang berlaku serta didukung oleh dokumen resmi yang relevan. Apabila Program Studi Mengembangkan kerjasama dengan Program Studi dalam Negeri dan Atau Luar Negeri, Perlu di Jelaskan Jenis Kerjasama yang berkaitan dengan legalitas program kerjasama, dalam hal ini Pengakuan SKS/Credit Transfer; Pengkuan Gelar dan Atau Penggunaan Gelar Ganda dari dalam Negeri dan atau Luar Negeri. Penggunaan gelar akademik pada program kerjasama bergelar luar negeri, bergelar ganda, yang melekat pada program studi harus mengacu pada ketentuan peraturan perundang-undangan yang berlaku serta didukung oleh dokumen resmi yang relevan. (Pemenuhan standar ini mencerminkan perwujudan tata pamong dan tata kelola perguruan tinggi yang baik, ditandai dengan sistem dokumentasi yang transparan, akuntabel, dan konsisten, sehingga mampu menjamin legitimasi serta kredibilitas penyelenggaraan pendidikan tinggi)',
            ],

            // E1.2 - Visi, Misi, Tujuan, dan Strategi
            [
                'id_elemen' => $elemenMap['E1.2'] ?? null,
                'code' => 'P1.2.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi pengusul harus memiliki visi, misi, dan tujuan yang dirumuskan secara jelas, sah, serta sesuai dengan ketentuan peraturan perundang-undangan yang berlaku. Perumusan visi dan misi dilakukan melalui proses yang partisipatif, transparan, serta berbasis pada rujukan akademik dan regulatif yang relevan, sehingga mampu menjadi sumber inspirasi bagi seluruh sivitas akademika dan pemangku kepentingan dalam mencapai cita-cita bersama. Visi dan misi dapat berupa gagasan yang bersifat ideal dan berjangka panjang, sementara tujuan harus terukur serta dapat diturunkan ke dalam strategi dan rencana kerja yang realistis. Seluruh pernyataan visi, misi, dan tujuan tersebut wajib disosialisasikan secara konsisten dan diwujudkan dalam dokumen perencanaan strategis maupun operasional, dengan implementasi yang terpantau. Pernyataan visi, misi, tujuan dievaluasi, dan disesuaikan secara berkelanjutan untuk menjamin relevansi serta keberlanjutan penyelenggaraan pendidikan tinggi.',
            ],

            // E1.3 - Kesesuaian Visi Keilmuan
            [
                'id_elemen' => $elemenMap['E1.3'] ?? null,
                'code' => 'P1.3.1',
                'pernyataan' => 'Program Studi pengusul harus memiliki visi keilmuan yang selaras dengan visi dan misi institusi serta Unit Pengelola Program Studi, sekaligus menegaskan identitas akademik yang khas pada bidang yang ditekuni. Visi keilmuan tersebut wajib tercermin dalam kurikulum dan proses pembelajaran, dengan menampilkan keunikan dan kekhasan yang relevan dengan rumpun keilmuan desain, perencanaan, lingkungan, dan arsitektur. Kejelasan visi keilmuan harus diwujudkan dalam strategi implementasi yang terukur, terencana, dan konsisten, sehingga mampu mengarahkan pengembangan kompetensi lulusan, kegiatan penelitian, serta kontribusi pengabdian kepada masyarakat. Dengan demikian, visi keilmuan bukan hanya menjadi pernyataan ideal, tetapi juga menjadi dasar operasional yang mengintegrasikan karakter akademik program studi dengan kebutuhan masyarakat, perkembangan ilmu pengetahuan, dan tuntutan profesi di tingkat nasional maupun internasional.',
            ],

            // E2.1 - Kurikulum
            [
                'id_elemen' => $elemenMap['E2.1'] ?? null,
                'code' => 'P2.1.1',
                'pernyataan' => 'Program Studi pengusul harus memiliki kurikulum pembelajaran berbasis capaian (Outcome-Based Education/OBE) yang inovatif, dirumuskan secara sistematis, sesuai dengan ketentuan peraturan perundang-undangan di bidang pendidikan tinggi, serta selaras dengan perkembangan ilmu pengetahuan, teknologi, seni, kebutuhan masyarakat, dan tuntutan dunia profesi. Penyusunan kurikulum wajib dilaksanakan melalui mekanisme yang terstruktur, transparan, partisipatif, dan berbasis pada kajian pemangku kepentingan, sehingga setiap capaian pembelajaran lulusan dapat dijamin keterkaitannya dengan profil lulusan, standar kompetensi, dan kebutuhan masyarakat. Dokumen kurikulum harus dapat menunjukkan koherensi pencapaian hasil pembelajaran yang ditunjukkan dengan peta keterkaitan antara visi keilmuan program studi, capaian pembelajaran, struktur mata kuliah, beban studi, metode pembelajaran, fleksibilitas proses, dan strategi asesmen yang adekuat. Kurikulum yang dihasilkan wajib menunjukkan keterukuran capaian melalui sistem evaluasi berkelanjutan dan mekanisme umpan balik dari pemangku kepentingan internal maupun eksternal. Apabila menerapkan fleksibilitas proses pembelajaran (tatap muka, jarak jauh termasuk daring, atau kombinasi tatap muka dengan jarak jauh), keleluasaan kepada mahasiswa untuk mengikuti pendidikan dari berbagai tahapan kurikulum, atau penerapan rekognisi pembelajaran lampau, strategi implementasinya harus secara eksplisit dijelaskan dan sesuai dengan ketentuan peraturan perundang-undangan. Seluruh proses penyusunan harus terdokumentasi dalam laporan resmi yang sahih dan dapat ditelusuri. Dokumen memiliki legalitas yang jelas sesuai dengan ketentuan regulasi yang berlaku, dan dokumen pokok kurikulum harus tersedia dan dapat diakses secara terbuka sehingga menjamin transparansi, akuntabilitas, serta keberlanjutan penyelenggaraan program studi.',
            ],

            // E2.2 - Admisi Mahasiswa
            [
                'id_elemen' => $elemenMap['E2.2'] ?? null,
                'code' => 'P2.2.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi pengusul harus memiliki mekanisme penerimaan mahasiswa yang terintegrasi, transparan, dan dituangkan dalam kebijakan resmi yang sah. Kebijakan admisi tersebut wajib menjabarkan sistem, prosedur, dan kriteria seleksi mahasiswa baru yang selaras dengan kapasitas program studi, termasuk daya tampung, ketersediaan sumber daya dosen, fasilitas pembelajaran, serta dukungan akademik dan non-akademik yang memadai. Proses admisi harus dipublikasikan secara terbuka dan dilaksanakan secara konsisten, sehingga menjamin kredibilitas dan akuntabilitas penerimaan mahasiswa. Program Studi wajib menyelenggarakan pencatatan dan pelaporan data admisi secara sistematis, meliputi jumlah calon mahasiswa pendaftar, mahasiswa yang diterima dan teregistrasi, hingga perkembangan setiap angkatan (cohort) sampai tahap kelulusan. Data ini menjadi instrumen evaluasi berkelanjutan untuk menilai kesesuaian antara jumlah mahasiswa, kapasitas layanan akademik, serta keberhasilan penyelenggaraan pendidikan. Kebijakan admisi harus mempertimbangkan konteks lokalitas, baik dalam menjawab kebutuhan masyarakat sekitar maupun dalam memperhatikan keragaman latar belakang sosial, ekonomi, dan budaya calon mahasiswa. Program afirmasi dan inklusivitas, seperti beasiswa, insentif, atau dukungan khusus bagi kelompok kurang mampu dan mahasiswa berkebutuhan khusus, merupakan bagian penting dari upaya mewujudkan akses pendidikan yang adil dan berkelanjutan. Dengan demikian, mekanisme admisi tidak hanya berfungsi sebagai pintu masuk akademik, tetapi juga sebagai cerminan komitmen program studi untuk menjaga mutu, relevansi, dan kontribusi nyata terhadap lingkungan sosial serta pembangunan daerah.',
            ],

            // E2.3 - Proses dan Siklus Pembelajaran
            [
                'id_elemen' => $elemenMap['E2.3'] ?? null,
                'code' => 'P2.3.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi pengusul harus menjelaskan secara komprehensif bagaimana proses pembelajaran diselenggarakan dan bagaimana siklus belajar mahasiswa dikelola secara terpadu sepanjang masa studi, termasuk apabila pelaksanaan fleksibilitas proses pembelajaran. Proses ini wajib dituangkan dalam kebijakan dan peraturan resmi yang koheren, konsisten, serta didukung oleh sistem yang andal untuk menjamin mutu dan akuntabilitas penyelenggaraan pendidikan. Pengelolaan siklus belajar mahasiswa mencakup seluruh tahapan utama, mulai dari perencanaan studi, pengelolaan sumber daya (dosen, ruang, jadwal, dan sarana pembelajaran), pelaksanaan perkuliahan, evaluasi capaian pembelajaran, hingga pelaporan status akademik dan kelulusan mahasiswa. Setiap tahapan harus dicatat dengan baik dan dikelola secara terintegrasi, baik melalui sistem manual maupun digital, dengan memastikan integritas data dan keabsahan informasi yang dihasilkan. Program Studi wajib memiliki mekanisme pelaporan yang jelas kepada institusi induk, pangkalan data pendidikan tinggi, mahasiswa, dan orang tua atau wali, serta dianjurkan untuk menyediakan data publik yang relevan bagi pemangku kepentingan eksternal.',
            ],

            // E2.4 - Penilaian dan Evaluasi
            [
                'id_elemen' => $elemenMap['E2.4'] ?? null,
                'code' => 'P2.4.1',
                'pernyataan' => 'Program studi harus memiliki kebijakan dan proses penilaian yang selaras dengan capaian pembelajaran yang dituju. Penilaian wajib dituangkan dalam rencana asesmen yang terdokumentasi dengan baik, dapat diakses secara daring maupun luring, serta dilaksanakan secara transparan, konsisten, dan adil. Program studi wajib melaksanakan evaluasi secara berkala untuk memastikan ketercapaian tujuan pembelajaran dan melakukan perbaikan berkelanjutan. Program studi juga wajib menyediakan mekanisme banding yang jelas, terukur, terdokumentasi, dan dijalankan secara konsisten agar hak mahasiswa untuk memperoleh penilaian objektif tetap terjamin.',
            ],

            // E2.5 - Kompetensi Lulusan dan Capaian Pembelajaran
            [
                'id_elemen' => $elemenMap['E2.5'] ?? null,
                'code' => 'P2.5.1',
                'pernyataan' => 'Program studi harus menjabarkan secara eksplisit capaian pembelajaran yang dituju dan memublikasikannya secara transparan agar dapat dipahami oleh seluruh pemangku kepentingan. Capaian pembelajaran wajib disusun selaras dengan kebutuhan masyarakat dan dunia industri yang relevan, serta harus dibuktikan adanya keterlibatan pemangku kepentingan eksternal dalam proses perumusannya. Untuk menjamin pencapaiannya, program studi wajib merumuskan penilaian yang dinyatakan dalam rencana asesmen capaian pembelajaran (learning outcomes assessment plan), yang dirancang secara komprehensif dengan dukungan sistem manual, digital, atau kombinasi keduanya. Program studi wajib menjabarkan mata kuliah maupun kegiatan pembelajaran kunci, termasuk mata kuliah integratif atau kegiatan Capstone, yang secara nyata dapat merepresentasikan capaian pembelajaran dan kompetensi lulusan yang dituju. Penilaian atas capaian ini wajib dilakukan secara konsisten, berkesinambungan, dan terdokumentasi dengan baik. Untuk menunjukkan pelampauan standar, program studi wajib menghadirkan bukti kinerja mahasiswa berupa karya, tugas akhir, publikasi, inovasi, atau bentuk pencapaian lain yang relevan. Bukti tersebut wajib direkam dalam bentuk asli atau rekaman digital yang menjamin originalitasnya, tanpa rekayasa ulang untuk kepentingan asesmen. Seluruh bukti ini berfungsi sebagai representasi nyata kualitas lulusan dan validitas capaian pembelajaran. Sebagai cakupan khusus, standar kompetensi lulusan dan capaian pembelajaran wajib dilengkapi dengan dokumen suplemen yang disusun secara spesifik sesuai bidang studi, sehingga mampu menegaskan karakter keilmuan program studi sekaligus memperlihatkan relevansinya dalam konteks lokal, nasional, maupun global.',
            ],

            // E3.1 - Dosen dan Tenaga Kependidikan
            [
                'id_elemen' => $elemenMap['E3.1'] ?? null,
                'code' => 'P3.1.1',
                'pernyataan' => 'Institusi, unit pengelola program studi, dan program studi pengusul harus memiliki kebijakan yang jelas, terpadu, dan terdokumentasi mengenai pengembangan sumber daya manusia. Kebijakan tersebut wajib mencakup perencanaan, pelaksanaan, dan evaluasi yang berkesinambungan sehingga dapat menjamin ketersediaan dan keberlanjutan sumber daya manusia yang kompeten. Unit pengelola program studi harus memastikan ketercukupan jumlah, kualifikasi, dan kompetensi sumber daya manusia untuk mendukung pelaksanaan tridarma perguruan tinggi secara proporsional. Program studi wajib menegaskan bahwa tenaga pendidik, tenaga kependidikan, dan tenaga pendukung lainnya memiliki kapasitas sesuai standar nasional, serta mendapat kesempatan untuk pengembangan profesional secara berkelanjutan. Pengelolaan sumber daya manusia harus memperhatikan konteks lokalitas, kebutuhan strategis program studi, serta dinamika perkembangan ilmu pengetahuan, teknologi, dan seni sesuai bidang. Dengan demikian, program studi dapat memastikan mutu penyelenggaraan pendidikan tetap terjaga dan relevan dengan tuntutan masyarakat, industri, dan dunia akademik.',
            ],

            // E3.2 - Sarana dan Prasarana Kerja
            [
                'id_elemen' => $elemenMap['E3.2'] ?? null,
                'code' => 'P3.2.1',
                'pernyataan' => 'Institusi atau Unit Pengelola Program Studi perlu menjabarkan kebijakan terkait sarana dan prasarana kerja secara terpadu dan koheren bagi seluruh sumber daya manusia yang terlibat dalam pelaksanaan tridarma. Kebijakan ini mencakup dosen, tenaga kependidikan, serta tenaga profesional yang berperan sebagai tutor, instruktur, asisten, maupun tenaga pendukung lainnya, baik yang berstatus aparatur negara, pegawai tetap, pegawai kontrak, maupun rekanan kerja (outsourcing). Aspek ketersediaan sarana dan prasarana kerja meliputi pemberian hak ruang kerja/fasilitas pendukung kerja dan kondisi kerja yang manusiawi, dan adanya skema, program, atau insentif internal untuk mendukung sarana dan prasarana kerja seluruh sumber daya manusia yang terlibat, sehingga terjamin keberlanjutan dan kualitas kontribusi mereka dalam pelaksanaan tridarma.',
            ],

            // E3.3 - Pengembangan Kapasitas
            [
                'id_elemen' => $elemenMap['E3.3'] ?? null,
                'code' => 'P3.3.1',
                'pernyataan' => 'Institusi atau Unit Pengelola Program Studi harus memiliki dan menjabarkan kebijakan pengembangan kapasitas sumber daya secara terpadu dan koheren, mulai dari perencanaan, pelaksanaan, hingga evaluasi. Unit Pengelola Program Studi harus memastikan ketercukupan program dan sumber daya untuk mendukung pelaksanaan tridarma perguruan tinggi dan secara proporsional kepentingan pengembangan Program Studi pengusul. Kebijakan harus dapat menjamin agar seluruh dosen dan tenaga kependidikan dapat mengembangkan karier dan potensi diri secara merata, proporsional, dan tanpa diskriminasi.',
            ],

            // E3.4 - Kesejahteraan Kerja
            [
                'id_elemen' => $elemenMap['E3.4'] ?? null,
                'code' => 'P3.4.1',
                'pernyataan' => 'Institusi atau Unit Pengelola Program Studi perlu menjabarkan kebijakan terkait kesejahteraan, kesehatan, keselamatan, dan keamanan kerja secara terpadu dan koheren bagi seluruh sumber daya manusia yang terlibat dalam pelaksanaan tridarma. Kebijakan ini mencakup dosen, tenaga kependidikan, serta tenaga profesional yang berperan sebagai tutor, instruktur, asisten, maupun tenaga pendukung lainnya, baik yang berstatus aparatur negara, pegawai tetap, pegawai kontrak, maupun rekanan kerja (outsourcing). Aspek kesejahteraan kerja meliputi pemberian remunerasi yang layak sesuai ketentuan Pemerintah, fasilitas kebutuhan dasar dan kondisi kerja yang manusiawi, dan adanya skema, program, atau insentif internal untuk mendukung kesejahteraan, kesehatan, keselamatan, dan keamanan kerja seluruh sumber daya manusia yang terlibat, sehingga terjamin keberlanjutan dan kualitas kontribusi mereka dalam pelaksanaan tridarma.',
            ],

            // E4.1 - Sistem Penjaminan Mutu Internal
            [
                'id_elemen' => $elemenMap['E4.1'] ?? null,
                'code' => 'P4.1.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul secara terpadu dan koheren harus memiliki dokumen sistem penjaminan mutu internal (SPMI) yang sahih dan dapat dipertanggungjawabkan. Dokumen SPMI ini harus mencakup: Penetapan Standar Mutu yang melampaui Standar Nasional Pendidikan Tinggi (SN Dikti); Peraturan, prosedur, dan manual yang jelas untuk pelaksanaan siklus Penjaminan Mutu Internal (PPEPP); dan Struktur organisasi, tugas, dan tanggung jawab unit atau tim penjaminan mutu yang fungsional dan terintegrasi dengan tata kelola institusi. Sistem Penjaminan Mutu Internal harus menjamin bahwa seluruh proses manajemen, akademik, dan non-akademik di lingkup Institusi, Unit Pengelola Program Studi, dan Program Studi dilaksanakan sesuai dengan standar yang ditetapkan. Selain itu, sistem harus dapat menghasilkan dokumen dan data yang transparan, terukur, dan akuntabel, sehingga dapat digunakan sebagai basis untuk perbaikan berkelanjutan.',
            ],

            // E4.2 - Implementasi Perbaikan Berkelanjutan
            [
                'id_elemen' => $elemenMap['E4.2'] ?? null,
                'code' => 'P4.2.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul secara terpadu dan koheren harus membuktikan bahwa sistem penjaminan mutu internal dijalankan dalam siklus yang konsisten dan berkelanjutan, tersedianya organisasi dan aktor yang melaksanakan, mendokumentasikan, serta menerapkan siklus Penetapan, Pelaksanaan, Evaluasi, Pengendalian, dan Peningkatan (PPEPP) secara konsisten. Audit Mutu Internal harus dilaksanakan secara rutin sebagai mekanisme objektif untuk menilai efektivitas pelaksanaan PPEPP dan memastikan tindak lanjut atas temuan ketidaksesuaian terhadap standar. Pengusul wajib menunjukkan adanya temuan, tindak lanjut, serta bukti pengukuran dan evaluasi yang ditindaklanjuti secara berkelanjutan. Mekanisme ini harus dapat menunjukkan adanya peningkatan standar mutu menuju budaya mutu yang diharapkan.',
            ],

            // E4.3 - Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal
            [
                'id_elemen' => $elemenMap['E4.3'] ?? null,
                'code' => 'P4.3.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul harus menunjukkan keterlibatan pemangku kepentingan yang relevan dalam sistem penjaminan mutu yang dituangkan dalam kebijakan dan peraturan yang berlaku. Keterlibatan ini ditandai dengan keberadaan dewan akademik atau forum sejenis yang melibatkan unsur eksternal, termasuk organisasi profesi, pengguna lulusan, alumni, dan mahasiswa, dalam memberikan evaluasi dan masukan terhadap proses pembelajaran. Institusi dan/atau Unit Pengelola Program Studi harus menunjukkan upaya penjaminan mutu untuk aspek nonakademik misalnya dengan melibatkan lembaga atau ahli eksternal yang relevan seperti akuntan publik untuk audit keuangan, lembaga audit energi, audit data dan sistem informasi, audit K3, maupun bentuk penjaminan mutu eksternal lain yang mendukung budaya mutu secara menyeluruh.',
            ],

            // E5.1 - Sarana dan Prasarana Belajar
            [
                'id_elemen' => $elemenMap['E5.1'] ?? null,
                'code' => 'P5.1.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi pengusul harus menjabarkan standar ketersediaan sarana dan prasarana belajar yang memadai untuk mendukung seluruh kegiatan pembelajaran. Standar ini harus mencakup kuantitas, kualitas, aksesibilitas, dan keberlanjutan prasarana fisik (seperti ruang kuliah, studio, laboratorium, workshop, dan perpustakaan), serta sarana pendukung (seperti perabot, peralatan, dan teknologi pembelajaran). Unit Pengelola Program Studi harus menunjukkan bahwa sarana dan prasarana belajar dikelola, dipelihara, dan diperbarui secara berkala. Institusi dan unit pengelola juga wajib memastikan bahwa semua sarana dan prasarana belajar mematuhi standar keselamatan, kesehatan, dan kelestarian lingkungan, serta mendukung inklusivitas bagi mahasiswa berkebutuhan khusus. Selain itu, prasarana fisik harus didukung oleh prasarana non-fisik (seperti sistem informasi akademik dan repositori digital) yang memadai dan andal.',
            ],

            // E5.2 - Sumber Pengetahuan
            [
                'id_elemen' => $elemenMap['E5.2'] ?? null,
                'code' => 'P5.2.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi pengusul harus memiliki kebijakan yang jelas dan terpadu untuk memastikan ketersediaan dan aksesibilitas sumber pengetahuan dan informasi ilmiah yang relevan dan mutakhir bagi seluruh sivitas akademika. Sumber pengetahuan ini mencakup koleksi cetak dan digital (buku, jurnal, prosiding, basis data, perangkat lunak), serta repositori karya ilmiah yang dikelola secara profesional. Unit Pengelola Program Studi harus menjamin bahwa sistem pengelolaan sumber pengetahuan (perpustakaan, repositori) memiliki layanan yang memadai, dikelola secara berkelanjutan, serta memfasilitasi kebutuhan riset dan pembelajaran. Unit pengelola wajib menunjukkan bukti adanya investasi yang berkelanjutan untuk pembaruan dan pengembangan koleksi sumber pengetahuan. Selain itu, harus tersedia sistem yang memadai untuk melindungi hak kekayaan intelektual atas karya-karya yang dihasilkan oleh sivitas akademika.',
            ],

            // E5.3 - Kepuasan Mahasiswa dan Alumni
            [
                'id_elemen' => $elemenMap['E5.3'] ?? null,
                'code' => 'P5.3.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, atau Program Studi Pengusul harus memiliki sistem yang andal untuk mengidentifikasi dan mengukur tingkat kepuasan mahasiswa dan alumni terhadap beragam layanan yang mereka terima, baik akademik maupun nonakademik. Termasuk dalam hal ini adalah adanya kepuasan terhadap upaya tindak lanjut terkait K3L, transparansi informasi, tindak kekerasan dan indisipliner lainnya. Hasil pengukuran kepuasan harus dianalisis secara sistematis, didokumentasikan, dan dijadikan dasar dalam pengambilan keputusan strategis. Institusi dan program studi juga harus menunjukkan bukti bahwa hasil evaluasi kepuasan mahasiswa dan alumni telah menghasilkan kebijakan dan tindakan nyata yang berdampak pada peningkatan kualitas layanan serta perbaikan berkelanjutan.',
            ],

            // E5.4 - Lulusan, Kajian Telusur, dan Kepuasan Pengguna
            [
                'id_elemen' => $elemenMap['E5.4'] ?? null,
                'code' => 'P5.4.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul harus memiliki mekanisme terpadu untuk melaksanakan survei dan kajian penelusuran alumni serta kepuasan pengguna lulusan. Mekanisme ini harus mampu mengidentifikasi relevansi kompetensi lulusan, lama waktu memperoleh pekerjaan, tingkat penghasilan awal, serta kesesuaian kompetensi dengan harapan pengguna lulusan. Seluruh proses penelusuran harus didokumentasikan secara runtut dan hasilnya dianalisis secara mendalam untuk menjadi dasar perumusan kebijakan, program tindak lanjut, serta upaya peningkatan mutu secara berkelanjutan. Dengan demikian, hasil kajian telusur tidak hanya menjadi data administratif, tetapi juga instrumen strategis untuk menjamin relevansi lulusan dengan kebutuhan masyarakat, industri, dan profesi.',
            ],

            // E6.1 - Organisasi dan Tata Kelola
            [
                'id_elemen' => $elemenMap['E6.1'] ?? null,
                'code' => 'P6.1.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul harus memiliki struktur organisasi dan tata kelola yang sah secara hukum serta sesuai dengan peraturan perundangan yang berlaku, baik yang ditetapkan pemerintah, yayasan, statuta, maupun peraturan internal lainnya. Tata kelola harus mencerminkan pembagian kewenangan dan tugas yang jelas, efektif, dan akuntabel, sehingga mendukung pengambilan keputusan yang tepat dan pelaksanaan tridarma yang terintegrasi. Unit Pengelola Program Studi harus menunjukkan bahwa mekanisme tata kelola menghindari konflik kepentingan, memiliki prosedur baku operasional yang terdokumentasi, serta mampu mengelola seluruh proses tridarma secara tertib. Selain itu, tata kelola juga harus mendorong implementasi prinsip manajemen dan kepemimpinan yang efektif, termasuk melalui kebijakan rekrutasi, penghargaan, dan sanksi yang adil agar tercipta iklim organisasi yang sehat dan berorientasi pada pengembangan berkelanjutan.',
            ],

            // E6.2 - Kerja Sama dan Kemitraan
            [
                'id_elemen' => $elemenMap['E6.2'] ?? null,
                'code' => 'P6.2.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul harus memiliki sistem dan tata kelola yang efektif dalam mengelola kerja sama internal dalam perguruan tinggi, eksternal, serta membangun kemitraan strategis, mendokumentasikannya dengan baik, dan menunjukkan manfaat perbaikan berkelanjutan. Institusi, Unit Pengelola Program Studi, dan Program Studi harus menunjukkan bukti pelaksanaan baik berupa kerja sama (dapat berupa kegiatan pendidikan, penelitian, pengabdian masyarakat, maupun kelembagaan yang bersifat jangka pendek) ataupun kemitraan (kolaborasi yang lebih permanen, jangka panjang, dan berbasis kesetaraan, seperti program gelar ganda, proyek, atau riset bersama multi tahun).',
            ],

            // E6.3 - Sistem dan Manajemen Informasi
            [
                'id_elemen' => $elemenMap['E6.3'] ?? null,
                'code' => 'P6.3.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul harus mempunyai sistem atau tata kelola untuk memastikan sistem dan manajemen informasi serta data dukung tridarma (mahasiswa, dosen, dan tenaga kependidikan) dikelola dengan baik dan mudah diakses, termasuk untuk transparansi kepada publik.',
            ],

            // E6.4 - Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan
            [
                'id_elemen' => $elemenMap['E6.4'] ?? null,
                'code' => 'P6.4.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul harus mempunyai sistem atau tata kelola untuk memastikan keselamatan, kesehatan, dan keamanan kerja semua aktor utama dalam pelaksanaan tridarma (mahasiswa, dosen, dan tenaga kependidikan) serta melaksanakan upaya nyata berkontribusi terhadap kelestarian lingkungan.',
            ],

            // E6.5 - Keuangan, Keberlanjutan, dan Mitigasi Risiko
            [
                'id_elemen' => $elemenMap['E6.5'] ?? null,
                'code' => 'P6.5.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi Pengusul harus mempunyai sistem atau tata kelola untuk memastikan kecukupan dan keberlanjutan keuangan, termasuk pembiayaan untuk pelaksanaan tridarma (mahasiswa, dosen, dan tenaga kependidikan), serta merumuskan sistem mitigasi risiko yang terukur.',
            ],

            // E7.1 - Kebijakan Penelitian
            [
                'id_elemen' => $elemenMap['E7.1'] ?? null,
                'code' => 'P7.1.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi harus memiliki kebijakan penelitian tertulis dan terpadu yang selaras dengan visi dan misi, serta mendukung terciptanya suasana ilmiah sehingga kegiatan penelitian dapat berlangsung secara berkelanjutan. Kebijakan mencakup pengelolaan penelitian, alokasi pendanaan internal, strategi kemitraan untuk memperoleh pendanaan eksternal, mekanisme pemantauan dan evaluasi, serta layanan hilirisasi dan transfer teknologi. Kebijakan penelitian juga menegaskan kontribusi penelitian dalam pengembangan ilmu pengetahuan, penguatan institusi, peningkatan kompetensi mahasiswa, dan relevansi terhadap karier dosen maupun tenaga kependidikan. Secara eksplisit, kebijakan penelitian memuat peran dan keterlibatan mahasiswa sebagai bagian dari pencapaian pembelajaran mereka, baik melalui tugas akhir, publikasi, maupun kegiatan ilmiah lainnya.',
            ],

            // E7.2 - Proses Penelitian
            [
                'id_elemen' => $elemenMap['E7.2'] ?? null,
                'code' => 'P7.2.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi harus membangun mekanisme perekaman penelitian yang dilakukan oleh dosen, mahasiswa, dan tenaga kependidikan fungsional secara terpadu. Mekanisme tersebut mencakup penegakan etika penelitian, proses seleksi proposal penelitian, pelaksanaan penelitian sesuai standar mutu, evaluasi berkala terhadap kemajuan dan hasil penelitian, serta diseminasi atau repositori hasil penelitian yang dapat diakses secara terbuka. Proses penelitian dirancang untuk menjamin integritas akademik, kualitas keluaran penelitian, dan kontribusi nyata bagi pengembangan ilmu pengetahuan, pembelajaran, serta pemecahan masalah di masyarakat.',
            ],

            // E7.3 - Luaran dan Dampak Penelitian
            [
                'id_elemen' => $elemenMap['E7.3'] ?? null,
                'code' => 'P7.3.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi harus memiliki sistem perekaman luaran penelitian yang dilakukan oleh dosen, mahasiswa, dan tenaga kependidikan fungsional secara terpadu. Rekaman tersebut mencakup jenis luaran, kualitas, relevansi, serta jejak dampak yang dihasilkan. Rekaman luaran penelitian harus menampilkan bukti nyata dampak dalam berbagai dimensi, antara lain: dampak akademik (sitasi, pengembangan teori, inovasi pembelajaran, kontribusi kurikulum), dampak profesional (peningkatan kompetensi, reputasi, dan praktik profesi), dampak kebijakan (kontribusi terhadap regulasi, standar, atau tata kelola publik), serta dampak sosial-ekonomi (hilirisasi, komersialisasi, pemberdayaan, dan peningkatan kesejahteraan masyarakat). Institusi dan Unit Pengelola Program Studi mencatat bentuk layanan lanjut penelitian, seperti hilirisasi, inkubasi, kerjasama pemanfaatan hasil, dan perlindungan Hak Kekayaan Intelektual, termasuk penghargaan atas sitasi, paten, maupun implementasi teknologi serta membangun skema penghargaan atas capaian di atas. Program Studi harus mendokumentasikan bukti kontribusi penelitian terhadap pengembangan ilmu, profesi, kebijakan publik, sektor pendidikan, dan masyarakat luas, dengan menunjukkan keterlibatan dosen dan mahasiswa, serta unsur profesi eksternal dalam menghasilkan dampak tersebut.',
            ],

            // E7.4 - Kebijakan Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['E7.4'] ?? null,
                'code' => 'P7.4.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi harus memiliki kebijakan terpadu mengenai pengabdian kepada masyarakat yang mendukung terciptanya suasana ilmiah dan keberlangsungan kegiatan pengabdian. Kebijakan tersebut mencakup tata kelola pengelolaan pengabdian, alokasi pendanaan internal, strategi kemitraan untuk memperoleh pendanaan eksternal, mekanisme pemantauan dan evaluasi kegiatan, serta penyediaan layanan untuk menciptakan dampak yang berkesinambungan bagi masyarakat. Kebijakan pengabdian juga harus berorientasi pada integrasi pendidikan dan kebutuhan nyata masyarakat sehingga berkontribusi pada peningkatan kualitas hidup, pemberdayaan, kelestarian lingkungan, dan keberlanjutan sosial-ekonomi.',
            ],

            // E7.5 - Proses Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['E7.5'] ?? null,
                'code' => 'P7.5.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi harus memiliki sistem rekaman proses pengabdian kepada masyarakat yang dilakukan oleh dosen, mahasiswa, dan tenaga kependidikan fungsional secara terpadu. Rekaman tersebut mencakup tahapan seleksi, perencanaan, pelaksanaan, hingga evaluasi pengabdian masyarakat, dengan menjunjung tinggi etika pendampingan dan intervensi. Rekaman proses harus menunjukkan keterlibatan aktif mahasiswa sehingga kegiatan pengabdian menjadi bagian dari pencapaian pembelajaran. Proses ini disarankan berbasis pada pemanfaatan kompetensi mahasiswa, keahlian dosen, serta kontribusi tenaga kependidikan fungsional dalam mendukung implementasi visi dan misi program studi dan institusi.',
            ],

            // E7.6 - Luaran dan Dampak Pengabdian kepada Masyarakat
            [
                'id_elemen' => $elemenMap['E7.6'] ?? null,
                'code' => 'P7.6.1',
                'pernyataan' => 'Institusi, Unit Pengelola Program Studi, dan Program Studi harus memiliki sistem perekaman luaran pengabdian kepada masyarakat yang dilakukan oleh dosen, mahasiswa, dan tenaga kependidikan fungsional secara terpadu. Rekaman tersebut mencakup jenis kegiatan, kualitas, relevansi, jejak dampak yang dihasilkan, serta skema penghargaan atas capaian tersebut. Bukti rekaman dapat ditunjukkan melalui laporan kegiatan, karya atau inovasi yang diaplikasikan di masyarakat, publikasi atau liputan media, maupun testimoni dari masyarakat penerima manfaat. Luaran pengabdian disarankan dihasilkan melalui kolaborasi antara dosen, tenaga kependidikan, mahasiswa, dan unsur profesi eksternal, untuk memperkuat keterkaitan antara pendidikan dan dampak nyata di masyarakat. Institusi dan Unit Pengelola Program Studi harus memiliki rekaman dampak yang memperlihatkan kontribusi dalam berbagai dimensi, antara lain: dampak akademik (pengayaan kurikulum, publikasi, inovasi pembelajaran), dampak profesional (peningkatan kompetensi, reputasi, dan praktik profesi), dampak kebijakan (kontribusi terhadap regulasi, standar, atau tata kelola publik), serta dampak sosial-ekonomi-lingkungan (hilirisasi, pemberdayaan, peningkatan kesejahteraan, dan kelestarian lingkungan). Program Studi harus mendokumentasikan rekaman pemanfaatan luaran tersebut dalam pembelajaran mahasiswa, khususnya yang dapat menunjukkan prestasi atau penghargaan di tingkat regional, nasional, maupun internasional.',
            ],
        ];

        foreach ($pernyataan as $item) {
            if ($item['id_elemen'] !== null) {
                Pernyataan::updateOrCreate(
                    ['code' => $item['code']], // Check by code
                    $item // Update or create with this data
                );
            }
        }
    }
}
