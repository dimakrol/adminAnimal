<?php

namespace App\Repositories;


use App\Models\Litter;

class LitterRepository extends Repository
{
    protected $createFromFields = ['given_id', 'prefix', 'bred', 'born', 'kits_amount',  'notes', 'survival_rate'];
    protected $updateFromFields = ['given_id', 'prefix', 'bred', 'born',  'kits_amount', 'notes'];

    /**
     * LitterRepository constructor.
     * @param Litter $litter
     */
    public function __construct(Litter $litter)
    {
        $this->object = $litter;
    }
}