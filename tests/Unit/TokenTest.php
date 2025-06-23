<?php

namespace Ihasan\Bkash\Tests\Unit;

use Ihasan\Bkash\Facades\Bkash;
use Ihasan\Bkash\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }
    
    public function testCanGenerateToken()
    {
        Http::fake([
            '*/token/grant' => Http::response([
                'id_token' => 'test-token',
                'refresh_token' => 'test-refresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token = Bkash::getToken();

        $this->assertEquals('test-token', $token);

        $this->assertTrue(Cache::has('bkash_token'));
    }

    public function testCanRefreshToken()
    {
        Cache::put('bkash_refresh_token', 'test-refresh-token', now()->addDays(30));

        Http::fake([
            '*/token/refresh' => Http::response([
                'id_token' => 'refreshed-token',
                'refresh_token' => 'new-refresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token = Bkash::refreshToken();

        // Assert the token is returned
        $this->assertEquals('refreshed-token', $token);

        // Assert the token is cached
        $this->assertTrue(Cache::has('bkash_token'));
        $this->assertEquals('refreshed-token', Cache::get('bkash_token'));
    }

    public function testFallsBackToGetTokenIfNoRefreshTokenExists()
    {
        Http::fake([
            '*/token/grant' => Http::response([
                'id_token' => 'fallback-token',
                'refresh_token' => 'fallback-refresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token = Bkash::refreshToken();

        $this->assertEquals('fallback-token', $token);
    }

    public function testThrowsExceptionWhenTokenGenerationFails()
    {
        Http::fake([
            '*/token/grant' => Http::response([
                'statusCode' => 500,
                'statusMessage' => 'Token generation failed',
            ], 500),
        ]);

        // Expect an exception
        $this->expectException(\Ihasan\Bkash\Exceptions\TokenGenerationException::class);
        
        Bkash::getToken();
    }

    public function testThrowsExceptionWhenTokenRefreshFails()
    {
        Cache::put('bkash_refresh_token', 'test-refresh-token', now()->addDays(30));

        // Mock the HTTP response
        Http::fake([
            '*/token/refresh' => Http::response([
                'statusCode' => 500,
                'statusMessage' => 'Token refresh failed',
            ], 500),
        ]);

        // Expect an exception
        $this->expectException(\Ihasan\Bkash\Exceptions\RefreshTokenException::class);
        
        Bkash::refreshToken();
    }
}