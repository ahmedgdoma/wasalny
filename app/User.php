<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username','phone', 'password','gender', 'have_car', 'car_number', 'car_model', 'car_color', 'type', 'img', 'city_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function travels()
    {
        return $this->hasMany('App\Travel');
    }

    public function city(){
        return $this->belongsTo('App\City');
    }
}
