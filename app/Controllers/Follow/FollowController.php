<?php

namespace SMA\Controllers\Follow;

use SMA\Models\User;
use SMA\Models\Yell;
use SMA\Models\Follow;
use SMA\Controllers\Controller;
use Respect\Validation\Validator as v;

class FollowController extends Controller
{
  public function follow($request, $response, $args)
  {
      $user = User::where('name', $args['name'])->first();
      $follow = Follow::create([
        'user_id' => $_SESSION['user'],
        'follower_id' => $user->id,
      ]);
      return $response;
  }

  public function unfollow($request, $response, $args)
  {
      $user = User::where('name', $args['name'])->first();
      $follow = Follow::where('user_id', $_SESSION['user'])->where('follower_id', $user->id)->first()->delete();
      return $response;
  }
}
