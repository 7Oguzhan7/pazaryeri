<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_week_one_demo_data_matches_the_acceptance_criteria(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 26);
        $this->assertDatabaseCount('sellers', 5);
        $this->assertDatabaseCount('addresses', 20);
        $this->assertDatabaseCount('categories', 15);
        $this->assertDatabaseCount('products', 100);
    }
}
