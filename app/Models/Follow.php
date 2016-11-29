<?php

namespace SMA\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
  protected $fillable = [
    'follower_id',
    'user_id',
  ];


  public function user()
  {
    return $this->belongsTo('SMA\Models\User');
  }



}


 ?>
