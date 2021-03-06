<?php

namespace App\Policies;

use App\Models\Scholar;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScholarProfilePolicy
{
    use HandlesAuthorization;

    public function addCoursework($user, Scholar $scholar)
    {
        return get_class($user) === User::class &&
            $user->isSupervisor() &&
            (int) $user->id === (int) $scholar->currentSupervisor->id;
    }

    public function markCourseworkCompleted($user, Scholar $scholar)
    {
        return get_class($user) === User::class && $user->can('phd course work:mark completed');
    }

    public function addAdvisoryMeeting($user, Scholar $scholar)
    {
        return get_class($user) === User::class &&
            $user->isSupervisor() &&
            (int) $user->id === (int) $scholar->currentSupervisor->id;
    }

    public function manageAdvisoryCommittee($user, Scholar $scholar)
    {
        return get_class($user) === User::class &&
            $user->isSupervisor() &&
            (int) $user->id === (int) $scholar->currentSupervisor->id;
    }
}
