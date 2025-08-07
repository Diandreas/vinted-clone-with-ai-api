<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function database_connection_works()
    {
        // Test that we can connect to the database
        $this->assertNotNull(DB::connection());
        
        // Test that we're using a valid driver
        $this->assertContains(DB::connection()->getDriverName(), ['sqlite', 'mysql']);
        
        // Test a simple query
        $result = DB::select('SELECT 1 as test');
        $this->assertEquals(1, $result[0]->test);
    }

    /** @test */
    public function migrations_run_successfully()
    {
        // Test that the main tables exist after migration
        $this->assertTrue(\Schema::hasTable('users'));
        $this->assertTrue(\Schema::hasTable('products'));
        $this->assertTrue(\Schema::hasTable('categories'));
    }
}
