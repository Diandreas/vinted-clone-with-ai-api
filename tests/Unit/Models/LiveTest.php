<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Live;
use App\Models\User;
use App\Models\LiveComment;
use App\Models\LiveLike;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LiveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_live()
    {
        $user = User::factory()->create();

        $liveData = [
            'user_id' => $user->id,
            'title' => 'Test Live',
            'description' => 'Test description',
            'status' => Live::STATUS_SCHEDULED,
            'stream_key' => 'test-stream-key',
        ];

        $live = Live::create($liveData);

        $this->assertDatabaseHas('lives', [
            'title' => 'Test Live',
            'status' => Live::STATUS_SCHEDULED,
        ]);
    }

    /** @test */
    public function it_can_start_a_live()
    {
        $user = User::factory()->create();
        $live = Live::factory()->create([
            'user_id' => $user->id,
            'status' => Live::STATUS_SCHEDULED
        ]);

        $result = $live->start();

        $this->assertTrue($result);
        $this->assertEquals(Live::STATUS_LIVE, $live->fresh()->status);
        $this->assertNotNull($live->fresh()->started_at);
        $this->assertTrue($user->fresh()->is_live);
    }

    /** @test */
    public function it_can_end_a_live()
    {
        $user = User::factory()->create(['is_live' => true]);
        $live = Live::factory()->create([
            'user_id' => $user->id,
            'status' => Live::STATUS_LIVE,
            'started_at' => now()->subHour()
        ]);

        $result = $live->end();

        $this->assertTrue($result);
        $this->assertEquals(Live::STATUS_ENDED, $live->fresh()->status);
        $this->assertNotNull($live->fresh()->ended_at);
        $this->assertFalse($user->fresh()->is_live);
    }

    /** @test */
    public function it_can_add_and_remove_viewers()
    {
        $live = Live::factory()->create([
            'status' => Live::STATUS_LIVE,
            'viewers_count' => 0,
            'max_viewers' => 0
        ]);

        $live->addViewer();
        $this->assertEquals(1, $live->fresh()->viewers_count);
        $this->assertEquals(1, $live->fresh()->max_viewers);

        $live->addViewer();
        $this->assertEquals(2, $live->fresh()->viewers_count);
        $this->assertEquals(2, $live->fresh()->max_viewers);

        $live->removeViewer();
        $this->assertEquals(1, $live->fresh()->viewers_count);
        $this->assertEquals(2, $live->fresh()->max_viewers); // max should not decrease
    }

    /** @test */
    public function it_can_be_liked_and_unliked()
    {
        $user = User::factory()->create();
        $live = Live::factory()->create();

        $liked = $live->toggleLike($user);
        $this->assertTrue($liked);
        $this->assertTrue($live->isLikedBy($user));

        $liked = $live->toggleLike($user);
        $this->assertFalse($liked);
        $this->assertFalse($live->isLikedBy($user));
    }

    /** @test */
    public function it_calculates_formatted_duration()
    {
        $live = Live::factory()->create(['duration_seconds' => 3725]); // 1h 2m 5s

        $this->assertEquals('1:02:05', $live->formatted_duration);
    }

    /** @test */
    public function it_calculates_engagement_rate()
    {
        $live = Live::factory()->create([
            'max_viewers' => 100,
            'likes_count' => 30,
            'comments_count' => 20
        ]);

        $this->assertEquals(50.0, $live->engagement_rate);
    }
}
