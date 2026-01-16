<?php

namespace Database\Seeders;

use App\Models\DatasetSuplemen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatasetSuplemenSeeder extends Seeder
{
    private int $urutan = 0;

    /**
     * DatasetSuplemenSeeder - Updated for correct degree levels
     *
     * Degree Levels:
     * - d1, d2, d3 (Diploma I, II, III)
     * - d4 (Diploma IV / Sarjana Terapan)
     * - s1 (Sarjana)
     * - s2 (Magister)
     * - s2-terapan (Magister Terapan)
     * - s3 (Doktor)
     * - s3-terapan (Doktor Terapan)
     * - profesi (Pendidikan Profesi)
     * - spesialis (Pendidikan Spesialis)
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('dataset_suplemen')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed all degree levels
        $this->seedDiploma123();
        $this->seedDiploma4();
        $this->seedSarjana();
        $this->seedProfesi();
        $this->seedSpesialis();
        $this->seedMagister();
        $this->seedMagisterTerapan();
        $this->seedDoktor();
        $this->seedDoktorTerapan();
    }

    // ========================================
    // DIPLOMA 1, 2, 3
    // ========================================
    private function seedDiploma123()
    {
        foreach (['d1', 'd2', 'd3'] as $code) {
            $this->urutan = 0;

            // Header
            $this->addItem(
                $code,
                'header',
                'list_item',
                0,
                'Suplemen Program Studi Diploma Satu, Diploma Dua, Diploma Tiga',
                ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
            );

            // Bagian A Common
            $this->addBagianACommon($code);

            // Pemastian CPL
            $this->addItem(
                $code,
                'pemastian_cpl',
                'list_item',
                1,
                'Pemastian Capaian Pembelajaran Lulusan',
                ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
            );

            // Pemenuhan Beban Belajar
            $this->addItem(
                $code,
                'pemenuhan_beban_belajar',
                'list_item',
                2,
                'Pemenuhan Beban Belajar',
                ['size' => 11, 'spaceAfter' => 40]
            );
            $this->addItem(
                $code,
                'pemenuhan_beban_belajar',
                'paragraph',
                null,
                'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
                ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
            );

            // Magang
            $this->addItem(
                $code,
                'magang',
                'list_item',
                2,
                'Magang',
                ['size' => 11, 'spaceAfter' => 40]
            );
            $this->addItem(
                $code,
                'magang',
                'paragraph',
                null,
                'Kegiatan magang di dunia usaha, dunia industri, dan dunia kerja yang relevan pada program Diploma Satu, Diploma Dua, dan Diploma Tiga (Durasi dan Beban belajar).',
                ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
            );

            // Penilaian Hasil Belajar
            $this->addPenilaianHasilBelajar($code, 'untuk program Diploma Tiga');
        }
    }

    // ========================================
    // DIPLOMA 4 (Sarjana Terapan)
    // ========================================
    private function seedDiploma4()
    {
        $code = 'd4';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Diploma Empat/Sarjana Terapan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'magang_kerja_praktek',
            'list_item',
            2,
            'Magang dan/atau Kerja Praktek, Studio dan/atau Praktikum, Kuliah Kerja Nyata/KKN, Kuliah Kerja Lapangan (KKL)',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'magang_kerja_praktek',
            'bullet',
            null,
            'Kegiatan Magang dan/atau Kerja Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'magang_kerja_praktek',
            'bullet',
            null,
            'Kegiatan Studio dan/atau Praktikum yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'magang_kerja_praktek',
            'bullet',
            null,
            'Kegiatan Kuliah Kerja Nyata/KKN yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'magang_kerja_praktek',
            'bullet',
            null,
            'Kegiatan Kuliah Kerja Lapangan/KKL yang relevan pada program Sarjana Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk Diploma Empat/Sarjana Terapan');
    }

    // ========================================
    // SARJANA (S1)
    // ========================================
    private function seedSarjana()
    {
        $code = 's1';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Sarjana',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'studio_praktikum',
            'list_item',
            2,
            'Studio, Praktikum, Magang, Kuliah Kerja Nyata/KKN, Kuliah Kerja Lapangan (KKL)',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'studio_praktikum',
            'bullet',
            null,
            'Kegiatan Studio dan/atau Praktikum yang relevan pada program Sarjana (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_praktikum',
            'bullet',
            null,
            'Kegiatan Magang dan/atau Kerja Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program Sarjana (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_praktikum',
            'bullet',
            null,
            'Kegiatan Kuliah Kerja Nyata/KKN yang relevan pada program Sarjana (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_praktikum',
            'bullet',
            null,
            'Kegiatan Kuliah Kerja Lapangan/KKL yang relevan pada program Sarjana (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'skripsi',
            'list_item',
            2,
            'Skripsi, prototipe, proyek, tugas akhir, kurikulum berbasis proyek',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'skripsi',
            'bullet',
            null,
            'Bentuk skripsi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis baik secara individu maupun berkelompok.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'skripsi',
            'bullet',
            null,
            'Penerapan kurikulum berbasis proyek atau bentuk pembelajaran lainnya yang sejenis dan asesmen yang dapat menunjukkan kompetensi lulusan.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk Sarjana');
    }

    // ========================================
    // PROFESI
    // ========================================
    private function seedProfesi()
    {
        $code = 'profesi';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Profesi',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'praktek_studio',
            'list_item',
            2,
            'Praktek, Studio',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'praktek_studio',
            'bullet',
            null,
            'Kegiatan Praktek profesi di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program profesi (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'praktek_studio',
            'bullet',
            null,
            'Kegiatan Studio yang mendukung dan/atau relevan dengan kegiatan praktek profesi pada program Profesi (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'tugas_akhir',
            'list_item',
            2,
            'Tugas Akhir',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'tugas_akhir',
            'bullet',
            null,
            'Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, yang relevan pada program Profesi.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk Profesi');
    }

    // ========================================
    // SPESIALIS (NEW)
    // ========================================
    private function seedSpesialis()
    {
        $code = 'spesialis';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Spesialis',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'praktek_klinik',
            'list_item',
            2,
            'Praktek Klinik/Lapangan, Penelitian',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'praktek_klinik',
            'bullet',
            null,
            'Kegiatan Praktek klinik atau praktek lapangan yang relevan pada program Spesialis (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'praktek_klinik',
            'bullet',
            null,
            'Kegiatan Penelitian yang mendukung dan/atau relevan dengan program Spesialis (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'tugas_akhir',
            'list_item',
            2,
            'Tugas Akhir',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'tugas_akhir',
            'bullet',
            null,
            'Tugas akhir dalam bentuk tesis, karya tulis ilmiah, atau bentuk tugas akhir lainnya yang sejenis untuk program Spesialis.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk program Spesialis', 'tesis');
    }

    // ========================================
    // MAGISTER (S2)
    // ========================================
    private function seedMagister()
    {
        $code = 's2';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Magister',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'studio_penelitian',
            'list_item',
            2,
            'Studio dan/atau Praktikum, Penelitian, Perancangan',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'studio_penelitian',
            'bullet',
            null,
            'Kegiatan Studio dan/atau Praktikum yang relevan pada program Magister (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_penelitian',
            'bullet',
            null,
            'Kegiatan Penelitian yang relevan pada Program Magister (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_penelitian',
            'bullet',
            null,
            'Kegiatan Perancangan yang relevan pada program Magister (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'tugas_akhir',
            'list_item',
            2,
            'Tugas Akhir',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'tugas_akhir',
            'bullet',
            null,
            'Tugas akhir dalam bentuk tesis, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk program Magister', 'tesis');
    }

    // ========================================
    // MAGISTER TERAPAN (S2 Terapan)
    // ========================================
    private function seedMagisterTerapan()
    {
        $code = 's2-terapan';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Magister Terapan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'studio_perancangan',
            'list_item',
            2,
            'Studio dan/atau Praktikum, Perancangan, Praktek',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'studio_perancangan',
            'bullet',
            null,
            'Kegiatan Studio dan/atau Praktikum yang relevan pada program Magister Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_perancangan',
            'bullet',
            null,
            'Kegiatan Perancangan yang pada program Magister Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_perancangan',
            'bullet',
            null,
            'Kegiatan Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program profesi (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'tugas_akhir',
            'list_item',
            2,
            'Tugas Akhir',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'tugas_akhir',
            'bullet',
            null,
            'Tugas akhir dalam bentuk tesis, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk program Magister Terapan', 'tesis');
    }

    // ========================================
    // DOKTOR (S3)
    // ========================================
    private function seedDoktor()
    {
        $code = 's3';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Doktor',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'penelitian_perancangan',
            'list_item',
            2,
            'Penelitian, Perancangan',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'penelitian_perancangan',
            'bullet',
            null,
            'Kegiatan Penelitian relevan pada program Doktor (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'penelitian_perancangan',
            'bullet',
            null,
            'Kegiatan Perancangan yang relevan pada program Doktor (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'tugas_akhir',
            'list_item',
            2,
            'Tugas Akhir',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'tugas_akhir',
            'bullet',
            null,
            'Tugas akhir dalam bentuk disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk program Doktor', 'disertasi');
    }

    // ========================================
    // DOKTOR TERAPAN (S3 Terapan)
    // ========================================
    private function seedDoktorTerapan()
    {
        $code = 's3-terapan';
        $this->urutan = 0;

        $this->addItem(
            $code,
            'header',
            'list_item',
            0,
            'Suplemen Program Studi Doktor Terapan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 120]
        );

        $this->addBagianACommon($code);

        $this->addItem(
            $code,
            'pemastian_cpl',
            'list_item',
            1,
            'Pemastian Capaian Pembelajaran Lulusan',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'list_item',
            2,
            'Pemenuhan Beban Belajar',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'pemenuhan_beban_belajar',
            'paragraph',
            null,
            'Kuliah, responsi, tutorial, seminar, praktikum, praktik, studio, penelitian, perancangan, pengembangan, tugas akhir, pelatihan bela negara, pertukaran pelajar, magang, wirausaha, pengabdian kepada masyarakat, dan/atau bentuk pembelajaran lain.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 120]
        );

        $this->addItem(
            $code,
            'studio_perancangan',
            'list_item',
            2,
            'Studio dan/atau Praktikum, Perancangan, Praktek',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'studio_perancangan',
            'bullet',
            null,
            'Kegiatan Studio dan/atau Praktikum yang relevan pada program Doktor Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_perancangan',
            'bullet',
            null,
            'Kegiatan Perancangan yang pada program Doktor Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );
        $this->addItem(
            $code,
            'studio_perancangan',
            'bullet',
            null,
            'Kegiatan Praktek di dunia usaha, dunia industri, atau dunia kerja yang relevan pada program Doktor Terapan (Durasi dan Beban belajar).',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'tugas_akhir',
            'list_item',
            2,
            'Tugas Akhir',
            ['size' => 11, 'spaceAfter' => 40]
        );
        $this->addItem(
            $code,
            'tugas_akhir',
            'bullet',
            null,
            'Tugas akhir dalam bentuk Disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis.',
            ['size' => 11, 'indentation' => 1080, 'spaceAfter' => 80]
        );

        $this->addPenilaianHasilBelajar($code, 'untuk program Doktor Terapan', 'Disertasi');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Add common Bagian A content (CPL, Susunan Materi, Beban Belajar, Rencana Pembelajaran)
     */
    private function addBagianACommon(string $code)
    {
        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            1,
            'Deskripsi Capaian Pembelajaran Lulusan, Susunan Materi Pembelajaran, Beban Belajar, Rencana Pembelajaran',
            ['size' => 11, 'bold' => true, 'spaceAfter' => 80]
        );

        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            2,
            'Capaian Pembelajaran Lulusan (CPL)',
            ['size' => 11]
        );

        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            2,
            'Susunan Materi Pembelajaran Untuk Mencapai CPL',
            ['size' => 11]
        );

        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            3,
            'Matakuliah',
            ['size' => 11]
        );
        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            3,
            'Modul',
            ['size' => 11]
        );
        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            3,
            'Blok tematik; dan/atau',
            ['size' => 11]
        );
        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            3,
            'Bentuk lain',
            ['size' => 11]
        );

        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            2,
            'Beban Belajar dan Masa Tempuh',
            ['size' => 11]
        );

        $this->addItem(
            $code,
            'bagian_a',
            'list_item',
            2,
            'Rencana Pembelajaran',
            ['size' => 11]
        );
    }

    /**
     * Add Penilaian Hasil Belajar section
     */
    private function addPenilaianHasilBelajar(string $code, string $programSuffix, string $tugas_akhir_type = 'prototipe')
    {
        $this->addItem(
            $code,
            'penilaian_hasil_belajar',
            'list_item',
            2,
            'Penilaian Hasil Belajar',
            ['size' => 11, 'spaceAfter' => 60]
        );

        $this->addItem(
            $code,
            'penilaian_hasil_belajar',
            'list_item',
            3,
            'Test',
            ['size' => 11, 'spaceAfter' => 40]
        );

        $this->addItem(
            $code,
            'penilaian_hasil_belajar',
            'list_item',
            3,
            'Non Test',
            ['size' => 11, 'spaceAfter' => 40]
        );

        $this->addItem(
            $code,
            'penilaian_hasil_belajar',
            'list_item',
            3,
            'Observasi',
            ['size' => 11, 'spaceAfter' => 40]
        );

        if ($tugas_akhir_type === 'tesis') {
            $text = "Tugas akhir dalam bentuk tesis, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis {$programSuffix}";
        } elseif ($tugas_akhir_type === 'disertasi') {
            $text = "Tugas akhir dalam bentuk disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis {$programSuffix}";
        } elseif ($tugas_akhir_type === 'Disertasi') {
            $text = "Tugas akhir dalam bentuk Disertasi, prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis {$programSuffix}";
        } else {
            $text = "Tugas akhir dalam bentuk prototipe, proyek, atau bentuk tugas akhir lainnya yang sejenis, baik secara individu maupun berkelompok, {$programSuffix}";
        }

        $this->addItem(
            $code,
            'penilaian_hasil_belajar',
            'list_item',
            3,
            $text,
            ['size' => 11, 'spaceAfter' => 40]
        );
    }

    /**
     * Add a suplemen item to database
     */
    private function addItem(
        string $degreeCode,
        string $sectionKey,
        string $contentType,
        ?int $numberingLevel,
        string $textContent,
        array $formatting = []
    ) {
        DatasetSuplemen::create([
            'degree_level_code' => $degreeCode,
            'section_key' => $sectionKey,
            'content_type' => $contentType,
            'numbering_level' => $numberingLevel,
            'text_content' => $textContent,
            'formatting' => $formatting,
            'urutan' => ++$this->urutan,
        ]);
    }
}
