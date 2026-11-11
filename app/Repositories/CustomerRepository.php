<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    public function getAllActive(): Collection
    {
        return Customer::where('is_active', true)->orderBy('name', 'asc')->get();
    }

    public function findById(int $id): ?Customer
    {
        return Customer::find($id);
    }
}
