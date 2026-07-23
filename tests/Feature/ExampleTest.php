<?php

namespace Tests\Feature;

use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed([
            RolesAndPermissionsSeeder::class,
            DummyDataSeeder::class,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
