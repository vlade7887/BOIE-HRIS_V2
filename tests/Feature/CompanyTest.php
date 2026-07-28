<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/companies', [
            'company_code' => 'ABC-001',
            'company_name' => 'Boie Solutions',
            'contact_person' => 'John Doe',
            'contact_number' => '09171234567',
            'email' => 'info@boie.com',
            'address' => 'Cebu City',
            'remarks' => 'Primary account',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('companies.index'));
        $this->assertDatabaseHas('companies', [
            'company_code' => 'ABC-001',
            'company_name' => 'Boie Solutions',
        ]);
    }
}
