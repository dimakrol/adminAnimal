<?php

namespace App\Repositories;


use App\Models\RabbitKit;

class RabbitKitRepository extends Repository

{
    protected $createFromFields = ['given_id', 'prefix', 'sex', 'current_weight', 'color', 'litter_id', 'image', 'notes', 'user_id'];
    protected $updateFromFields = ['given_id', 'prefix', 'sex', 'weight', 'weight_date', 'current_weight', 'color', 'litter_id', 'image', 'notes'];

    /**
     * RabbitKitRepository constructor.
     * @param RabbitKit $kit
     */
    public function __construct(RabbitKit $kit)
    {
        $this->object = $kit;
    }

}