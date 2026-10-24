<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ManageNexAdmin extends Command
{
    protected $signature = 'nex:admin {email : Email address of the existing account} {--revoke : Remove NEX admin access}';
    protected $description = 'Grant or revoke NEX moderation access for an existing account';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('No account found with that email address.');
            return self::FAILURE;
        }

        $grant = !$this->option('revoke');
        $user->forceFill(['is_admin' => $grant])->save();

        $this->info(($grant ? 'NEX admin access granted to ' : 'NEX admin access revoked from ') . $user->email . '.');

        return self::SUCCESS;
    }
}
