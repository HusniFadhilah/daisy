# Audit Gap BAN-PT/PDDikti

- PDDikti public API yang tersedia saat audit berada dalam mode limited/timeout, sehingga verifikasi massal PDDikti belum bisa diselesaikan otomatis.
- Audit ini memakai daftar selisih dari `260526 - Database Prodi.xlsx` vs `data_akreditasi_lengkap.csv`, lalu mencari ulang kecocokan di BAN-PT Bianglala.
- Jika sebuah gap ditemukan di BAN-PT, besar kemungkinan datanya juga perlu dicek ulang di PDDikti saat endpoint PDDikti kembali stabil.

## Ringkasan Gap

- Kombinasi prodi/jenjang yang kurang: 52
- Total kekurangan berdasarkan detail cakupan: 97
- Temuan BAN-PT kandidat: 141

## Extra Di Database Prodi Namun Bukan Cakupan

| Sheet | Prodi | Jumlah |
| --- | --- | --- |
| Desain | Pendidikan Vokasional Desain Fashion | 5 |

## Temuan BAN-PT Untuk Gap

### Arsitektur - Arsitektur - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Pertanian Bogor | A | 2013-11-21 | Tidak | 54211 - S1 Arsitektur Lansekap |
| Universitas Ichsan Gorontalo | Baik | 2027-10-25 | Ya | 23201 - S1 Arsitektur |
| Universitas Komputer Indonesia | Baik Sekali | 2030-10-28 | Ya | 23201 - S1 Arsitektur |
| Universitas Tribhuwana Tungga Dewi | Unggul | 2030-04-29 | Ya | 54211 - S1 Arsitektur Lansekap |

### Arsitektur - Arsitektur - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Pertanian Bogor | Unggul | 2029-01-17 | Ya | 54109 - S2 Arsitektur Lansekap |
| Sekolah Tinggi Teknik Malang | B | 2020-01-09 | Tidak | 23101 - S2 Arsitektur |
| Universitas Brawijaya | Unggul | 2028-03-28 | Ya | 23101 - S2 Arsitektur Lingkungan Binaan |
| Universitas Kebangsaan Republik Indonesia | Terakreditasi Pertama | 2027-12-10 | Ya | 23101 - S2 Arsitektur |

### Arsitektur - Pendidikan Profesi Arsitek - Profesi

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Teknologi Sepuluh November | Baik | 2030-04-14 | Ya | 23901 - Profesi Pendidikan Profesi Arsitek |
| Universitas Atma Jaya Yogyakarta | Terakreditasi Sementara | 2029-09-03 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Bina Nusantara | Terakreditasi Sementara | 2030-08-25 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Brawijaya | Baik | 2030-02-11 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Diponegoro | Terakreditasi Sementara | 2030-08-25 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Gadjah Mada | Baik Sekali | 2029-02-01 | Ya | 23901 - Profesi Pendidikan Profesi Arsitek |
| Universitas Hasanuddin | Baik | 2030-08-06 | Ya | 23901 - Profesi Pendidikan Profesi Arsitek |
| Universitas Islam Negeri Alauddin Makassar | Terakreditasi Pertama | 2028-03-31 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Katolik Parahyangan | Terakreditasi Sementara | 2029-06-06 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Mercu Buana | Terakreditasi Sementara | 2030-05-14 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Muhammadiyah Surakarta | Terakreditasi Pertama | 2028-01-26 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Multimedia Nusantara Jakarta | Baik | 2030-10-22 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Pancasila | Terakreditasi Pertama | 2028-05-25 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Pembangunan Jaya | Terakreditasi Pertama | 2028-03-31 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Tadulako | Terakreditasi Pertama | 2028-04-21 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Tanjungpura | Terakreditasi Sementara | 2030-08-25 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Tarumanagara | Terakreditasi Sementara | 2030-10-21 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Udayana | Terakreditasi Sementara | 2029-06-11 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |
| Universitas Warmadewa | Terakreditasi Sementara | 2030-04-14 | Ya | 23906 - Profesi Pendidikan Profesi Arsitek |

