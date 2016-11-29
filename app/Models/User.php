<?php

namespace SMA\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
  protected $fillable = [
    'email',
    'name',
    'password',
  ];

  public function setPassword($password)
  {
    $this->update([
      'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);
  }

  public function posts()
  {
    return $this->hasMany('SMA\Models\Yell')->orderBy('created_at', 'desc');
  }

  public function follows()
  {
    return $this->hasMany('SMA\Models\Follow');
  }

  public function isFollowing(User $user)
  {
      return (bool) Follow::where('follower_id', $user->id)->where('user_id', $_SESSION['user'])->count();
  }



}


 ?>
