<?php

namespace App\Mail;

use App\Models\PengajuanAkreditasi;
use App\Models\ReviewKesiapan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewKesiapanNotifikasi extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $review;

    public function __construct(PengajuanAkreditasi $pengajuan, ReviewKesiapan $review)
    {
        $this->pengajuan = $pengajuan;
        $this->review = $review;
    }

    public function build()
    {
        $subject = $this->review->hasil_review === 'siap'
            ? 'Review Kesiapan: SIAP Lanjut ke Tahap AK'
            : 'Review Kesiapan: Borang Perlu Perbaikan';

        return $this->subject($subject)
            ->view('emails.review-kesiapan-notifikasi');
    }
}