### Arsitektur - Profesi Arsitek - Profesi

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Teknologi Bandung | Terakreditasi Sementara | 2030-09-16 | Ya | 23901 - Profesi Profesi Arsitek |
| Universitas Indonesia | Baik | 2031-04-21 | Ya | 23901 - Profesi Profesi Arsitek |
| Universitas Islam Indonesia | Baik Sekali | 2031-03-17 | Ya | 23901 - Profesi Profesi Arsitek |
| Universitas Sebelas Maret | Terakreditasi Sementara | 2030-03-25 | Ya | 23901 - Profesi Profesi Arsitek |
| Universitas Sumatera Utara | B | 2027-12-06 | Ya | 23901 - Profesi Profesi Arsitek |
| Universitas Syiah Kuala | Terakreditasi Sementara | 2029-01-23 | Ya | 23901 - Profesi Profesi Arsitek |

### Arsitektur - Teknik Arsitektur - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Sains Dan Teknologi Nasional | Baik Sekali | 2030-03-19 | Ya | 23201 - S1 Teknik Arsitektur |
| Institut Sains Dan Teknologi Td Pardede | B | 2026-10-21 | Ya | 23201 - S1 Teknik Arsitektur |
| Institut Teknologi Adhi Tama Surabaya | Baik Sekali | 2031-03-17 | Ya | 23201 - S1 Teknik Arsitektur |
| Institut Teknologi Budi Utomo | Baik | 2028-07-25 | Ya | 23201 - S1 Teknik Arsitektur |
| Institut Teknologi Nasional Malang | Baik Sekali | 2028-05-31 | Ya | 23201 - S1 Teknik Arsitektur |
| Sekolah Tinggi Teknik Malang | Baik | 2028-11-14 | Ya | 23201 - S1 Teknik Arsitektur |
| Sekolah Tinggi Teknologi Cirebon | Baik | 2028-07-11 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Borobudur | Baik Sekali | 2031-03-10 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Brawijaya | Unggul | 2030-03-16 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Budi Luhur | Baik Sekali | 2026-04-29 | Tidak | 23201 - S1 Teknik Arsitektur |
| Universitas Bung Karno | B | 2028-04-04 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Dwijendra | Baik Sekali | 2028-01-04 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Gunadarma | Unggul | 2026-06-18 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Jayabaya | B | 9999-01-01 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Katolik Musi Charitas | Baik | 2029-04-16 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Kristen Indonesia | Unggul | 2026-02-05 | Tidak | 23201 - S1 Teknik Arsitektur |
| Universitas Kristen Petra | Unggul | 2030-09-05 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Lancang Kuning | B | 2026-12-28 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Langlang Buana | Baik Sekali | 2031-03-31 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Madako Tolitoli | Baik | 2030-07-22 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Medan Area | Baik Sekali | 2030-05-06 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Mercu Buana | Baik Sekali | 2029-06-25 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Merdeka Surabaya | Baik | 2026-04-29 | Tidak | 23201 - S1 Teknik Arsitektur |
| Universitas Mpu Tantular | B | 9999-01-01 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Muhammadiyah Jakarta | Baik Sekali | 2029-10-19 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Muhammadiyah Kendari | Baik | 2030-10-06 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Muhammadiyah Surabaya | Baik Sekali | 2029-03-13 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Muhammadiyah Surakarta | Unggul | 2026-09-30 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Muslim Indonesia | Baik | 2028-09-14 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Ngurah Rai | Baik Sekali | 2031-03-17 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Pendidikan Indonesia | Unggul | 2031-02-24 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Riau Kepulauan | Baik | 2028-10-03 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Sains Alqur An | A | 2029-04-24 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Sains Dan Teknologi Jayapura | Baik Sekali | 2027-06-21 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Sriwijaya | A | 2023-09-25 | Tidak | 23201 - S1 Teknik Arsitektur |
| Universitas Syiah Kuala | Baik Sekali | 2029-07-30 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Tadulako | Baik Sekali | 2028-06-27 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Tanjungpura | Baik Sekali | 2027-12-28 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Tompotika Luwuk Banggai | Baik | 2027-02-22 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Widya Kartika | B | 2029-08-21 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Wijayakusuma Purwokerto | Baik | 2030-10-07 | Ya | 23201 - S1 Teknik Arsitektur |
| Universitas Winaya Mukti | Baik | 9999-01-01 | Ya | 23201 - S1 Teknik Arsitektur |

