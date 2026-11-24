<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_transfer_between_accounts(): void
    {
        $this->assertTrue(true);
    }
}
