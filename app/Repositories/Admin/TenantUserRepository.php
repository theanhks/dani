<?php

namespace App\Repositories\Admin;

use App\Models\Admin\User;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Admin\Contracts\TenantUserRepositoryInterface;

class TenantUserRepository extends BaseRepository implements TenantUserRepositoryInterface
{
    public function model(): string
    {
        return User::class;
    }
}
