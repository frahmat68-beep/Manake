<?php

namespace App\Observers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class UserObserver
{
    public function created(User $user): void
    {
        if (! schema_table_exists_cached('profiles')) {
            return;
        }

        Profile::firstOrCreate(
            ['user_id' => $user->id],
            ['is_completed' => false]
        );
    }
}
