<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Programme;
use App\Models\ProgrammeRevision;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function course_belongs_to_a_programme_revsion()
    {
        $programme = create(Programme::class);
        $revision = create(ProgrammeRevision::class, 1, ['revised_at' => $programme->wef, 'programme_id' => $programme->id]);
        $course = create(Course::class);
        $course->programmeRevisions()->attach($revision, ['semester' => 1]);

        $this->assertInstanceOf(BelongsToMany::class, $course->programmeRevisions());
        $this->assertInstanceOf(ProgrammeRevision::class, $course->programmeRevisions()->first());
        $this->assertEquals($revision->id, $course->programmeRevisions()->first()->id);
    }

    /** @test */
    public function a_course_has_many_revisions()
    {
        $programme = create(Programme::class);
        $course = create(Course::class);

        $this->assertInstanceOf(HasMany::class, $course->revisions());

        $course->revisions()->createMany([
            ['revised_at' => now()->subYears(1)],
            ['revised_at' => now()->subYears(2)],
            ['revised_at' => now()->subYears(4)],
        ]);

        $this->assertCount(3, $course->revisions);
    }
}
