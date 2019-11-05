<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    //
    public function games(){
      return $this->belongsToMany(Game::class, 'game_has_channels');
    }
}
