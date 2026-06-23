<?php

namespace Tests\Unit\Services;

use App\Mail\PenawaranAsesmenMail;
use App\Services\MailDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailDeliveryServiceTest extends TestCase
{
    private MailDeliveryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MailDeliveryService();
    }

    // ─────────────────────────────────────────────────────────────
    // uniqueEmails — pure function
    // ─────────────────────────────────────────────────────────────

    public function test_uniqueEmails_removes_duplicates(): void
    {
        $result = $this->service->uniqueEmails([
            'user@example.com',
            'user@example.com',
            'other@example.com',
        ]);

        $this->assertCount(2, $result);
        $this->assertContains('user@example.com', $result);
        $this->assertContains('other@example.com', $result);
    }

    public function test_uniqueEmails_lowercases_all(): void
    {
        $result = $this->service->uniqueEmails(['USER@EXAMPLE.COM', 'Test@Example.com']);

        $this->assertContains('user@example.com', $result);
        $this->assertContains('test@example.com', $result);
    }

    public function test_uniqueEmails_trims_whitespace(): void
    {
        $result = $this->service->uniqueEmails(['  user@example.com  ', ' other@example.com']);

        $this->assertContains('user@example.com', $result);
        $this->assertContains('other@example.com', $result);
    }

    public function test_uniqueEmails_returns_empty_array_for_empty_input(): void
    {
        $result = $this->service->uniqueEmails([]);
        $this->assertEmpty($result);
    }

    public function test_uniqueEmails_drops_empty_strings(): void
    {
        $result = $this->service->uniqueEmails(['', '  ', 'valid@example.com']);

        $this->assertCount(1, $result);
        $this->assertContains('valid@example.com', $result);
    }

    public function test_uniqueEmails_case_insensitive_dedup(): void
    {
        $result = $this->service->uniqueEmails(['User@Example.COM', 'user@example.com']);
        $this->assertCount(1, $result);
    }

    // ─────────────────────────────────────────────────────────────
    // sendToEmails — Mail::fake()
    // ─────────────────────────────────────────────────────────────

    public function test_sendToEmails_returns_skipped_when_no_recipients(): void
    {
        Mail::fake();

        $mailable = new class extends Mailable {
            public function build() { return $this->text('emails.test'); }
        };

        $result = $this->service->sendToEmails([], $mailable);

        $this->assertSame([], $result['sent_to']);
        $this->assertSame(1, $result['skipped']);
        Mail::assertNothingSent();
    }

    public function test_sendToEmails_sends_to_provided_emails(): void
    {
        Mail::fake();

        $mailable = new class extends Mailable {
            public function build() { return $this->subject('Test')->html('<p>test</p>'); }
        };

        $result = $this->service->sendToEmails(
            ['remahankecil@gmail.com'],
            $mailable
        );

        $this->assertSame(['remahankecil@gmail.com'], $result['sent_to']);
        $this->assertSame(0, $result['skipped']);
        Mail::assertSentCount(1);
    }

    public function test_sendToEmails_deduplicates_recipients(): void
    {
        Mail::fake();

        $mailable = new class extends Mailable {
            public function build() { return $this->subject('Dedup Test')->html('<p>test</p>'); }
        };

        $result = $this->service->sendToEmails(
            ['Remahankecil@Gmail.com', 'remahankecil@gmail.com'],
            $mailable
        );

        // After dedup: only 1 unique email
        $this->assertCount(1, $result['sent_to']);
        Mail::assertSentCount(1);
    }

    public function test_sendToEmails_returns_sent_to_list(): void
    {
        Mail::fake();

        $mailable = new class extends Mailable {
            public function build() { return $this->subject('Multi')->html('<p>test</p>'); }
        };

        $emails  = ['remahankecil@gmail.com', 'other@example.com'];
        $result  = $this->service->sendToEmails($emails, $mailable);

        $this->assertSame(0, $result['skipped']);
        $this->assertCount(2, $result['sent_to']);
    }

    public function test_sendToEmails_supports_cc_and_bcc(): void
    {
        Mail::fake();

        $mailable = new class extends Mailable {
            public function build() { return $this->subject('CC test')->html('<p>test</p>'); }
        };

        $result = $this->service->sendToEmails(
            ['remahankecil@gmail.com'],
            $mailable,
            cc: ['cc@example.com'],
            bcc: ['bcc@example.com'],
        );

        $this->assertSame(0, $result['skipped']);
        Mail::assertSentCount(1);
    }

    public function test_sendToEmails_with_empty_string_recipients_counts_as_empty(): void
    {
        Mail::fake();

        $mailable = new class extends Mailable {
            public function build() { return $this->subject('Empty')->html('<p>test</p>'); }
        };

        $result = $this->service->sendToEmails(['', '  '], $mailable);

        $this->assertSame(1, $result['skipped']);
        Mail::assertNothingSent();
    }
}
