<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/ConfirmPassword'));
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/confirm-password', ['password' => 'password']);

        $response
            ->assertRedirect('/dashboard')
            ->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->from('/confirm-password')
            ->post('/confirm-password', ['password' => 'wrong-password'])
            ->assertRedirect('/confirm-password')
            ->assertSessionHasErrors('password');
    }
}
