<?php

namespace App\Models\Traits\LikeBreeder;

use App\Models\RabbitBreeder;

trait RabbitBreederLikeBreeder
{
    use LikeBreeder;

    public function likeBreeder()
    {
        $likeness = new RabbitBreeder();
        foreach (['prefix', 'name', 'breed', 'cage', 'tattoo', 'sex', 'color', 'weight', 'aquired', 'date_of_birth', 'notes']
                 as $attribute) {
            $likeness->$attribute = $this->$attribute;
        }
        $likeness->father_id = 0;
        $likeness->mother_id = 0;
        $likeness->category_id = 1;
        $likeness->image = $this->image ? $this->image['name'] : null;

        return $likeness;
    }
}
