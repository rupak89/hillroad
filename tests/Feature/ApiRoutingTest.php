<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRoutingTest extends TestCase
{
    public function test_debug_api_routes_are_removed(): void
    {
        $this->getJson('/api/user-test')
            ->assertNotFound()
            ->assertJson(['message' => 'Not found']);

        $this->getJson('/api/session-test-api')
            ->assertNotFound()
            ->assertJson(['message' => 'Not found']);
    }

    public function test_unknown_api_routes_return_json_not_the_spa(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJson(['message' => 'Not found'])
            ->assertHeader('content-type', 'application/json');
    }

    public function test_known_api_routes_are_not_shadowed_by_the_fallback(): void
    {
        $this->getJson('/api/menus')->assertUnauthorized();
    }
}
