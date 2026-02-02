<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_can_be_rendered()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'test_admin_' . time() . '@example.com',
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Total Monthly Rent');
        $response->assertSee('Revenue Trend');
        $response->assertSee('Recent Invoices');
        
        // Clean up
        $user->delete();
    }
}