### Desain - Desain - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Teknologi Sepuluh Nopember | Baik | 2029-10-19 | Ya | 90021 - S2 Desain Interior |
| Universitas Trisakti | A | 2028-05-03 | Ya | 90131 - S2 Desain Produk |

### Desain - Desain Interaktif - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Bunda Mulia | Baik | 2025-10-17 | Tidak | 90249 - S1 Desain Interaktif |

### Desain - Desain Interior - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Teknologi Sains Bandung | Baik | 2023-09-12 | Tidak | 90221 - S1 Desain Interior |
| Universitas Presiden | Baik | 2031-03-31 | Ya | 90221 - S1 Desain Interior |

### Desain - Desain Mode - D4

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Seni Indonesia Surakarta | Baik Sekali | 0000-00-00 | Ya | 90212 - D-IV Desain Mode Batik |
| Institut Seni Indonesia Yogyakarta | Baik | 2031-03-03 | Ya | 90331 - D-IV Desain Mode Kriya Batik |

### Desain - Desain Mode Batik - D4

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Seni Indonesia Surakarta | Baik Sekali | 0000-00-00 | Ya | 90212 - D-IV Desain Mode Batik |

### Desain - Desain Produk - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Seni Indonesia Surakarta | Terakreditasi Sementara | 2030-09-09 | Ya | 90233 - S1 Desain Produk Industri |
| Institut Teknologi Nasional Bandung | Unggul | 2026-11-11 | Ya | 90231 - S1 Desain Produk |
| Universitas Dinamika | Baik Sekali | 2025-11-24 | Tidak | 90242 - S1 Desain Produk |
| Universitas Pendidikan Indonesia | Baik | 2029-01-30 | Ya | 90231 - S1 Desain Produk Industri Kampus Tasikmalaya |

### Desain - Multimedia - D3

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Politeknik Bali Maha Werdhi | Baik | 2026-08-31 | Ya | 90448 - D-III Multimedia |

### Desain - Teknologi Industri Cetak Kemasan - D4

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Politeknik Negeri Jakarta | Unggul | 2028-04-20 | Ya | 90341 - D-IV Teknologi Industri Cetak Kemasan |

### Desain - Teknologi Rekayasa Cetak dan Grafis 3 Dimensi - D4

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Politeknik Negeri Jakarta | Terakreditasi Unggul | 2028-12-20 | Ya | 90344 - D-IV Teknologi Rekayasa Cetak Dan Grafis 3 Dimensi |

### Lingkungan - Geografi Lingkungan - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Gadjah Mada | Unggul | 2029-05-26 | Ya | 25201 - S1 Geografi Lingkungan |

### Lingkungan - Ilmu Lingkungan - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Nusa Cendana | B | 2025-07-21 | Tidak | 95101 - S2 Ilmu Lingkungan |
| Universitas Papua | Baik | 2031-01-27 | Ya | 95129 - S2 Ilmu Lingkungan |

### Lingkungan - Kajian Lingkungan dan Pembangunan - S3

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Negeri Padang | B | 9999-01-01 | Ya | 60003 - S3 Kajian Lingkungan Dan Pembangunan |

### Lingkungan - Kependudukan dan Lingkungan Hidup - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Negeri Gorontalo | B | 2026-10-07 | Ya | 95102 - S2 Kependudukan Dan Lingkungan Hidup |

