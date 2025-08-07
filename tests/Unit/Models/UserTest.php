<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Follow;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'username' => 'johndoe',
            'bio' => 'Test bio',
        ];

        $user = User::create($userData);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayNotHasKey('email', $userArray);
        $this->assertArrayNotHasKey('phone', $userArray);
        $this->assertArrayNotHasKey('date_of_birth', $userArray);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_verified' => true,
            'is_live' => false,
            'settings' => ['theme' => 'dark'],
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
        $this->assertIsBool($user->is_verified);
        $this->assertIsBool($user->is_live);
        $this->assertIsArray($user->settings);
    }

    /** @test */
    public function it_has_products_relationship()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->products->contains($product));
        $this->assertEquals($user->id, $product->user_id);
    }

    /** @test */
    public function it_can_follow_and_unfollow_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Test following
        $result = $user1->follow($user2);
        $this->assertTrue($result);
        $this->assertTrue($user1->isFollowing($user2));

        // Test unfollowing
        $result = $user1->unfollow($user2);
        $this->assertTrue($result);
        $this->assertFalse($user1->isFollowing($user2));
    }

    /** @test */
    public function it_cannot_follow_itself()
    {
        $user = User::factory()->create();

        $result = $user->follow($user);
        $this->assertFalse($result);
        $this->assertFalse($user->isFollowing($user));
    }

    /** @test */
    public function it_cannot_follow_same_user_twice()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->follow($user2);
        $result = $user1->follow($user2);

        $this->assertFalse($result);
        $this->assertEquals(1, $user1->following()->count());
    }

    /** @test */
    public function it_has_followers_and_following_relationships()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $user1->follow($user2);
        $user3->follow($user1);

        $this->assertEquals(1, $user1->following()->count());
        $this->assertEquals(1, $user1->followers()->count());
        $this->assertEquals(0, $user2->following()->count());
        $this->assertEquals(1, $user2->followers()->count());
    }

    /** @test */
    public function it_calculates_followers_count_attribute()
    {
        $user = User::factory()->create();
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();

        $follower1->follow($user);
        $follower2->follow($user);

        $this->assertEquals(2, $user->followers_count);
    }

    /** @test */
    public function it_calculates_following_count_attribute()
    {
        $user = User::factory()->create();
        $following1 = User::factory()->create();
        $following2 = User::factory()->create();

        $user->follow($following1);
        $user->follow($following2);

        $this->assertEquals(2, $user->following_count);
    }

    /** @test */
    public function it_calculates_average_rating_attribute()
    {
        $user = User::factory()->create();
        $reviewer = User::factory()->create();
        
        Review::factory()->create([
            'reviewed_id' => $user->id, 
            'reviewer_id' => $reviewer->id, 
            'rating' => 5
        ]);
        Review::factory()->create([
            'reviewed_id' => $user->id, 
            'reviewer_id' => $reviewer->id, 
            'rating' => 3
        ]);
        Review::factory()->create([
            'reviewed_id' => $user->id, 
            'reviewer_id' => $reviewer->id, 
            'rating' => 4
        ]);

        $this->assertEquals(4, $user->average_rating);
    }

    /** @test */
    public function it_returns_zero_average_rating_when_no_reviews()
    {
        $user = User::factory()->create();

        $this->assertEquals(0, $user->average_rating);
    }

    /** @test */
    public function it_generates_avatar_url()
    {
        $user = User::factory()->create(['avatar' => 'test-avatar.jpg']);
        
        $this->assertStringContainsString('storage/avatars/test-avatar.jpg', $user->avatar_url);
    }

    /** @test */
    public function it_returns_default_avatar_when_no_avatar()
    {
        $user = User::factory()->create(['avatar' => null]);
        
        $this->assertStringContainsString('images/default-avatar.png', $user->avatar_url);
    }

    /** @test */
    public function it_updates_last_seen_timestamp()
    {
        $user = User::factory()->create(['last_seen_at' => null]);

        $user->updateLastSeen();

        $this->assertNotNull($user->fresh()->last_seen_at);
        $this->assertTrue($user->fresh()->last_seen_at->isAfter(now()->subMinute()));
    }

    /** @test */
    public function it_scopes_verified_users()
    {
        $verifiedUser = User::factory()->create(['is_verified' => true]);
        $unverifiedUser = User::factory()->create(['is_verified' => false]);

        $verifiedUsers = User::verified()->get();

        $this->assertTrue($verifiedUsers->contains($verifiedUser));
        $this->assertFalse($verifiedUsers->contains($unverifiedUser));
    }

    /** @test */
    public function it_scopes_live_users()
    {
        $liveUser = User::factory()->create(['is_live' => true]);
        $offlineUser = User::factory()->create(['is_live' => false]);

        $liveUsers = User::live()->get();

        $this->assertTrue($liveUsers->contains($liveUser));
        $this->assertFalse($liveUsers->contains($offlineUser));
    }

    /** @test */
    public function it_scopes_recently_active_users()
    {
        $recentUser = User::factory()->create(['last_seen_at' => now()->subMinutes(5)]);
        $oldUser = User::factory()->create(['last_seen_at' => now()->subHours(2)]);

        $recentUsers = User::recentlyActive()->get();

        $this->assertTrue($recentUsers->contains($recentUser));
        $this->assertFalse($recentUsers->contains($oldUser));
    }

    /** @test */
    public function it_should_be_searchable_only_when_email_verified()
    {
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

        $this->assertTrue($verifiedUser->shouldBeSearchable());
        $this->assertFalse($unverifiedUser->shouldBeSearchable());
    }

    /** @test */
    public function it_returns_searchable_array()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'username' => 'johndoe',
            'bio' => 'Test bio',
            'location' => 'Paris',
            'is_verified' => true,
        ]);

        $searchableArray = $user->toSearchableArray();

        $this->assertArrayHasKey('id', $searchableArray);
        $this->assertArrayHasKey('name', $searchableArray);
        $this->assertArrayHasKey('username', $searchableArray);
        $this->assertArrayHasKey('bio', $searchableArray);
        $this->assertArrayHasKey('location', $searchableArray);
        $this->assertArrayHasKey('is_verified', $searchableArray);
        $this->assertEquals('John Doe', $searchableArray['name']);
        $this->assertEquals('johndoe', $searchableArray['username']);
    }
}