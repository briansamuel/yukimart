<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->testUser = User::factory()->create([
            'email' => 'test@yukimart.com',
            'password' => Hash::make('password123'),
            'username' => 'testuser',
            'full_name' => 'Test User',
            'phone' => '0123456789',
        ]);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@yukimart.com',
            'password' => 'password123',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'username',
                        'email',
                        'full_name',
                        'phone',
                        'is_active',
                    ],
                    'expires_at',
                ],
                'meta' => [
                    'timestamp',
                    'version',
                    'request_id',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->testUser->id,
            'name' => 'Test Device',
        ]);

        // Store response for Postman collection
        $this->storeApiResponse('auth_login_success', $response->json());
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@yukimart.com',
            'password' => 'wrongpassword',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);

        $this->storeApiResponse('auth_login_failure', $response->json());
    }

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@yukimart.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'full_name' => 'New User',
            'phone' => '0987654321',
            'address' => '123 Test Street',
            'birth_date' => '1990-01-01',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'username',
                        'email',
                        'full_name',
                        'phone',
                    ],
                    'token',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@yukimart.com',
            'username' => 'newuser',
        ]);

        $this->storeApiResponse('auth_register_success', $response->json());
    }

    /** @test */
    public function user_cannot_register_with_existing_email()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'username' => 'anotheruser',
            'email' => 'test@yukimart.com', // Already exists
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'full_name' => 'Another User',
            'phone' => '0987654321',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->storeApiResponse('auth_register_email_exists', $response->json());
    }

    /** @test */
    public function authenticated_user_can_get_profile()
    {
        Sanctum::actingAs($this->testUser);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'username',
                    'email',
                    'full_name',
                    'phone',
                    'address',
                    'birth_date',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'test@yukimart.com',
                    'username' => 'testuser',
                ],
            ]);

        $this->storeApiResponse('auth_me_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_update_profile()
    {
        Sanctum::actingAs($this->testUser);

        $updateData = [
            'full_name' => 'Updated Test User',
            'phone' => '0111222333',
            'address' => '456 Updated Street',
        ];

        $response = $this->putJson('/api/v1/auth/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'full_name' => 'Updated Test User',
                    'phone' => '0111222333',
                    'address' => '456 Updated Street',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->testUser->id,
            'full_name' => 'Updated Test User',
            'phone' => '0111222333',
        ]);

        $this->storeApiResponse('auth_update_profile_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_change_password()
    {
        Sanctum::actingAs($this->testUser);

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        // Verify new password works
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@yukimart.com',
            'password' => 'newpassword123',
            'device_name' => 'Test Device',
        ]);

        $loginResponse->assertStatus(200);

        $this->storeApiResponse('auth_change_password_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        Sanctum::actingAs($this->testUser);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        $this->storeApiResponse('auth_logout_success', $response->json());
    }

    /** @test */
    public function user_can_request_password_reset()
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@yukimart.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset link sent to your email',
            ]);

        $this->storeApiResponse('auth_forgot_password_success', $response->json());
    }

    /** @test */
    public function authenticated_user_can_refresh_token()
    {
        Sanctum::actingAs($this->testUser);

        $response = $this->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'expires_at',
                ],
                'meta'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Token refreshed successfully',
            ]);

        $this->storeApiResponse('auth_refresh_success', $response->json());
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $protectedRoutes = [
            ['GET', '/api/v1/auth/me'],
            ['PUT', '/api/v1/auth/profile'],
            ['POST', '/api/v1/auth/change-password'],
            ['POST', '/api/v1/auth/logout'],
            ['POST', '/api/v1/auth/refresh'],
        ];

        foreach ($protectedRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            $response->assertStatus(401);
        }

        $this->storeApiResponse('auth_unauthenticated_error', [
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * Store API response for Postman collection update
     */
    protected function storeApiResponse(string $key, array $response): void
    {
        $responsesFile = storage_path('testing/api_responses.json');
        
        if (!file_exists(dirname($responsesFile))) {
            mkdir(dirname($responsesFile), 0755, true);
        }

        $responses = [];
        if (file_exists($responsesFile)) {
            $responses = json_decode(file_get_contents($responsesFile), true) ?? [];
        }

        $responses[$key] = [
            'response' => $response,
            'timestamp' => now()->toISOString(),
            'test_class' => static::class,
        ];

        file_put_contents($responsesFile, json_encode($responses, JSON_PRETTY_PRINT));
    }
}