### Lingkungan - Manajemen Sumber Daya Hayati - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Padjadjaran | B | 2025-09-01 | Tidak | 46105 - S2 Manajemen Sumber Daya Hayati |

### Lingkungan - Pendidikan Kependudukan dan Lingkungan Hidup - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Sebelas Maret | A | 2023-06-06 | Tidak | 95102 - S2 Pendidikan Kependudukan Dan Lingkungan Hidup |

### Lingkungan - Pengelolaan Lingkungan - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Hasanuddin | Unggul | 2029-05-29 | Ya | 95129 - S2 Pengelolaan Lingkungan Hidup |

### Lingkungan - Pengelolaan Sumberdaya Alam - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Al-muslim | Baik Sekali | 2030-12-16 | Ya | 95101 - S2 Pengelolaan Sumberdaya Alam Dan Lingkungan |
| Universitas Brawijaya | Unggul | 2027-12-28 | Ya | 95101 - S2 Pengelolaan Sumberdaya Alam Dan Lingkungan |
| Universitas Cenderawasih | B | 2027-05-24 | Ya | 95101 - S2 Pengelolaan Sumberdaya Alam Dan Lingkungan |
| Universitas Lambung Mangkurat | Baik Sekali | 2031-05-12 | Ya | 95101 - S2 Pengelolaan Sumberdaya Alam & Lingkungan |
| Universitas Mataram | Baik | 2027-06-04 | Ya | 95119 - S2 Pengelolaan Sumberdaya Alam Dan Lingkungan |
| Universitas Palangka Raya | Baik Sekali | 2027-11-15 | Ya | 95101 - S2 Pengelolaan Sumberdaya Alam & Lingkungan |
| Universitas Panca Bhakti | Terakreditasi Sementara | 2029-10-03 | Ya | 95119 - S2 Pengelolaan Sumberdaya Alam Dan Lingkungan |
| Universitas Serambi Mekkah | Terakreditasi Sementara | 2030-01-14 | Ya | 95119 - S2 Pengelolaan Sumberdaya Alam Dan Lingkungan |

### Lingkungan - Pengelolaan Sumberdaya Alam - S3

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Sumatera Utara | Baik Sekali | 2026-04-27 | Tidak | 95001 - S3 Pengelolaan Sumberdaya Alam Dan Lingkungan |

### Lingkungan - Teknik dan Manajemen Lingkungan - D3

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Pertanian Bogor | Baik | 2026-03-02 | Tidak | 54457 - D-III Teknik Dan Manajemen Lingkungan |

### Lingkungan - Teknik dan Manajemen Lingkungan - D4

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Pertanian Bogor | Unggul | 2030-08-20 | Ya | 13351 - D-IV Teknik Dan Manajemen Lingkungan |

### Perencanaan - Kajian Pembangunan Perkotaan dan Wilayah - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Krisnadwipayana | Baik Sekali | 2029-11-19 | Ya | 95125 - S2 Kajian Pembangunan Perkotaan Dan Wilayah |

### Perencanaan - Pembangunan Wilayah - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Gadjah Mada | Unggul | 2029-10-01 | Ya | 95203 - S1 Pembangunan Wilayah |

### Perencanaan - Pembangunan Wilayah dan Pedesaan - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Pertanian Bogor | Unggul | 2027-08-16 | Ya | 95103 - S2 Pembangunan Wilayah Dan Pedesaan |
| Universitas Tadulako | B | 2027-06-07 | Ya | 95103 - S2 Pembangunan Wilayah Dan Pedesaan |

### Perencanaan - Perencanaan Pembangunan - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Mahasaraswati Denpasar | A | 2025-09-29 | Tidak | 35101 - S2 Perencanaan Pembangunan Wilayah Dan Pengelolaan Lingkungan |
| Universitas Sumatera Utara | Unggul | 2029-05-01 | Ya | 95103 - S2 Perencanaan Pembangunan Wilayah Dan Pedesaan |

