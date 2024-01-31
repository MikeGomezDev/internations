<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate --seed');
    }

    public function test_authenticate(): void
    {
        $request = [
            'name' => 'admin',
            'password' => 'admin_password',
        ];

        $response = $this->post('/api/authenticate', 
            $request
        );

        $response->assertStatus(201);
    }

    /**
     * @dataProvider data_provider_test_authenticate_failed
     */
    public function test_authenticate_failed_validation($request, $errorCode, $errorMessage): void
    {
        $response = $this->post('/api/authenticate', 
            $request
        );

        $response->assertStatus($errorCode);
        $response->assertJson($errorMessage);
    }

    public static function data_provider_test_authenticate_failed(): array
    {
        return [
            [
                [
                    'name' => 'admin',
                    'password' => 'wrong_password',
                ],
                401,
                [
                    'message' => 'Invalid login details'
                ]
            ],
            [
                [
                    'name' => 'wrong_name',
                    'password' => 'admin_password',
                ],
                401,
                [
                    'message' => 'Invalid login details'
                ]
            ],
            [
                [
                    'name' => 'user',
                    'password' => 'user_password',
                ],
                401,
                [
                    'message' => 'You are not authorized to access this resource'
                ]
            ],
            [
                [
                    'name' => 'name',
                ],
                422,
                [
                    'errors' => [
                        'password' => [
                            'The password field is required.'
                        ]
                    ]
                ],
            ],
            [
                [
                    'password' => 'password',
                ],
                422,
                [
                    'errors' => [
                        'name' => [
                            'The name field is required.'
                        ]
                    ]
                ],
            ],
        ];
    }
}
