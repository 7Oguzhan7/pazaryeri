<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_pending_seller_application(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)->post(route('seller.application.store'), [
            'store_name' => 'Yeni Mağaza',
            'tax_number' => '1234567890',
            'iban' => 'tr47 0000 0000 0000 0000 0000 00',
        ])->assertRedirect(route('seller.application.create'));

        $this->assertDatabaseHas('sellers', [
            'user_id' => $customer->id,
            'store_name' => 'Yeni Mağaza',
            'slug' => 'yeni-magaza',
            'tax_number' => '1234567890',
            'iban' => 'TR470000000000000000000000',
            'status' => Seller::STATUS_PENDING,
        ]);
        $this->assertTrue($customer->fresh()->hasRole('customer'));

        $this->actingAs($customer)->get(route('seller.application.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('application.status', Seller::STATUS_PENDING)
                ->where('application.storeName', 'Yeni Mağaza'));
    }

    public function test_seller_application_rejects_an_invalid_tax_number_and_iban(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)->from(route('seller.application.create'))
            ->post(route('seller.application.store'), [
                'store_name' => 'Geçersiz Mağaza',
                'tax_number' => '123',
                'iban' => 'TR000000000000000000000000',
            ])
            ->assertRedirect(route('seller.application.create'))
            ->assertSessionHasErrors([
                'tax_number' => 'Vergi numarası 10 veya 11 rakamdan oluşmalıdır.',
                'iban' => 'IBAN doğrulama kontrolünden geçemedi.',
            ]);

        $this->assertDatabaseCount('sellers', 0);
    }

    public function test_admin_approval_grants_seller_role_and_enables_seller_panel(): void
    {
        $customer = $this->createCustomer();
        $application = $this->createSellerApplication($customer);
        $admin = $this->createAdmin();

        $this->actingAs($admin)->patch(route('admin.seller-applications.approve', $application))
            ->assertRedirect(route('admin.seller-applications.index'));

        $this->assertDatabaseHas('sellers', [
            'id' => $application->id,
            'status' => Seller::STATUS_APPROVED,
        ]);
        $this->assertTrue($customer->fresh()->hasRole('seller'));
        $this->assertFalse($customer->fresh()->hasRole('customer'));
        $this->actingAs($customer)->get(route('seller.dashboard'))->assertOk();
    }

    public function test_non_admin_cannot_review_seller_applications(): void
    {
        $customer = $this->createCustomer();
        $application = $this->createSellerApplication($customer);
        $anotherCustomer = $this->createCustomer('another-customer@example.test');

        $this->actingAs($anotherCustomer)
            ->patch(route('admin.seller-applications.approve', $application))
            ->assertForbidden();

        $this->assertDatabaseHas('sellers', [
            'id' => $application->id,
            'status' => Seller::STATUS_PENDING,
        ]);
    }

    public function test_rejected_application_can_be_resubmitted_for_review(): void
    {
        $customer = $this->createCustomer();
        $application = $this->createSellerApplication($customer);
        $admin = $this->createAdmin();
        $this->actingAs($admin)->patch(route('admin.seller-applications.reject', $application))
            ->assertRedirect(route('admin.seller-applications.index'));

        $this->actingAs($customer)->post(route('seller.application.store'), [
            'store_name' => 'Düzeltilmiş Mağaza',
            'tax_number' => '0987654321',
            'iban' => 'TR470000000000000000000000',
        ])->assertRedirect(route('seller.application.create'));

        $this->assertDatabaseHas('sellers', [
            'id' => $application->id,
            'store_name' => 'Düzeltilmiş Mağaza',
            'status' => Seller::STATUS_PENDING,
        ]);
        $this->assertDatabaseCount('sellers', 1);
    }

    private function createCustomer(string $email = 'customer@example.test'): User
    {
        $customer = User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
        ]);
        $customer->assignRole(Role::findOrCreate('customer', 'web'));

        return $customer;
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        return $admin;
    }

    private function createSellerApplication(User $user): Seller
    {
        return Seller::query()->create([
            'user_id' => $user->id,
            'store_name' => 'Test Başvuru',
            'slug' => 'test-basvuru',
            'tax_number' => '1234567890',
            'iban' => 'TR470000000000000000000000',
            'status' => Seller::STATUS_PENDING,
        ]);
    }
}
