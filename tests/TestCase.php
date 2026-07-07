<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // [TEST] Nonaktifkan enkripsi payload API saat testing
        // agar assertJson* dan assertJsonValidationErrors bekerja normal
        config(['app.api_encryption_key' => null]);
    }
}
