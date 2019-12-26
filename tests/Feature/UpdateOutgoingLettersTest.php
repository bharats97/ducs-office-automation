<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\OutgoingLetter;
use App\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateOutgoingLettersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_update_letters()
    {
        $letter = create(OutgoingLetter::class);
        $this->withExceptionHandling()
            ->patch("/outgoing-letters/{$letter->id}", ['date' => '2018-08-9'])
            ->assertRedirect('/login');

        $this->assertEquals($letter->date, $letter->fresh()->date);
    }

    /** @test */
    public function user_can_update_outgoing_letter_in_database()
    {
        Storage::fake();

        $this->signIn();
        $sender = create(User::class);
        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id(),
            'type' => 'Notesheet'
        ]);

        $new_outgoing_letter = [
            'date' => "1987-02-08",
            'recipient' => "Raleigh Wunsch",
            'sender_id' => $sender->id,
            'description' => "Voluptatem est odit voluptas eius deserunt.",
            'amount' => 11243.56,
            'attachments' => [ $file = UploadedFile::fake()->create('document.pdf') ]
        ];

        $this->withoutExceptionHandling()
            ->patch(
                "/outgoing-letters/{$letter->id}",
                $new_outgoing_letter
            )->assertRedirect('/outgoing-letters');

        $letter = $letter->fresh();
        $this->assertEquals($new_outgoing_letter['description'], $letter->description);
        $this->assertEquals($new_outgoing_letter['sender_id'], $letter->sender_id);
        $this->assertEquals($new_outgoing_letter['amount'], $letter->amount);
        $this->assertEquals($new_outgoing_letter['recipient'], $letter->recipient);
        $this->assertEquals($new_outgoing_letter['date'], $letter->date->format('Y-m-d'));

        $this->assertEquals('letter_attachments/outgoing/' . $file->hashName(), $letter->attachments->last()->path);
        Storage::assertExists($letter->attachments->last()->path);
    }

    /** @test */
    public function user_can_not_update_creator_id_of_an_outgoing_letter_in_database()
    {
        $this->signIn();
        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id()
        ]);


        $this->withoutExceptionHandling()
            ->patch(
                "/outgoing-letters/{$letter->id}",
                ['creator_id' => create(User::class)->id]
            )->assertRedirect('/outgoing-letters');

        $this->assertEquals($letter->creator_id, $letter->fresh()->creator_id);
    }

    /** @test */
    public function request_validates_date_field_cannot_be_null()
    {
        try {
            $this->signIn();

            $letter = create(OutgoingLetter::class, 1, [
                'creator_id' => auth()->id()
            ]);

            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['date'=>'']);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('date', $e->errors());
        }

        $this->assertEquals($letter->date, $letter->fresh()->date);
    }

    /** @test */
    public function request_validates_date_field_is_a_valid_date()
    {
        $this->signIn();

        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id()
        ]);

        $invalidDates = [
            '2014-16-14', //16 is not a valid month
            '2017-02-29', //not a leap year
            '2017-04-31', //31 date does not exist in 4th month
            '04-2017-12', // wrong format
        ];

        $validDates = [
            '2018-01-31',
            '2016-02-29',
            '2018-02-28',
            '2018-03-30',
        ];

        foreach ($invalidDates as $date) {
            try {
                $this->withoutExceptionHandling()
                    ->patch("/outgoing-letters/{$letter->id}", ['date'=>$date]);

                $this->fail("Invalid date '{$date}' was not validated");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('date', $e->errors());
                $this->assertEquals($letter->date, $letter->fresh()->date);
            }
        }

        foreach ($validDates as $date) {
            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['date'=>$date,])
                ->assertRedirect('/outgoing-letters');

            $this->assertEquals($date, $letter->fresh()->date->format('Y-m-d'));
        }
    }

    /** @test */
    public function request_validates_date_field_cannot_be_a_future_date()
    {
        $this->signIn();

        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id()
        ]);

        try {
            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['date' => $date = now()->addMonth(1)->format('Y-m-d')]);

            $this->fail("Future date '{$date}' was not validated");
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('date', $e->errors());
            $this->assertEquals($letter->date, $letter->fresh()->date);
        } catch (\Exception $e) {
            $this->fail("Future date '{$date}' was not validated");
        }

        $date = now()->subMonth(1)->format('Y-m-d');
        $this->withoutExceptionHandling()
            ->patch("/outgoing-letters/{$letter->id}", ['date'=>$date])
                ->assertRedirect('/outgoing-letters');

        $this->assertEquals($date, $letter->fresh()->date->format('Y-m-d'));
    }

    /** @test */
    public function request_validates_sender_id_field_cannot_be_null()
    {
        try {
            $this->signIn();
            $letter = create(OutgoingLetter::class, 1, [
                'creator_id' => auth()->id()
            ]);
            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['sender_id'=>'']);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('sender_id', $e->errors());
        }

        $this->assertEquals($letter->sender_id, $letter->fresh()->sender_id);
    }

    /** @test */
    public function request_validates_sender_id_field_must_be_a_existing_user()
    {
        try {
            $this->signIn();

            $letter = create(OutgoingLetter::class, 1, [
                'creator_id' => auth()->id()
            ]);

            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['sender_id' => 4]);

            $this->fail('Failed to validate \'sender_id\' is a valid existing user id');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('sender_id', $e->errors());
        }
        $this->assertEquals($letter->sender_id, $letter->fresh()->sender_id);
    }

    /** @test */
    public function request_validates_subject_field_is_not_null()
    {
        try {
            $this->signIn(create(User::class));
            $letter = create(OutgoingLetter::class, 1, [
                'creator_id' => auth()->id()
            ]);

            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['subject' => '']);

            $this->fail('Empty \'subject\' field cannot be empty.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('subject', $e->errors());
        }

        $this->assertEquals($letter->subject, $letter->fresh()->subject);
    }

    /** @test */
    public function request_validates_subject_field_maxlimit_100()
    {
        try {
            $this->signIn(create(User::class));
            $letter = create(OutgoingLetter::class, 1, [
                'creator_id' => auth()->id()
            ]);

            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['subject' => Str::random(101)]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('subject', $e->errors());
        }

        $this->assertEquals($letter->subject, $letter->fresh()->subject);
    }

    /** @test */
    public function request_validates_description_field_can_be_null()
    {
        $this->signIn();
        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id()
        ]);

        $this->withoutExceptionHandling()
            ->patch("/outgoing-letters/{$letter->id}", ['description' => ''])
            ->assertRedirect('/outgoing-letters');

        $this->assertNull($letter->fresh()->description);
    }

    /** @test */
    public function request_validates_description_field_maxlimit_400()
    {
        $this->signIn();
        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id()
        ]);

        try {
            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['description' => Str::random(401)])
                ->assertRedirect('/outgoing-letters');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('description', $e->errors());
        }

        $this->assertEquals($letter->description, $letter->fresh()->description);
    }

    /** @test */
    public function request_validates_amount_field_can_be_null()
    {
        $this->signIn();

        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id()
        ]);

        $this->withoutExceptionHandling()
            ->patch("/outgoing-letters/{$letter->id}", ['amount'=>''])
            ->assertRedirect('/outgoing-letters');
        $this->assertNull($letter->fresh()->amount);
    }

    /** @test */
    public function request_validates_amount_field_can_not_be_a_string_value()
    {
        try {
            $this->signIn();

            $letter = create(OutgoingLetter::class, 1, [
                'creator_id' => auth()->id()
            ]);

            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['amount' => 'some string']);

            $this->fail('Failed to validate \'amount\' cannot be a string value');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('amount', $e->errors());
            $this->assertEquals($letter->amount, $letter->fresh()->amount);
        }
    }

    /** @test */
    public function outgoing_letters_type_field_cannot_be_updated()
    {
        $this->signIn();
        $letter = create(OutgoingLetter::class, 1, [
            'creator_id' => auth()->id(),
            'type' => 'Bill',
        ]);

        $this->withoutExceptionHandling()
            ->patch("/outgoing-letters/{$letter->id}", ['type'=> 'Notesheet']);

        $this->assertEquals($letter->type, $letter->fresh()->type);
    }

    /** @test */
    public function request_validates_recipient_field_cannot_be_null()
    {
        try {
            $this->signIn();
            $letter = create(OutgoingLetter::class, 1, [
                'creator_id' => auth()->id()
            ]);
            $this->withoutExceptionHandling()
                ->patch("/outgoing-letters/{$letter->id}", ['recipient'=>'']);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('recipient', $e->errors());
        }

        $this->assertEquals($letter->recipient, $letter->fresh()->recipient);
    }
}
