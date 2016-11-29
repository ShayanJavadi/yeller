<?php

namespace SMA\Models;

use Illuminate\Database\Eloquent\Model;

class Yell extends Model
{
  protected $fillable = [
    'body',
    'user_id',
  ];


  public function user()
  {
    return $this->belongsTo('SMA\Models\User');
  }
}


 ?>
