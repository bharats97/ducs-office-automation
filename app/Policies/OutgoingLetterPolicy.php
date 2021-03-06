<?php

namespace App\Policies;

use App\Models\OutgoingLetter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OutgoingLetterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any outgoing letters.
     *
     * @param  \App\Models\User  $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('outgoing letters:view');
    }

    /**
     * Determine whether the user can view the outgoing letter.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\OutgoingLetter  $letter
     *
     * @return mixed
     */
    public function view(User $user, OutgoingLetter $letter)
    {
        return $user->can('outgoing letters:view');
    }

    /**
     * Determine whether the user can create outgoing letters.
     *
     * @param  \App\Models\User  $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('outgoing letters:create');
    }

    /**
     * Determine whether the user can update the outgoing letter.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\OutgoingLetter  $letter
     *
     * @return mixed
     */
    public function update(User $user, OutgoingLetter $letter)
    {
        return $user->can('outgoing letters:edit')
            && (int) $letter->creator_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the outgoing letter.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\OutgoingLetter  $letter
     *
     * @return mixed
     */
    public function delete(User $user, OutgoingLetter $letter)
    {
        return $user->can('outgoing letters:delete')
            && (int) $letter->creator_id === (int) $user->id;
    }
}
