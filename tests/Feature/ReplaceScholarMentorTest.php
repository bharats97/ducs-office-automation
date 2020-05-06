<?php

namespace Tests\Feature;

use App\Models\Cosupervisor;
use App\Models\Scholar;
use App\Models\SupervisorProfile;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReplaceScholarMentorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function co_supervisor_of_scholar_can_be_replaced()
    {
        $this->signIn();
        $scholar = create(Scholar::class);

        $oldCosupervisor = $scholar->cosupervisor;
        $newCosupervisor = create(Cosupervisor::class);

        $this->withoutExceptionHandling()
            ->patch(route('staff.scholars.replace_cosupervisor', $scholar), [
                'cosupervisor_profile_type' => Cosupervisor::class,
                'cosupervisor_profile_id' => $newCosupervisor->id,
            ])
            ->assertSessionHasFlash('success', 'Co-Supervisor replaced successfully!');

        $this->assertEquals(1, count($scholar->fresh()->old_cosupervisors));

        $this->assertEquals($oldCosupervisor->name, $scholar->fresh()->old_cosupervisors[0]['name']);
        $this->assertEquals($oldCosupervisor->email, $scholar->fresh()->old_cosupervisors[0]['email']);
        $this->assertEquals($oldCosupervisor->designation, $scholar->fresh()->old_cosupervisors[0]['designation']);
        $this->assertEquals($oldCosupervisor->affiliation, $scholar->fresh()->old_cosupervisors[0]['affiliation']);
        $this->assertEquals(now()->format('d F Y'), $scholar->fresh()->old_cosupervisors[0]['date']);

        $this->assertTrue($newCosupervisor->is($scholar->fresh()->cosupervisor));
    }

    /** @test */
    public function supervisor_of_scholar_can_be_replaced()
    {
        $this->signIn();
        $scholar = create(Scholar::class);
        $oldSupervisor = $scholar->supervisor;
        $newSupervisorProfile = create(SupervisorProfile::class);

        $this->withoutExceptionHandling()
            ->patch(route('staff.scholars.replace_supervisor', $scholar), [
                'supervisor_profile_id' => $newSupervisorProfile->id,
            ])
            ->assertSessionHasFlash('success', 'Supervisor replaced successfully!');

        $this->assertEquals(1, count($scholar->fresh()->old_supervisors));

        $this->assertEquals($oldSupervisor->name, $scholar->fresh()->old_supervisors[0]['name']);
        $this->assertEquals($oldSupervisor->email, $scholar->fresh()->old_supervisors[0]['email']);
        $this->assertEquals(now()->format('d F Y'), $scholar->fresh()->old_supervisors[0]['date']);

        $this->assertTrue($newSupervisorProfile->supervisor->is($scholar->fresh()->supervisor));
    }

    /** @test */
    public function co_supervisor_of_scholar_can_be_replaced_even_if_current_cosupervisor_is_null()
    {
        $this->signIn();

        $scholar = create(Scholar::class, 1, [
            'cosupervisor_profile_type' => null,
            'cosupervisor_profile_id' => null,
        ]);

        $newCosupervisor = create(Cosupervisor::class);

        $this->withoutExceptionHandling()
            ->patch(route('staff.scholars.replace_cosupervisor', $scholar), [
                'cosupervisor_profile_type' => Cosupervisor::class,
                'cosupervisor_profile_id' => $newCosupervisor->id,
            ])
            ->assertSessionHasFlash('success', 'Co-Supervisor replaced successfully!');

        $this->assertEquals(1, count($scholar->fresh()->old_cosupervisors));

        $this->assertNull($scholar->fresh()->old_cosupervisors[0]['name']);
        $this->assertNull($scholar->fresh()->old_cosupervisors[0]['email']);
        $this->assertNull($scholar->fresh()->old_cosupervisors[0]['designation']);
        $this->assertNull($scholar->fresh()->old_cosupervisors[0]['affiliation']);
        $this->assertEquals(now()->format('d F Y'), $scholar->fresh()->old_cosupervisors[0]['date']);

        $this->assertTrue($newCosupervisor->is($scholar->fresh()->cosupervisor));
    }

    // /** @test */
    // public function cosupervisor_of_scholar_can_not_be_replaced_if_cosupervisor_is_same_as_previous_cosupervisor()
    // {
    //     $this->signIn();
    //     $scholar = create(Scholar::class);

    //     $this->assertEquals(0, count($scholar->fresh()->old_cosupervisors));

    //     $oldCosupervisor = $scholar->cosupervisor;

    //     try {
    //         $this->withoutExceptionHandling()
    //             ->patch(route('staff.scholars.replace_cosupervisor', $scholar), [
    //                 'cosupervisor_id' => $oldCosupervisor->id,
    //             ]);
    //     } catch (ValidationException $e) {
    //         $this->assertArrayHasKey('cosupervisor_id', $e->errors());
    //     }

    //     $this->assertEquals(0, count($scholar->fresh()->old_cosupervisors));

    //     $scholar = create(Scholar::class, 1, ['cosupervisor_id' => '']);

    //     $this->assertEquals(0, count($scholar->fresh()->old_cosupervisors));

    //     try {
    //         $this->withoutExceptionHandling()
    //             ->patch(route('staff.scholars.replace_cosupervisor', $scholar), [
    //                 'cosupervisor_id' => '',
    //             ]);
    //     } catch (ValidationException $e) {
    //         $this->assertArrayHasKey('cosupervisor_id', $e->errors());
    //     }

    //     $this->assertEquals(0, count($scholar->fresh()->old_cosupervisors));
    // }

    /** @test */
    public function supervisor_of_scholar_can_not_be_replaced_if_supervisor_is_same_as_previous_supervisor()
    {
        $this->signIn();
        $scholar = create(Scholar::class);

        $this->assertEquals(0, count($scholar->fresh()->old_supervisors));

        $oldSupervisorProfileId = $scholar->supervisor_profile_id;

        try {
            $this->withoutExceptionHandling()
                ->patch(route('staff.scholars.replace_supervisor', $scholar), [
                    'cosupervisor_id' => $oldSupervisorProfileId,
                ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('supervisor_profile_id', $e->errors());
        }

        $this->assertEquals(0, count($scholar->fresh()->old_supervisors));
    }

    /** @test */
    public function current_advisory_committee_is_replaced_if_the_scholar_supervisor_is_replaced()
    {
        $this->signIn();

        $scholar = create(Scholar::class, 1, [
            'created_at' => now()->subMonths(3),
        ]);
        $newSupervisorProfile = create(SupervisorProfile::class);

        $beforeSupervisorReplaceAdvisoryCommittee = $scholar->advisory_committee;

        $this->assertEquals([], $scholar->old_advisory_committees);

        $this->withoutExceptionHandling()
            ->patch(route('staff.scholars.replace_supervisor', $scholar), [
                'supervisor_profile_id' => $newSupervisorProfile->id,
            ])
            ->assertSessionHasFlash('success', 'Supervisor replaced successfully!');

        $this->assertEquals(1, count($scholar->fresh()->old_supervisors));

        $this->assertEquals(count($scholar->fresh()->old_advisory_committees), 1);

        $expectedOldCommittee = [
            'committee' => $beforeSupervisorReplaceAdvisoryCommittee,
            'to_date' => today(),
            'from_date' => Carbon::parse($scholar->created_at->format('d F Y')),
        ];

        $this->assertEquals(
            $expectedOldCommittee,
            $scholar->fresh()->old_advisory_committees[0]
        );
    }

    /** @test */
    public function current_advisory_committee_is_replaced_if_the_scholar_cosupervisor_is_replaced()
    {
        $this->signIn();
        $scholar = create(Scholar::class);
        $newCosupervisor = create(Cosupervisor::class);

        $beforeCosupervisorReplaceAdvisoryCommittee = $scholar->advisory_committee;

        $this->withoutExceptionHandling()
            ->patch(route('staff.scholars.replace_cosupervisor', $scholar), [
                'cosupervisor_profile_type' => Cosupervisor::class,
                'cosupervisor_profile_id' => $newCosupervisor->id,
            ])
            ->assertSessionHasFlash('success', 'Co-Supervisor replaced successfully!');

        $this->assertEquals(1, count($scholar->fresh()->old_cosupervisors));

        $this->assertEquals(count($scholar->fresh()->old_advisory_committees), 1);

        $expectedOldCommittee = [
            'committee' => $beforeCosupervisorReplaceAdvisoryCommittee,
            'to_date' => today(),
            'from_date' => Carbon::parse($scholar->created_at->format('d F Y')),
        ];

        $this->assertEquals(
            $expectedOldCommittee,
            $scholar->fresh()->old_advisory_committees[0]
        );
    }
}
