<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_player_when_user_is_created()
    {
        $this->assertNotNull(factory(User::class)->create()->player);
    }
}
