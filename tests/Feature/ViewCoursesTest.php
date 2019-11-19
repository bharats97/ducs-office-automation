<?php

namespace Tests\Feature;

use App\Programme;
use App\Course;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ViewCoursesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_all_courses_ordered_by_latest_added()
    {
        $this->signIn();

        $courses = create(Course::class, 3);

        $viewCourses = $this->withoutExceptionHandling()->get('/courses')
            ->assertSuccessful()
            ->assertViewIs('courses.index')
            ->assertViewHas('courses')
            ->viewData('courses');

        $this->assertCount(3, $viewCourses);
        $this->assertSame(
            $courses->sortByDesc('created_at')->pluck('id')->toArray(),
            $viewCourses->pluck('id')->toArray()
        );
    }

    /** @test */
    public function courses_index_page_also_has_programmes()
    {
        $this->signIn();

        $programmes = create(Programme::class, 3);

        $viewProgrammes = $this->withoutExceptionHandling()->get('/courses')
            ->assertSuccessful()
            ->assertViewHas('programmes')
            ->viewData('programmes');

        $this->assertInstanceOf(Collection::class, $viewProgrammes);
        $this->assertCount(3, $viewProgrammes);
        $this->assertSame($viewProgrammes->toArray(), $programmes->pluck('name', 'id')->toArray());
    }
}