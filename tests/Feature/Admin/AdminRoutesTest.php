<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Report;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur admin
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
            'role' => 'admin'
        ]);
        
        // Créer un utilisateur régulier
        $this->regularUser = User::factory()->create([
            'is_admin' => false,
            'role' => 'user'
        ]);
    }

    /** @test */
    public function non_authenticated_users_cannot_access_admin_routes()
    {
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(401);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_routes()
    {
        Sanctum::actingAs($this->regularUser);
        
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_users_can_access_admin_routes()
    {
        Sanctum::actingAs($this->adminUser);
        
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_list_users()
    {
        Sanctum::actingAs($this->adminUser);
        
        // Créer quelques utilisateurs
        User::factory()->count(5)->create();
        
        $response = $this->getJson('/api/v1/admin/users');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    /** @test */
    public function admin_can_search_users()
    {
        Sanctum::actingAs($this->adminUser);
        
        $user = User::factory()->create(['name' => 'John Doe']);
        
        $response = $this->getJson('/api/v1/admin/users?search=John');
        
        $response->assertStatus(200)
                ->assertJsonPath('data.0.name', 'John Doe');
    }

    /** @test */
    public function admin_can_filter_users_by_role()
    {
        Sanctum::actingAs($this->adminUser);
        
        $response = $this->getJson('/api/v1/admin/users?role=user');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_show_user()
    {
        Sanctum::actingAs($this->adminUser);
        
        $user = User::factory()->create();
        
        $response = $this->getJson("/api/v1/admin/users/{$user->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user',
                        'stats'
                    ]
                ]);
    }

    /** @test */
    public function admin_can_verify_user()
    {
        Sanctum::actingAs($this->adminUser);
        
        $user = User::factory()->create(['is_verified' => false]);
        
        $response = $this->putJson("/api/v1/admin/users/{$user->id}/verify");
        
        $response->assertStatus(200);
        
        $this->assertTrue($user->fresh()->is_verified);
    }

    /** @test */
    public function admin_can_ban_user()
    {
        Sanctum::actingAs($this->adminUser);
        
        $user = User::factory()->create(['is_banned' => false]);
        
        $response = $this->putJson("/api/v1/admin/users/{$user->id}/ban");
        
        $response->assertStatus(200);
        
        $this->assertTrue($user->fresh()->is_banned);
    }

    /** @test */
    public function admin_can_unban_user()
    {
        Sanctum::actingAs($this->adminUser);
        
        $user = User::factory()->create(['is_banned' => true]);
        
        $response = $this->putJson("/api/v1/admin/users/{$user->id}/unban");
        
        $response->assertStatus(200);
        
        $this->assertFalse($user->fresh()->is_banned);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        Sanctum::actingAs($this->adminUser);
        
        $user = User::factory()->create();
        
        $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function admin_can_list_products()
    {
        Sanctum::actingAs($this->adminUser);
        
        Product::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/admin/products');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'meta'
                ]);
    }

    /** @test */
    public function admin_can_list_pending_products()
    {
        Sanctum::actingAs($this->adminUser);
        
        Product::factory()->create(['status' => 'pending']);
        
        $response = $this->getJson('/api/v1/admin/products/pending');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_approve_product()
    {
        Sanctum::actingAs($this->adminUser);
        
        $product = Product::factory()->create(['status' => 'pending']);
        
        $response = $this->putJson("/api/v1/admin/products/{$product->id}/approve");
        
        $response->assertStatus(200);
        
        $this->assertEquals('active', $product->fresh()->status);
    }

    /** @test */
    public function admin_can_reject_product()
    {
        Sanctum::actingAs($this->adminUser);
        
        $product = Product::factory()->create(['status' => 'pending']);
        
        $response = $this->putJson("/api/v1/admin/products/{$product->id}/reject");
        
        $response->assertStatus(200);
        
        $this->assertEquals('rejected', $product->fresh()->status);
    }

    /** @test */
    public function admin_can_feature_product()
    {
        Sanctum::actingAs($this->adminUser);
        
        $product = Product::factory()->create(['is_featured' => false]);
        
        $response = $this->putJson("/api/v1/admin/products/{$product->id}/feature");
        
        $response->assertStatus(200);
        
        $this->assertTrue($product->fresh()->is_featured);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        Sanctum::actingAs($this->adminUser);
        
        $product = Product::factory()->create();
        
        $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function admin_can_list_reports()
    {
        Sanctum::actingAs($this->adminUser);
        
        Report::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/admin/reports');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_show_report()
    {
        Sanctum::actingAs($this->adminUser);
        
        $report = Report::factory()->create();
        
        $response = $this->getJson("/api/v1/admin/reports/{$report->id}");
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_resolve_report()
    {
        Sanctum::actingAs($this->adminUser);
        
        $report = Report::factory()->create(['status' => 'open']);
        
        $response = $this->putJson("/api/v1/admin/reports/{$report->id}/resolve");
        
        $response->assertStatus(200);
        
        $this->assertEquals('resolved', $report->fresh()->status);
    }

    /** @test */
    public function admin_can_dismiss_report()
    {
        Sanctum::actingAs($this->adminUser);
        
        $report = Report::factory()->create(['status' => 'open']);
        
        $response = $this->putJson("/api/v1/admin/reports/{$report->id}/dismiss");
        
        $response->assertStatus(200);
        
        $this->assertEquals('dismissed', $report->fresh()->status);
    }

    /** @test */
    public function admin_can_list_categories()
    {
        Sanctum::actingAs($this->adminUser);
        
        Category::factory()->count(3)->create();
        
        $response = $this->getJson('/api/v1/admin/categories');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'meta'
                ]);
    }

    /** @test */
    public function admin_can_create_category()
    {
        Sanctum::actingAs($this->adminUser);
        
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'color' => '#FF5733',
            'is_active' => true
        ];
        
        $response = $this->postJson('/api/v1/admin/categories', $categoryData);
        
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
        
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    /** @test */
    public function admin_can_update_category()
    {
        Sanctum::actingAs($this->adminUser);
        
        $category = Category::factory()->create();
        
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description'
        ];
        
        $response = $this->putJson("/api/v1/admin/categories/{$category->id}", $updateData);
        
        $response->assertStatus(200);
        
        $this->assertEquals('Updated Category', $category->fresh()->name);
    }

    /** @test */
    public function admin_can_delete_category()
    {
        Sanctum::actingAs($this->adminUser);
        
        $category = Category::factory()->create();
        
        $response = $this->deleteJson("/api/v1/admin/categories/{$category->id}");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function admin_cannot_delete_category_with_products()
    {
        Sanctum::actingAs($this->adminUser);
        
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);
        
        $response = $this->deleteJson("/api/v1/admin/categories/{$category->id}");
        
        $response->assertStatus(422);
        
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function admin_can_create_brand()
    {
        Sanctum::actingAs($this->adminUser);
        
        $brandData = [
            'name' => 'Test Brand',
            'description' => 'Test Brand Description'
        ];
        
        $response = $this->postJson('/api/v1/admin/brands', $brandData);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('brands', ['name' => 'Test Brand']);
    }

    /** @test */
    public function admin_can_update_brand()
    {
        Sanctum::actingAs($this->adminUser);
        
        $brand = Brand::factory()->create();
        
        $updateData = [
            'name' => 'Updated Brand',
            'description' => 'Updated Description'
        ];
        
        $response = $this->putJson("/api/v1/admin/brands/{$brand->id}", $updateData);
        
        $response->assertStatus(200);
        
        $this->assertEquals('Updated Brand', $brand->fresh()->name);
    }

    /** @test */
    public function admin_can_delete_brand()
    {
        Sanctum::actingAs($this->adminUser);
        
        $brand = Brand::factory()->create();
        
        $response = $this->deleteJson("/api/v1/admin/brands/{$brand->id}");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    /** @test */
    public function admin_can_access_analytics()
    {
        Sanctum::actingAs($this->adminUser);
        
        $response = $this->getJson('/api/v1/admin/analytics/overview');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_settings()
    {
        Sanctum::actingAs($this->adminUser);
        
        $response = $this->getJson('/api/v1/admin/settings');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_update_settings()
    {
        Sanctum::actingAs($this->adminUser);
        
        $settingsData = [
            'site_name' => 'Updated Site Name',
            'maintenance_mode' => false
        ];
        
        $response = $this->putJson('/api/v1/admin/settings', $settingsData);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_routes_are_properly_protected()
    {
        // Test sans authentification
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(401);
        
        // Test avec utilisateur non-admin
        Sanctum::actingAs($this->regularUser);
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(403);
        
        // Test avec utilisateur admin
        Sanctum::actingAs($this->adminUser);
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(200);
    }
}
