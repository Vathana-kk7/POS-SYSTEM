<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthMiddlewareTest extends TestCase
{
    public function test_unauthenticated_api_request_returns_json_401(): void
    {
        $response = $this->withHeader('Accept', 'application/json')
            ->get('/api/test-auth');

        $response->assertStatus(401);
    }
}