### Perencanaan - Perencanaan Pembangunan Wilayah dan Pengelolaan Lingkungan - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Universitas Mahasaraswati Denpasar | A | 2025-09-29 | Tidak | 35101 - S2 Perencanaan Pembangunan Wilayah Dan Pengelolaan Lingkungan |

### Perencanaan - Perencanaan Wilayah - S2

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Pertanian Bogor | Unggul | 2028-05-09 | Ya | 95105 - S2 Perencanaan Wilayah |
| Institut Teknologi Bandung | Unggul | 2031-04-23 | Ya | 35101 - S2 Perencanaan Wilayah Dan Kota |
| Institut Teknologi Sepuluh Nopember | Baik Sekali | 2031-04-09 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Bosowa | Baik Sekali | 2028-06-07 | Ya | 35101 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Brawijaya | Unggul | 2027-10-11 | Ya | 95125 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Cenderawasih | Baik | 2027-07-27 | Ya | 95125 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Diponegoro | Unggul | 2027-05-31 | Ya | 95103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Gadjah Mada | Unggul | 2026-08-18 | Ya | 95103 - S2 Magister Perencanaan Wilayah Dan Kota |
| Universitas Hasanuddin | Baik | 2027-11-30 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Indonesia | Baik Sekali | 2028-12-05 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Islam Bandung | Baik Sekali | 2029-07-02 | Ya | 35101 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Islam Sultan Agung | Terakreditasi Sementara | 2030-05-14 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Lampung | Baik Sekali | 2031-03-03 | Ya | 95103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Mahasaraswati Denpasar | Unggul | 2030-09-30 | Ya | 95114 - S2 Perencanaan Wilayah Dan Pedesaan |
| Universitas Pakuan | Baik | 2026-08-24 | Ya | 95125 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Palangka Raya | Baik | 9999-01-01 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Pembangunan Panca Budi | Baik Sekali | 2030-08-26 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Riau | Baik | 2026-07-05 | Ya | 95103 - S2 Perencanaan Wilayah Dan Perdesaan |
| Universitas Sebelas Maret | Terakreditasi Sementara | 2029-06-06 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Simalungun | B | 9999-01-01 | Ya | 35101 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Sintuwu Maroso Poso | Baik | 2027-04-29 | Ya | 95114 - S2 Perencanaan Wilayah Dan Perdesaan |
| Universitas Sumatera Utara | Terakreditasi Sementara | 2030-09-16 | Ya | 35103 - S2 Perencanaan Wilayah Dan Kota |
| Universitas Tarumanagara | Baik Sekali | 2030-09-05 | Ya | 35101 - S2 Perencanaan Wilayah Dan Kota |

### Perencanaan - Perencanaan Wilayah dan Kota - S1

| Universitas | Peringkat | Kedaluwarsa | Aktif | BAN-PT PS |
| --- | --- | --- | --- | --- |
| Institut Teknologi Sepuluh Nopember | Unggul | 2030-11-02 | Ya | 35201 - S1 Perencanaan Wilayah Dan Kota |
| Universitas Komputer Indonesia | Baik Sekali | 2031-04-09 | Ya | 35201 - S1 Perencanaan Wilayah Dan Kota |

## Defisit Detail

