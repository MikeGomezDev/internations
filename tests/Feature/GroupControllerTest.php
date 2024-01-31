<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate --seed');
    }

    public function test_index(): void
    {
        $response = $this->get('/api/groups');

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
            '/api/group', 
            [
                'name' => 'Group 1',
            ],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas(
            'groups', 
            [
                'name' => 'Group 1',
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
            '/api/group', 
            [
                'name' => '',
            ],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'name' => [
                    'The name field is required.'
                ]
            ]
        ]);
    }

    //Create test for addUser and removeUser
    public function test_add_user(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);
        
        $response = $this->post(
            '/api/group/2/addUser/2', 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully added user to group!'
        ]);
        $this->assertDatabaseHas(
            'group_user', 
            [
                'user_id' => 2,
                'group_id' => 2,
            ]
        );
    }

    /**
     * @dataProvider data_provider_test_add_user_failed
     */
    public function test_add_user_failed($group_id, $user_id, $expected_message, $error_code): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);
        
        $response = $this->post(
            '/api/group/' . $group_id . '/addUser/' . $user_id, 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );
        
        $response->assertStatus($error_code);
        $response->assertJson([
            'message' => $expected_message,
        ]);
    }

    //add data provider for add_user_failed
    public static function data_provider_test_add_user_failed(): array
    {
        return [
            'user already in group' => [
                'group_id' => 1,
                'user_id' => 2,
                'expected_message' => 'User already in group!',
                'error_code' => 400,
            ],
            'group not found' => [
                'group_id' => 999,
                'user_id' => 1,
                'expected_message' => 'Group or user not found!',
                'error_code' => 404,
            ],
            'user not found' => [
                'group_id' => 1,
                'user_id' => 999,
                'expected_message' => 'Group or user not found!',
                'error_code' => 404,
            ],
        ];
    }

    public function test_remove_user(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);
        
        $response = $this->post(
            '/api/group/1/removeUser/2', 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully removed user from group!'
        ]);
        $this->assertDatabaseMissing(
            'group_user', 
            [
                'user_id' => 2,
                'group_id' => 1,
            ]
        );
    }

    /**
     * @dataProvider data_provider_test_remove_user_failed
     */
    public function test_remove_user_failed($group_id, $user_id, $expected_message, $error_code): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);
        
        $response = $this->post(
            '/api/group/' . $group_id . '/removeUser/' . $user_id, 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );
        
        $response->assertStatus($error_code);
        $response->assertJson([
            'message' => $expected_message,
        ]);
    }

    public static function data_provider_test_remove_user_failed(): array
    {
        return [
            'user not in group' => [
                'group_id' => 1,
                'user_id' => 1,
                'expected_message' => 'User not in group!',
                'error_code' => 400,
            ],
            'group not found' => [
                'group_id' => 999,
                'user_id' => 1,
                'expected_message' => 'Group or user not found!',
                'error_code' => 404,
            ],
            'user not found' => [
                'group_id' => 1,
                'user_id' => 999,
                'expected_message' => 'Group or user not found!',
                'error_code' => 404,
            ],
        ];
    }

    public function test_destroy(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', $request);

        $this->assertDatabaseHas(
            'groups', 
            [
                'id' => 1,
            ]
        );

        $response = $this->delete(
            '/api/group/2', 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully deleted group!'
        ]);
        $this->assertDatabaseMissing(
            'groups', 
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
            '/api/group/1', 
            [],
            [
                'Accept' => 'application/json',
                'Bearer' => 'Bearer ' . $response->json('token'),
            ]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Group has users!'
        ]);
    }
}
