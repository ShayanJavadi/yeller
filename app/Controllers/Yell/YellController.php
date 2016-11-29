<?php

namespace SMA\Controllers\Yell;

use SMA\Models\User;
use SMA\Models\Yell;
use SMA\Models\Follow;
use SMA\Controllers\Controller;
use Respect\Validation\Validator as v;

class YellController extends Controller
{
  public function postYell($request, $response)
  {
    $yell = Yell::create([
      'body' => $request->getParam('yellBody'),
      'user_id' => $_SESSION['user'],
    ]);
  }

  public function deleteYell($request, $response, $args)
  {
    if ($request->isXhr()) {
      $yell = Yell::where('id', $args['id'])->first();
      $yell->delete();
    }
  }

  public function getYells($request, $response, $args)
  {
    $user = User::where('name', $args['name'])->first();
    $following = $user->isFollowing($user);
    $followers = "";
    $friendPosts = new \Illuminate\Database\Eloquent\Collection;
    //grab the user's followers
    $followers = Follow::where('follower_id', $user->id)->get();
    //user posts
    $posts = $user->posts;
    //append an author to each post made by user
    $posts->map(function ($post) {
      $postAuthor = User::where('id', $post->user_id)->first();
      $post['authorName'] = $postAuthor->name;
      $post['authorPicture'] = $postAuthor->avatar;
      return $post;
    });

    $posts = $posts->sortByDesc(function($post)
    {
      return $post->created_at;
    });
    //check if session user is following this person
    $this->container->view->getEnvironment()->addGlobal('isFollowing', $following);
    //session id
    $this->container->view->getEnvironment()->addGlobal('sessionId', $_SESSION['user']);

    $this->container->view->getEnvironment()->addGlobal('User', $user);
    //posts by user
    $this->container->view->getEnvironment()->addGlobal('Posts',$user->posts);
    //feed
    $this->container->view->getEnvironment()->addGlobal('Feed',$posts);
    // TODO: make two twig templates one for user only one for feed
    $this->container->view->getEnvironment()->addGlobal('Followings', $user->follows);
    $this->container->view->getEnvironment()->addGlobal('Followers', $followers);

    return $this->view->render($response, 'templates/Yells.twig');
  }
}
