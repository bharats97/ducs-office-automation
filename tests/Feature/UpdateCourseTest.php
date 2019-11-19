<?php

namespace Tests\Feature;

use App\Programme;
use App\Course;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateCourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_update_course_code()
    {
        $this->withoutExceptionHandling()
            ->signIn();

        $course = create(Course::class);

        $response = $this->patch('/courses/'.$course->id, [
            'code' => $newCode = 'New123'
        ])->assertRedirect('/courses')
        ->assertSessionHasFlash('success', 'Course updated successfully!');

        $this->assertEquals(1, Course::count());
        $this->assertEquals($newCode, $course->fresh()->code);
    }

    /** @test */
    public function admin_can_update_course_name()
    {
        $this->withoutExceptionHandling()
            ->signIn();

        $course = create(Course::class);

        $response = $this->patch('/courses/' . $course->id, [
            'name' => $newName = 'New course'
        ])->assertRedirect('/courses')
        ->assertSessionHasFlash('success', 'Course updated successfully!');

        $this->assertEquals(1, Course::count());
        $this->assertEquals($newName, $course->fresh()->name);
    }

    /** @test */
    public function admin_can_update_courses_related_programme()
    {
        $this->withoutExceptionHandling()
            ->signIn();

        $course = create(Course::class);
        $newProgramme = create(Programme::class);

        $response = $this->patch('/courses/' . $course->id, [
            'programme_id' => $newProgramme->id
        ])->assertRedirect('/courses')
        ->assertSessionHasFlash('success', 'Course updated successfully!');

        $this->assertEquals(1, Course::count());
        $this->assertEquals($newProgramme->id, $course->fresh()->programme_id);
    }

    /** @test */
    public function course_is_not_validated_for_uniqueness_if_code_is_not_changed()
    {
        $this->withoutExceptionHandling()
            ->signIn();

        $course = create(Course::class);

        $response = $this->patch('/courses/'.$course->id, [
            'code' => $course->code,
            'name' => $newName = 'New course'
        ])->assertRedirect('/courses')
        ->assertSessionHasNoErrors()
        ->assertSessionHasFlash('success', 'Course updated successfully!');


        $this->assertEquals(1, Course::count());
        $this->assertEquals($newName, $course->fresh()->name);
    }
}