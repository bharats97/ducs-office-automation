<?php

namespace Tests\Feature;

use App\OutgoingLetterLog;
use Illuminate\Auth\AuthenticationException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\UnauthorizedException;

class CreateOutgoingLetterLogsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_fill_outgoing_letter_logs_form()
    {
        $this->be(factory(\App\User::class)->create());

        $this->withoutExceptionHandling()
            ->get('/outgoing-letter-logs/create')
            ->assertSuccessful()
            ->assertViewIs('outgoing_letter_logs.create');
    }

    /** @test */
    public function guest_cannot_fill_outgoing_letter_logs_form()
    {
        $this->expectException(AuthenticationException::class);

        $this->withoutExceptionHandling()
            ->get('/outgoing-letter-logs/create');
    }

    /** @test */
    public function store_outgoing_letter_log_in_database()
    {
        $outgoing_letter_log = factory(OutgoingLetterLog::class)->make();
        $this->be(factory(\App\User::class)->create());
        $this->withoutExceptionHandling()
            ->post('/outgoing-letter-logs', $outgoing_letter_log->toArray())
            ->assertSuccessful(201);
        $this->assertEquals(1, OutgoingLetterLog::count());
    }
}
