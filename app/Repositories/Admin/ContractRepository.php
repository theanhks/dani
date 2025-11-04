<?php

namespace App\Repositories\Admin;

use App\Models\Admin\Contract;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Admin\Contracts\ContractRepositoryInterface;

class ContractRepository extends BaseRepository implements ContractRepositoryInterface
{
    public function model(): string
    {
        return Contract::class;
    }
}

