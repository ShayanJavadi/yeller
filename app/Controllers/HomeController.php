<?php

namespace SMA\Controllers;

use SMA\Models\User;
use SMA\Models\Yell;
use SMA\Models\Follow;
use SMA\Controllers\Controller;
use Slim\views\Twig as View;

class HomeController extends Controller
{

  public function getSessionProfile($request, $response)
  {
    $recommendationsId = null;
    //grab current user
    $user = User::where('id', $_SESSION['user'])->first();
    $following = $user->isFollowing($user);
    //make these two so no error thrown if empty
    $followers = "";
    $friendPosts = new \Illuminate\Database\Eloquent\Collection;

    //grab the user's followers
    $followers = Follow::where('follower_id', $user->id)->get();
    //user posts
    $posts = $user->posts;
    //get the posts made by people user is following, and their ids
    foreach ($user->follows as $peopleUserFollowing) {
      $follow = User::where('id', $peopleUserFollowing->follower_id)->first();
      //for when getting recommendations and comparing our list to see if user already follows or not
      $followingIds[] = $follow->id;
      //posts by people that user is following
      //what do I name this variable.. -_-
      $friendPosts = $friendPosts->merge($follow->posts);
    }

    //append user id to the following ids so our recs dont end up recommending the user as a follow
    $followingIds[] = $_SESSION['user'];

    //get people that the user's followings are following, unless the user is already following them
    $recommendationFollows = Follow::orderByRaw(('RAND()'))->whereIn('user_id', $followingIds)->whereNotIn('follower_id', $followingIds )->take(5)->get();
    foreach ($recommendationFollows as $rec) {
      $recommendationsId[] = $rec->follower_id;
    }

    //grab the models of recommendations
    if ($recommendationsId != null) {
      $recommendations = User::orderByRaw(('RAND()'))->whereIn('id', array_unique($recommendationsId))->take(5)->get();
    }else {
      $recommendations = User::orderByRaw(('RAND()'))->whereNotIn('id', $followingIds)->take(5)->get();
    }
    //create user feed
    //
    //append author to each post made by friendllowers
    $friendPosts->map(function ($post) {
      $postAuthor = User::where('id', $post->user_id)->first();
      $post['authorName'] = $postAuthor->name;
      $post['authorPicture'] = $postAuthor->avatar;
      return $post;
    });

    //append an author to each post made by user
    $posts->map(function ($post) {
      $postAuthor = User::where('id', $post->user_id)->first();
      $post['authorName'] = $postAuthor->name;
      $post['authorPicture'] = $postAuthor->avatar;
      return $post;
    });

    $feed = $friendPosts->merge($posts);
    $feed = $feed->sortByDesc(function($post)
    {
      return $post->created_at;
    });
    //check if session user is following this person
    $this->container->view->getEnvironment()->addGlobal('isFollowing', $following);
    //session id
    $this->container->view->getEnvironment()->addGlobal('sessionId', $_SESSION['user']);

    $this->container->view->getEnvironment()->addGlobal('User', $user);
    //posts by user
    $this->container->view->getEnvironment()->addGlobal('Posts', $user->posts);
    //feed
    $this->container->view->getEnvironment()->addGlobal('Feed', $feed);
    $this->container->view->getEnvironment()->addGlobal('Recommendations', $recommendations);

    $this->container->view->getEnvironment()->addGlobal('Followings', $user->follows);
    $this->container->view->getEnvironment()->addGlobal('Followers', $followers);
    //get followers
    //get following
    //get tweets
    return $this->view->render($response, 'home.twig');
  }

  public function getContact($request, $response)
  {

    // $user = User::where('email', 'shayan@gmail.com')->first();
    // var_dump($user->email);
    // die();

    return $this->view->render($response, 'contact.twig');
  }
}


 ?>
