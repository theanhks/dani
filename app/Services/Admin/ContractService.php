<?php

namespace App\Services\Admin;

use App\Repositories\Admin\ContractRepository;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ContractService extends BaseService
{
    public function __construct(ContractRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Calculate expiration date based on contract date and effective period
     */
    protected function calculateExpirationDate($contractDate, $effectivePeriodValue, $effectivePeriodType)
    {
        $date = Carbon::parse($contractDate);
        
        // Cast to integer to ensure Carbon receives the correct type
        $value = (int) $effectivePeriodValue;
        
        if ($effectivePeriodType === 'month') {
            $date->addMonths($value);
        } else {
            $date->addDays($value);
        }
        
        return $date->format('Y-m-d');
    }

    /**
     * Create a new contract
     */
    public function create($attributes = [])
    {
        // Remove expiration_date from input (will be calculated)
        unset($attributes['expiration_date']);
        
        // Calculate expiration_date
        if (isset($attributes['contract_date']) && isset($attributes['effective_period_value']) && isset($attributes['effective_period_type'])) {
            $attributes['expiration_date'] = $this->calculateExpirationDate(
                $attributes['contract_date'],
                $attributes['effective_period_value'],
                $attributes['effective_period_type']
            );
        }
        
        return $this->repository->create($attributes);
    }

    /**
     * Update a contract
     */
    public function update($id, $attributes = [])
    {
        // Remove expiration_date from input (will be recalculated)
        unset($attributes['expiration_date']);
        
        // Calculate expiration_date
        if (isset($attributes['contract_date']) && isset($attributes['effective_period_value']) && isset($attributes['effective_period_type'])) {
            $attributes['expiration_date'] = $this->calculateExpirationDate(
                $attributes['contract_date'],
                $attributes['effective_period_value'],
                $attributes['effective_period_type']
            );
        }
        
        return $this->repository->update($id, $attributes);
    }

    /**
     * Get paginated contracts with search
     */
    public function getPaginate(): LengthAwarePaginator
    {
        $search = request()->get('search');
        if ($search) {
            $this->repository->whereColumnsLike($search, ['contract_number', 'company_name']);
        }
        
        return $this->repository->getPaginate();
    }
}