| Sheet | Prodi | Jenjang | Kurang | Temuan BAN-PT |
| --- | --- | --- | ---: | ---: |
| Arsitektur | Arsitektur | S1 | 1 | 4 |
| Arsitektur | Arsitektur | S2 | 1 | 4 |
| Arsitektur | Pendidikan Profesi Arsitek | Profesi | 18 | 19 |
| Arsitektur | Pertamanan | S1 | 1 | 0 |
| Arsitektur | Profesi Arsitek | Profesi | 5 | 6 |
| Arsitektur | Teknik Arsitektur | S1 | 1 | 42 |
| Desain | Desain | S2 | 1 | 2 |
| Desain | Desain Digital | D2 | 1 | 0 |
| Desain | Desain Interaktif | S1 | 1 | 1 |
| Desain | Desain Interior | S1 | 1 | 2 |
| Desain | Desain Mode | D4 | 2 | 2 |
| Desain | Desain Mode Batik | D4 | 1 | 1 |
| Desain | Desain Produk | S1 | 1 | 4 |
| Desain | Desain Produk Kulit, Karet, dan Plastik | D4 | 1 | 0 |
| Desain | Ergonomi Fisiologi Kerja | S2 | 1 | 0 |
| Desain | Multimedia | D3 | 1 | 1 |
| Desain | Produksi Garmen | D4 | 1 | 0 |
| Desain | Teknologi Industri Cetak Kemasan | D4 | 1 | 1 |
| Desain | Teknologi Rekayasa Cetak dan Grafis 3 Dimensi | D4 | 1 | 1 |
| Lingkungan | Geografi Lingkungan | S1 | 1 | 1 |
| Lingkungan | Ilmu Lingkungan | S1 | 2 | 0 |
| Lingkungan | Ilmu Lingkungan | S2 | 1 | 2 |
| Lingkungan | Kajian Lingkungan dan Pembangunan | S3 | 1 | 1 |
| Lingkungan | Kependudukan dan Lingkungan Hidup | S2 | 1 | 1 |
| Lingkungan | Manajemen Lingkungan | S2 | 4 | 0 |
| Lingkungan | Manajemen Sumber Daya Hayati | S2 | 1 | 1 |
| Lingkungan | Manajemen Sumberdaya Lahan | S1 | 1 | 0 |
| Lingkungan | Pendidikan Kependudukan dan Lingkungan Hidup | S2 | 1 | 1 |
| Lingkungan | Pendidikan Kependudukan dan Lingkungan Hidup | S3 | 1 | 0 |
| Lingkungan | Pendidikan Lingkungan | S2 | 1 | 0 |
| Lingkungan | Pengelolaan dan Pemberdayaan Sumberdaya Alam dan Lingkungan | S2 | 1 | 0 |
| Lingkungan | Pengelolaan Lingkungan | S2 | 2 | 1 |
| Lingkungan | Pengelolaan Lingkungan dan Pembangunan | S2 | 1 | 0 |
| Lingkungan | Pengelolaan Sumberdaya Alam | S2 | 5 | 8 |
| Lingkungan | Pengelolaan Sumberdaya Alam | S3 | 2 | 1 |
| Lingkungan | Sains Keberlanjutan | S2 | 1 | 0 |
| Lingkungan | Sains Lingkungan | S1 | 2 | 0 |
| Lingkungan | Sains Lingkungan Kelautan | S1 | 1 | 0 |
| Lingkungan | Studi Lingkungan | S2 | 3 | 0 |
| Lingkungan | Studi Lingkungan dan Perkotaan | S2 | 1 | 0 |
| Lingkungan | Teknik dan Manajemen Lingkungan | D3 | 1 | 1 |
| Lingkungan | Teknik dan Manajemen Lingkungan | D4 | 1 | 1 |
| Lingkungan | Teknik dan Pengelolaan Sumber Daya Air | S1 | 1 | 0 |
| Perencanaan | Kajian Pembangunan Perkotaan dan Wilayah | S2 | 2 | 1 |
| Perencanaan | Kajian Pengembangan Perkotaan | S2 | 1 | 0 |
| Perencanaan | Pembangunan Wilayah | S1 | 1 | 1 |
| Perencanaan | Pembangunan Wilayah dan Pedesaan | S2 | 2 | 2 |
| Perencanaan | Perencanaan dan Pengembangan Pariwisata | S2 | 7 | 0 |
| Perencanaan | Perencanaan Pembangunan | S2 | 2 | 2 |
| Perencanaan | Perencanaan Pembangunan Wilayah dan Pengelolaan Lingkungan | S2 | 1 | 1 |
| Perencanaan | Perencanaan Wilayah | S2 | 1 | 23 |
| Perencanaan | Perencanaan Wilayah dan Kota | S1 | 2 | 2 |
