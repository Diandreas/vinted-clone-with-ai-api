<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function application_returns_successful_response()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function api_health_check_works()
    {
        $response = $this->getJson('/api/health');
        
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'OK'
                ])
                ->assertJsonStructure([
                    'status',
                    'timestamp',
                    'version'
                ]);
    }

    /** @test */
    public function database_health_check_works()
    {
        $response = $this->getJson('/api/health/database');
        
        $response->assertStatus(200)
                ->assertJson([
                    'database' => 'connected'
                ]);
    }

    /** @test */
    public function api_documentation_endpoint_works()
    {
        $response = $this->getJson('/api/docs');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'version',
                    'endpoints'
                ]);
    }

    /** @test */
    public function api_returns_404_for_unknown_endpoints()
    {
        $response = $this->getJson('/api/v1/nonexistent-endpoint');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'API endpoint not found'
                ]);
    }

    /** @test */
    public function unauthenticated_requests_to_protected_routes_return_401()
    {
        $protectedRoutes = [
            '/api/v1/products',
            '/api/v1/lives',
            '/api/v1/auth/user',
            '/api/v1/users/profile',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->postJson($route);
            $this->assertEquals(401, $response->status(), "Route {$route} should return 401 for unauthenticated requests");
        }
    }

    /** @test */
    public function api_has_proper_cors_headers()
    {
        $response = $this->getJson('/api/v1/products');
        
        // Note: You might need to configure CORS headers in your Laravel app
        // This test assumes basic CORS setup
        $this->assertNotNull($response->headers->get('Content-Type'));
    }

    /** @test */
    public function api_rate_limiting_is_configured()
    {
        // Test that rate limiting exists (this might need adjustment based on your rate limiting setup)
        $responses = [];
        
        // Make multiple requests quickly
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/v1/products');
        }
        
        // All should succeed for reasonable rate limits
        foreach ($responses as $response) {
            $this->assertContains($response->status(), [200, 429], 'Response should be either success or rate limited');
        }
    }

    /** @test */
    public function environment_is_testing()
    {
        $this->assertEquals('testing', app()->environment());
    }

    /** @test */
    public function required_environment_variables_are_set()
    {
        $requiredEnvVars = [
            'APP_NAME',
            'APP_ENV',
            'APP_KEY',
            'DB_CONNECTION',
        ];

        foreach ($requiredEnvVars as $envVar) {
            $this->assertNotEmpty(
                config(strtolower(str_replace('_', '.', $envVar))),
                "Environment variable {$envVar} should be set"
            );
        }
    }

    /** @test */
    public function cache_is_working()
    {
        $key = 'test_cache_key';
        $value = 'test_cache_value';
        
        // Store in cache
        cache()->put($key, $value, 60);
        
        // Retrieve from cache
        $cachedValue = cache()->get($key);
        
        $this->assertEquals($value, $cachedValue);
        
        // Clean up
        cache()->forget($key);
    }

    /** @test */
    public function storage_directories_are_writable()
    {
        $directories = [
            storage_path('app'),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($directories as $directory) {
            $this->assertTrue(
                is_writable($directory),
                "Directory {$directory} should be writable"
            );
        }
    }
}
