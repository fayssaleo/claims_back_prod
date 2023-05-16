<?php

namespace App\Modules\NatureOfDamage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NatureOfDamage extends Model
{
    use HasFactory;

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }
    public function Autos()
    {
        return $this->hasMany(Auto::class);
    }
    public function Vessel()
    {
        return $this->hasMany(Vessel::class);
    }
    public function Containers()
    {
        return $this->hasMany(Container::class);
    }
    public function Claims()
    {
        return $this->hasMany(Claim::class);
    }



    protected $fillable = [
        'name',


    ];

}
