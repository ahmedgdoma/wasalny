<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [
        'station_name',
    ];

    public function travel()
    {
        return $this->belongsTo('App\Travel');
    }
}
