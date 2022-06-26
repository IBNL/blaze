<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class DoubleGame extends Model
{

    protected $table = "double_game";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_blaze',
        'color',
        'roll',
        'server_seed',
        'created_at_blaze'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<dateTime>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function createdAtBlaze(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => str_replace(['T','Z'], ' ',$value),
        );
    }

}
