<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Travel extends Model
{
    protected $fillable = [
        'user_id', 'travel_name', 'start_point', 'end_point', 'capacity', 'start_time', 'passenger_gender', 'repeat' ,'status', 'city_id', 'repeat'
    ];
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function city()
    {
        return $this->belongsTo('App\City');
    }
    public function stations()
    {
        return $this->belongsToMany('App\Station');
    }
}
