<?php

namespace App\Models;

use App\Traits\ArchivableTrait;
use App\Traits\EventsTrait;
//use App\Traits\ImageAbleTrait;
use App\Traits\CloudinaryImageAbleTrait;
use App\Traits\WeightableTrait;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pedigree extends Model
{
    use CloudinaryImageAbleTrait, ArchivableTrait, EventsTrait, WeightableTrait {
        WeightableTrait::getWeightSlugAttribute as getWSA;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rabbit_breeder_id', 'level', 'prefix', 'name', 'custom_id', 'day_of_birth', 'aquired', 'color', 'weight', 'breed', 'sex', 'image', 'notes', 'rabbit_breeders_id', 'rabbit_kit_id', 'legs', 'registration_number', 'champion_number'
    ];

    protected $imagesFolder = 'pedigree';

    protected $appends = [

        'weight_slug',
        'weight_unit',
        'css',
        'user_id',
        'rabbit'

    ];

    /**
     * Breeder this pedigree is about
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function breeder()
    {
        return $this->belongsTo(RabbitBreeder::class,'rabbit_breeder_id');
    }

    /**
     * Breeder representing current block in pedigree
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function meBreeder()
    {
        return $this->belongsTo(RabbitBreeder::class,'rabbit_breeders_id');
    }

    /**
     * Rabbit kit about which this pedigree is
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kit()
    {
        return $this->belongsTo(RabbitKit::class, 'rabbit_kit_id');
    }

    /**
     * Is this a record about the kit?
     * @return bool
     */
    public function isKitHimself()
    {
        return $this->level == 'me';
    }
	
	/**
     * Is this a record about the kit?
     * @return bool
     */
    public function isKitNull()
    {
        return $this->rabbit_kit_id == null;
    }

    public function getCssAttribute()
    {
        if($this->sex =='buck') {
            return [
                'icon'  =>  "fa fa-mars",
                'color' =>  "bg-aqua",
                'img'   =>  'icon-male.png'
            ];
        }

        return [
            'icon'  =>  "fa fa-venus",
            'color' =>  "bg-maroon",
            'img'   =>  'icon-female.png'
        ];
    }

    public function getUserIdAttribute()
    {
        return $this->breeder? $this->breeder->user_id : $this->kit->user_id;
    }

    public function setDayOfBirthAttribute($acquired)
    {
        if ($acquired != null && $acquired != '11/30/-0001' ) {
            $this->attributes['day_of_birth'] = Carbon::createFromFormat(User::getDateFormatPHPSafe(), $acquired)
                                                            ->toDateString();
        } else {
            $this->attributes['day_of_birth'] = null;
        }
    }

    public function getDayOfBirthAttribute($acquired)
    {
        if ($acquired) {
            return Carbon::createFromFormat('Y-m-d', $acquired)->format(User::getDateFormatPHPSafe());
        }
        return null;
    }

    public function setAquiredAttribute($acquired)
    {
        if ($acquired) {
            $this->attributes['aquired'] = Carbon::createFromFormat(User::getDateFormatPHPSafe(), $acquired)
                                                    ->toDateString();
        } else {
            $this->attributes['aquired'] = null;
        }
    }

    public function getAquiredAttribute($acquired)
    {
        if ($acquired) {
            return Carbon::createFromFormat('Y-m-d', $acquired)->format(User::getDateFormatPHPSafe());
        }
        return null;
    }

    public function getWeightUnits()
    {
        $base = $this->kit ?: $this->breeder;
        return $base->getWeightUnits();
    }

    public function getWeightSlugAttribute()
    {
        $base = $this->isKitHimself() ? $this->kit : $this->meBreeder;
        return $base ? $base->weight_slug : $this->getWSA();
    }

    public function getUserAttribute()
    {
        return ($kit = $this->kit) ? $this->kit->user : $this->breeder->user;
    }

    public function getWeightUnitAttribute()
    {
    //         $base = $this->isKitHimself() ? $this->kit : $this->meBreeder;
    // -        return $base ? $base->user->general_weight_units : null;

        if($this->user){
            return $this->user->general_weight_units;
        }
        return null;

    }

    public function getRabbitAttribute()
    {
        return 0;
    }
}
