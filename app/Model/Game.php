<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    //
    public function channels(){
      return $this->belongsToMany(Channel::class,'game_has_channels');
    }
}
