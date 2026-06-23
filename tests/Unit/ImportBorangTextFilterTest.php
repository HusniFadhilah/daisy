<?php
// tests/Unit/ImportBorangTextFilterTest.php

namespace Tests\Unit;

use Tests\TestCase;

class ImportBorangTextFilterTest extends TestCase
{
    /** @test */
    public function it_keeps_real_content()
    {
        $job = new \App\Jobs\ImportBorangDocxJob(1, 1, 'dummy.docx');

        $realContent = [
            'Berikut merupakan deskripsinya dari D1',
            'Program studi ini memiliki legalitas yang lengkap.',
            'Struktur organisasi terdiri dari ketua, sekretaris, dan bendahara.',
        ];

        foreach ($realContent as $text) {
            $isPlaceholder = $this->callPrivateMethod($job, 'isPlaceholderText', [$text]);
            $this->assertFalse($isPlaceholder, "Should keep: {$text}");
        }
    }

    /** @test */
    public function it_skips_placeholders()
    {
        $job = new \App\Jobs\ImportBorangDocxJob(1, 1, 'dummy.docx');

        $placeholders = [
            'Deskripsi legalitas program dan tata pamong.',
            'Tabel D.1.a Dokumen Legalitas Program Studi',
            '[mohon isi sesuai dengan kondisi]',
            'deskripsi',
            'no',
        ];

        foreach ($placeholders as $text) {
            $isPlaceholder = $this->callPrivateMethod($job, 'isPlaceholderText', [$text]);
            $this->assertTrue($isPlaceholder, "Should skip: {$text}");
        }
    }

    private function callPrivateMethod($object, $method, $params = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $params);
    }
}
