<?php

namespace Ihasan\Bkash\Tests\Unit;

use Ihasan\Bkash\Facades\Bkash;
use Ihasan\Bkash\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ReflectionClass;

class MultiTenantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Cache::flush();
    }
    
    public function testCanSetTenantId()
    {
        $bkash = app(\Ihasan\Bkash\Bkash::class);
        $bkash->forTenant('tenant-1');

        $reflection = new ReflectionClass($bkash);
        $property = $reflection->getProperty('tenantId');
        $property->setAccessible(true);

        $this->assertEquals('tenant-1', $property->getValue($bkash));
    }

    public function testUsesTenantSpecificCacheKeys()
    {
        Http::fake([
            '*/token/grant' => Http::response([
                'id_token' => 'tenant-1-token',
                'refresh_token' => 'tenant-1-refresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token = Bkash::forTenant('tenant-1')->getToken();

        // Assert the token is returned
        $this->assertEquals('tenant-1-token', $token);

        // Assert the token is cached with tenant prefix
        $this->assertTrue(Cache::has('tenant_tenant-1_bkash_token'));
        $this->assertEquals('tenant-1-token', Cache::get('tenant_tenant-1_bkash_token'));
    }

    public function testDifferentTenantsHaveDifferentTokens()
    {
        Http::fake([
            '*/token/grant' => Http::sequence()
                ->push(['id_token' => 'tenant-1-token', 'expires_in' => 3600], 200)
                ->push(['id_token' => 'tenant-2-token', 'expires_in' => 3600], 200),
        ]);

        $token1 = Bkash::forTenant('tenant-1')->getToken();
        $token2 = Bkash::forTenant('tenant-2')->getToken();

        // Assert the tokens are different
        $this->assertEquals('tenant-1-token', $token1);
        $this->assertEquals('tenant-2-token', $token2);

        // Assert both tokens are cached with their respective tenant prefixes
        $this->assertTrue(Cache::has('tenant_tenant-1_bkash_token'));
        $this->assertTrue(Cache::has('tenant_tenant-2_bkash_token'));
    }

    public function testUsesDefaultCacheKeysWhenNoTenantIsSpecified()
    {
        Http::fake([
            '*/token/grant' => Http::response([
                'id_token' => 'default-token',
                'refresh_token' => 'default-refresh-token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $token = Bkash::getToken();

        $this->assertEquals('default-token', $token);

        // Assert the token is cached without tenant prefix
        $this->assertTrue(Cache::has('bkash_token'));
        $this->assertEquals('default-token', Cache::get('bkash_token'));
    }
}