<?php

namespace Tests\Feature;

use App\OutgoingLetter;
use App\Remark;
use App\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\str;
use Spatie\Permission\Models\Role;

class StoreRemarkTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function guest_cannot_store_remark()
    {
        $this->withExceptionHandling()
            ->post('/outgoing-letters/1/remarks')
            ->assertRedirect('/login');

        $this->assertEquals(0, Remark::count());
    }

    /** @test */
    public function user_cannot_store_remark_if_they_donot_have_permission()
    {
        $role = Role::firstOrCreate(['name' => 'Not a Remarker']);
        $role->revokePermissionTo('create remarks');
        $this->signIn(create(User::class), $role->name);


        $letter = create(OutgoingLetter::class);

        $this->withExceptionHandling()
            ->post("/outgoing-letters/{$letter->id}/remarks")
            ->assertForbidden();
    }

    /** @test */
    public function user_can_create_remark_if_they_are_permitted_to()
    {
        $role = Role::firstOrCreate(['name' => 'Remarker']);
        $role->givePermissionTo('create remarks');
        $this->signIn(create(User::class), $role->name);

        $letter = create(OutgoingLetter::class);

        $this->withoutExceptionHandling()
            ->post("/outgoing-letters/{$letter->id}/remarks", [
                'description'=>'Not received by University'
            ]);

        $this->assertEquals(1, Remark::count());
    }

    /** @test */
    public function request_validates_description_field_cannot_be_null()
    {
        $role = Role::firstOrCreate(['name' => 'Remarker']);
        $role->givePermissionTo('create remarks');
        $this->signIn(create(User::class), $role->name);

        $letter = create(OutgoingLetter::class);
        $remark = ['description'=>''];

        try {
            $this->withoutExceptionHandling()
                ->post("/outgoing-letters/{$letter->id}/remarks", $remark);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('description', $e->errors());
        }

        $this->assertEquals(0, Remark::count());
    }

    /** @test */
    public function request_validates_description_field_minlimit_10()
    {
        $role = Role::firstOrCreate(['name' => 'Remarker']);
        $role->givePermissionTo('create remarks');
        $this->signIn(create(User::class), $role->name);

        $letter = create(OutgoingLetter::class);
        $remark = ['description'=>Str::random(9)];

        try {
            $this->withoutExceptionHandling()
                ->post("/outgoing-letters/{$letter->id}/remarks", $remark);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('description', $e->errors());
        }

        $this->assertEquals(0, Remark::count());
    }

    /** @test */
    public function request_validates_description_field_maxlimit_255()
    {
        $role = Role::firstOrCreate(['name' => 'Remarker']);
        $role->givePermissionTo('create remarks');
        $this->signIn(create(User::class), $role->name);

        $letter = create(OutgoingLetter::class);
        $remark = ['description' => Str::random(256)];

        try {
            $this->withoutExceptionHandling()
                ->post("/outgoing-letters/{$letter->id}/remarks", $remark);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('description', $e->errors());
        }

        $this->assertEquals(0, Remark::count());
    }
}