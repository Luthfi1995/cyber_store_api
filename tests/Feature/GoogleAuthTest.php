<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_requires_id_token()
    {
        $response = $this->postJson('/api/v1/auth/google', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_token']);
    }

    public function test_google_login_registers_new_user_and_creates_cart_with_mock_token()
    {
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'mock-google-token',
            'email' => 'newuser@ubsistore.test',
            'name' => 'New User',
            'google_id' => 'google-id-12345'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'google_id',
                    'role'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@ubsistore.test',
            'google_id' => 'google-id-12345',
            'role' => User::ROLE_CUSTOMER
        ]);

        $user = User::where('email', 'newuser@ubsistore.test')->first();
        $this->assertNotNull($user);

        // Check if cart was created
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id
        ]);
    }

    public function test_google_login_authenticates_existing_user_by_google_id()
    {
        $user = User::factory()->create([
            'email' => 'existing@ubsistore.test',
            'google_id' => 'google-id-55555',
            'role' => User::ROLE_CUSTOMER
        ]);

        // Create a cart for them
        Cart::create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'mock-google-token',
            'email' => 'existing@ubsistore.test',
            'name' => 'Existing User',
            'google_id' => 'google-id-55555'
        ]);

        $response->assertStatus(200);
        $this->assertEquals($user->id, $response->json('user.id'));
    }

    public function test_google_login_links_existing_user_by_email()
    {
        $user = User::factory()->create([
            'email' => 'link@ubsistore.test',
            'google_id' => null,
            'role' => User::ROLE_CUSTOMER
        ]);

        // Create a cart for them
        Cart::create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'mock-google-token',
            'email' => 'link@ubsistore.test',
            'name' => 'Link User',
            'google_id' => 'new-linked-google-id'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'new-linked-google-id'
        ]);
    }
}
