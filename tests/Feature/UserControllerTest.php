<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate --seed');
    }

    public function test_index(): void
    {
        $response = $this->get('/api/users');

        $response->assertStatus(200);
    }

    public function test_store(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);

        $response = $this->post(
            '/api/user', 
            [
                'name' => 'test_user',
                'password' => 'test_password',
            ],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas(
            'users', 
            [
                'name' => 'test_user',
            ]
        );
    }
    
    public function test_store_failed(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);

        $response = $this->post(
            '/api/user', 
            [
                'name' => 'test_user',
            ],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'password' => [
                    'The password field is required.'
                ]
            ]
        ]);
    }

    public function test_destroy(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);

        $this->assertDatabaseHas(
            'users', 
            [
                'id' => 2,
            ]
        );

        $response = $this->delete(
            '/api/user/2', 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully deleted user!'
        ]);
        $this->assertDatabaseMissing(
            'users', 
            [
                'id' => 2,
            ]
        );
    }

    public function test_destroy_failed(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);

        $response = $this->delete(
            '/api/user/1', 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'You are not authorized to delete this user!'
        ]);
    }
}
