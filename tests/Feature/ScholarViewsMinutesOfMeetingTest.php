<?php

namespace Tests\Feature;

use App\Models\AdvisoryMeeting;
use App\Models\Scholar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScholarViewsMinutesOfMeetingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function scholar_can_view_their_minutes_of_meetings()
    {
        Storage::fake();

        $this->signInScholar($scholar = create(Scholar::class));

        $minutesOfMeetingPath = UploadedFile::fake()->create('fakefile.pdf', 20, 'application/pdf')->store('advisory_meetings');

        $meeting = create(AdvisoryMeeting::class, 1, [
            'scholar_id' => $scholar->id,
            'date' => now()->format('Y-m-d'),
            'minutes_of_meeting_path' => $minutesOfMeetingPath,
        ]);

        $this->withoutExceptionHandling()
           ->get(route('scholars.advisory_meetings.minutes_of_meeting', $meeting))
           ->assertSuccessful();
    }

    /** @test */
    public function scholar_can_not_view_minutes_of_meetings_of_other_scholars()
    {
        Storage::fake();

        $this->signInScholar($scholar = create(Scholar::class));

        $minutesOfMeetingPath = UploadedFile::fake()->create('fakefile.pdf', 20, 'application/pdf')->store('advisory_meetings');

        $otherScholar = create(Scholar::class);

        $meeting = $otherScholar->advisoryMeetings()->create([
            'date' => now()->format('Y-m-d'),
            'minutes_of_meeting_path' => $minutesOfMeetingPath,
        ]);

        $this->withExceptionHandling()
           ->get(route('scholars.advisory_meetings.minutes_of_meeting', $meeting))
           ->assertForbidden();
    }
}
