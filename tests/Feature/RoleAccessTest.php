<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_open_admin_dashboard(): void
    {
        $customer = $this->userWithRole('customer');

        $this->actingAs($customer)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_seller_cannot_open_admin_dashboard(): void
    {
        $seller = $this->userWithRole('seller');

        $this->actingAs($seller)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_approved_seller_can_open_seller_dashboard(): void
    {
        $seller = $this->userWithRole('seller');
        Seller::query()->create([
            'user_id' => $seller->id,
            'store_name' => 'Onaylı Mağaza',
            'slug' => 'onayli-magaza',
            'tax_number' => '1234567890',
            'iban' => 'TR120006200000000000000001',
            'status' => 'onayli',
        ]);

        $this->actingAs($seller)
            ->get('/satici')
            ->assertOk();
    }

    public function test_pending_seller_cannot_open_seller_dashboard(): void
    {
        $seller = $this->userWithRole('seller');
        Seller::query()->create([
            'user_id' => $seller->id,
            'store_name' => 'Bekleyen Mağaza',
            'slug' => 'bekleyen-magaza',
            'tax_number' => '1234567890',
            'iban' => 'TR120006200000000000000001',
            'status' => 'beklemede',
        ]);

        $this->actingAs($seller)
            ->get('/satici')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('login'));
    }

    public function test_unverified_user_is_redirected_to_email_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_legacy_seller_role_is_migrated_to_the_canonical_role(): void
    {
        $legacyRole = Role::findOrCreate('satici', 'web');
        $seller = User::factory()->create();
        $seller->assignRole($legacyRole);

        $this->seed(RoleSeeder::class);

        $this->assertTrue($seller->fresh()->hasRole('seller'));
        $this->assertDatabaseMissing('roles', ['name' => 'satici', 'guard_name' => 'web']);
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
