<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use App\Models\Live;
use App\Models\LiveComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class LiveControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_lives()
    {
        Live::factory()->count(3)->create(['status' => Live::STATUS_LIVE]);
        Live::factory()->count(2)->create(['status' => Live::STATUS_SCHEDULED]);

        $response = $this->getJson('/api/v1/lives');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'status',
                                'user'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_lives_by_status()
    {
        Live::factory()->create(['status' => Live::STATUS_LIVE]);
        Live::factory()->create(['status' => Live::STATUS_SCHEDULED]);

        $response = $this->getJson('/api/v1/lives?status=live');

        $response->assertStatus(200);
        $lives = $response->json('data.data');
        
        $this->assertCount(1, $lives);
        $this->assertEquals(Live::STATUS_LIVE, $lives[0]['status']);
    }

    /** @test */
    public function authenticated_user_can_create_live()
    {
        Sanctum::actingAs($this->user);

        $liveData = [
            'title' => 'My Live Stream',
            'description' => 'Testing live stream',
            'scheduled_at' => now()->addHour()->toISOString()
        ];

        $response = $this->postJson('/api/v1/lives', $liveData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'stream_key'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('lives', [
            'title' => 'My Live Stream',
            'user_id' => $this->user->id,
            'status' => Live::STATUS_SCHEDULED
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_live()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/lives', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_can_show_a_live()
    {
        $live = Live::factory()->create();

        $response = $this->getJson("/api/v1/lives/{$live->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'user',
                        'comments'
                    ]
                ]);
    }

    /** @test */
    public function owner_can_update_their_live()
    {
        Sanctum::actingAs($this->user);
        $live = Live::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/v1/lives/{$live->id}", [
            'title' => 'Updated Live Title',
            'description' => 'Updated description'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'title' => 'Updated Live Title',
                        'description' => 'Updated description'
                    ]
                ]);

        $this->assertDatabaseHas('lives', [
            'id' => $live->id,
            'title' => 'Updated Live Title'
        ]);
    }

    /** @test */
    public function non_owner_cannot_update_live()
    {
        $owner = User::factory()->create();
        $live = Live::factory()->create(['user_id' => $owner->id]);
        
        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/v1/lives/{$live->id}", [
            'title' => 'Updated Title'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_add_comment_to_live()
    {
        Sanctum::actingAs($this->user);
        $live = Live::factory()->create(['status' => Live::STATUS_LIVE]);

        $response = $this->postJson("/api/v1/lives/{$live->id}/comment", [
            'content' => 'Great live stream!'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'content',
                        'user'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('live_comments', [
            'live_id' => $live->id,
            'user_id' => $this->user->id,
            'content' => 'Great live stream!'
        ]);
    }

    /** @test */
    public function it_validates_comment_content()
    {
        Sanctum::actingAs($this->user);
        $live = Live::factory()->create();

        $response = $this->postJson("/api/v1/lives/{$live->id}/comment", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function authenticated_user_can_like_live()
    {
        Sanctum::actingAs($this->user);
        $live = Live::factory()->create();

        $response = $this->postJson("/api/v1/lives/{$live->id}/like");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Live liked'
                ]);

        $this->assertTrue($live->isLikedBy($this->user));
    }

    /** @test */
    public function authenticated_user_can_unlike_live()
    {
        Sanctum::actingAs($this->user);
        $live = Live::factory()->create();
        $live->toggleLike($this->user); // Like first

        $response = $this->postJson("/api/v1/lives/{$live->id}/like");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Live unliked'
                ]);

        $this->assertFalse($live->isLikedBy($this->user));
    }

    /** @test */
    public function it_can_join_live()
    {
        $live = Live::factory()->create(['status' => Live::STATUS_LIVE]);
        $initialViewers = $live->viewers_count;

        $response = $this->postJson("/api/v1/lives/{$live->id}/join");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'viewers_count'
                ]);

        $this->assertEquals($initialViewers + 1, $live->fresh()->viewers_count);
    }

    /** @test */
    public function it_can_leave_live()
    {
        $live = Live::factory()->create([
            'status' => Live::STATUS_LIVE,
            'viewers_count' => 5
        ]);

        $response = $this->postJson("/api/v1/lives/{$live->id}/leave");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'viewers_count'
                ]);

        $this->assertEquals(4, $live->fresh()->viewers_count);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_live()
    {
        $response = $this->postJson('/api/v1/lives', [
            'title' => 'Test Live'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_comment_on_live()
    {
        $live = Live::factory()->create();

        $response = $this->postJson("/api/v1/lives/{$live->id}/comment", [
            'content' => 'Test comment'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_like_live()
    {
        $live = Live::factory()->create();

        $response = $this->postJson("/api/v1/lives/{$live->id}/like");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_get_live_comments()
    {
        $live = Live::factory()->create();
        LiveComment::factory()->count(5)->create(['live_id' => $live->id]);

        $response = $this->getJson("/api/v1/lives/{$live->id}/comments");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'content',
                                'user'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_increments_comments_count_when_adding_comment()
    {
        Sanctum::actingAs($this->user);
        $live = Live::factory()->create([
            'status' => Live::STATUS_LIVE,
            'comments_count' => 0
        ]);

        $this->postJson("/api/v1/lives/{$live->id}/comment", [
            'content' => 'Test comment'
        ]);

        $this->assertEquals(1, $live->fresh()->comments_count);
    }

    /** @test */
    public function it_increments_likes_count_when_liking()
    {
        Sanctum::actingAs($this->user);
        $live = Live::factory()->create(['likes_count' => 0]);

        $this->postJson("/api/v1/lives/{$live->id}/like");

        $this->assertEquals(1, $live->fresh()->likes_count);
    }
}
