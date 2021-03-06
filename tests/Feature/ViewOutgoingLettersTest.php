<?php

namespace Tests\Feature;

use App\Models\OutgoingLetter;
use App\Models\User;
use App\Types\OutgoingLetterType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ViewOutgoingLettersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_view_outgoing_letters()
    {
        $this->get(route('staff.outgoing_letters.index'))
            ->assertRedirect();
    }

    /** @test */
    public function user_cannot_view_any_outgoing_letter_if_permission_not_given()
    {
        $this->signIn($user = create(User::class));

        $user->roles->every->revokePermissionTo('outgoing letters:view');

        $this->withExceptionHandling()
            ->get(route('staff.outgoing_letters.index'))
            ->assertForbidden();
    }

    /** @test */
    public function user_can_view_outgoing_letters()
    {
        $this->signIn();
        create(OutgoingLetter::class, 3);

        $viewOutgoingLetters = $this->withoutExceptionHandling()
            ->get(route('staff.outgoing_letters.index'))
            ->assertSuccessful()
            ->assertViewIs('staff.outgoing_letters.index')
            ->assertViewHas('letters')
            ->viewData('letters');

        $this->assertInstanceOf(LengthAwarePaginator::class, $viewOutgoingLetters);
        $this->assertCount(3, $viewOutgoingLetters);
    }

    /** @test */
    public function view_letters_are_sorted_on_date()
    {
        $this->signIn();
        $letters = create(OutgoingLetter::class, 3);

        $view_data = $this->withExceptionHandling()
            ->get(route('staff.outgoing_letters.index'))
            ->assertSuccessful()
            ->assertViewIs('staff.outgoing_letters.index')
            ->assertViewHas('letters')
            ->viewData('letters');

        $letters = $letters->sortByDesc('date');
        $sorted_letters_ids = $letters->pluck('id')->toArray();
        $view_data_ids = $view_data->pluck('id')->toArray();
        $this->assertEquals($sorted_letters_ids, $view_data_ids);
    }

    /** @test */
    public function view_has_a_unique_list_of_recipients()
    {
        $this->signIn();

        create(OutgoingLetter::class, 3, ['recipient' => 'Exam Office']);
        create(OutgoingLetter::class);
        create(OutgoingLetter::class);

        $recipients = $this->withExceptionHandling()
                ->get(route('staff.outgoing_letters.index'))
                ->assertSuccessful()
                ->assertViewIs('staff.outgoing_letters.index')
                ->assertViewHas('recipients')
                ->viewData('recipients');

        $this->assertCount(3, $recipients);
        $this->assertSame(
            OutgoingLetter::all()->pluck('recipient', 'recipient')->toArray(),
            $recipients->toArray()
        );
    }

    /** @test */
    public function view_has_a_unique_list_of_types()
    {
        $this->signIn();

        $types = $this->withoutExceptionHandling()
                ->get(route('staff.outgoing_letters.index'))
                ->assertSuccessful()
                ->assertViewIs('staff.outgoing_letters.index')
                ->assertViewHas('types')
                ->viewData('types');

        $this->assertCount(3, $types);
        $this->assertSame(
            array_combine(OutgoingLetterType::values(), OutgoingLetterType::values()),
            $types->toArray()
        );
    }

    /** @test */
    public function view_has_a_unique_list_of_senders()
    {
        $this->signIn();

        create(OutgoingLetter::class, 3, ['sender_id' => 2]);
        create(OutgoingLetter::class);
        create(OutgoingLetter::class);

        $senders = $this->withExceptionHandling()
                ->get(route('staff.outgoing_letters.index'))
                ->assertSuccessful()
                ->assertViewIs('staff.outgoing_letters.index')
                ->assertViewHas('senders')
                ->viewData('senders');

        $this->assertCount(3, $senders);
        $this->assertSame(
            OutgoingLetter::with('sender')->get()->pluck('sender.name', 'sender_id')->toArray(),
            $senders->toArray()
        );
    }

    /** @test */
    public function view_has_a_unique_list_of_creators()
    {
        $user = $this->signIn();

        create(OutgoingLetter::class, 3, ['creator_id' => $user->id]);
        create(OutgoingLetter::class);
        create(OutgoingLetter::class);

        $creators = $this->withExceptionHandling()
                ->get(route('staff.outgoing_letters.index'))
                ->assertSuccessful()
                ->assertViewIs('staff.outgoing_letters.index')
                ->assertViewHas('creators')
                ->viewData('creators');

        $this->assertCount(3, $creators);
        $this->assertSame(
            OutgoingLetter::with('creator')->get()->pluck('creator.name', 'creator_id')->toArray(),
            $creators->toArray()
        );
    }
}
