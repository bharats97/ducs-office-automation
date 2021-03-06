<?php

namespace Tests\Feature;

use App\Models\Scholar;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SupervisorManagesAdvisoryMeetingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function supervisor_can_add_advisory_meetings()
    {
        Storage::fake();

        $scholar = create(Scholar::class);

        $this->signIn($scholar->supervisor);

        try {
            $this->withoutExceptionHandling()
                ->post(route('research.scholars.advisory_meetings.store', $scholar), [
                    'date' => now()->subDays(2)->format('Y-m-d'),
                    'minutes_of_meeting' => UploadedFile::fake()
                        ->create('minutes_of_meeting.pdf', 15),
                ])->assertRedirect();
        } catch (ValidationException $e) {
            dump($e->errors());
            $this->fail('validation error occured!');
        }

        $this->assertCount(1, $scholar->fresh()->advisoryMeetings);
    }

    /** @test */
    public function supervisor_can_view_minutes_of_meeting_for_a_advisory_meeting()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('minutes_of_meeting.pdf', 15, 'document/*');

        $scholar = create(Scholar::class);

        $meeting = $scholar->advisoryMeetings()->create([
            'date' => now()->subDays(2),
            'minutes_of_meeting_path' => $file->store('advisory_meetings'),
        ]);

        $this->signIn($scholar->supervisor);

        $this->withoutExceptionHandling()
            ->get(route('research.scholars.advisory_meetings.minutes_of_meeting', [$meeting]))
            ->assertSuccessful();
    }
}
