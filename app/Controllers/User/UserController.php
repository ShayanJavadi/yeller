<?php

namespace SMA\Controllers\User;

use SMA\Models\User;
use SMA\Models\Yell;
use SMA\Models\Follow;
use SMA\Controllers\Controller;
use Respect\Validation\Validator as v;

class UserController extends Controller
{

  public function getProfile($request, $response, $args)
  {

    //grab profile user
    $user = User::where('name', $args['name'])->first();
    //grab session user for recs
    $sessionUser = User::where('id', $_SESSION['user'])->first();
    $following = $user->isFollowing($user);
    //make these two so no error thrown if empty
    $followers = "";
    $friendPosts = new \Illuminate\Database\Eloquent\Collection;
    //grab the user's followers
    $followers = Follow::where('follower_id', $user->id)->get();
    //user posts
    $posts = $user->posts;
    //get the posts made by friendllowers
    foreach ($user->follows as $follow) {
      $follow = User::where('id', $follow->follower_id)->first();
      //posts by people that user is following
      //what do I name this variable.. -_-
      //friends = people you are following
      $friendPosts = $friendPosts->merge($follow->posts);
    }

    foreach ($sessionUser->follows as $follow) {
      $follow = User::where('id', $follow->follower_id)->first();
      //posts by people that user is following
      $followingIds[] = $follow->id;
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
    //add them together and sort so newest is at top
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
    $this->container->view->getEnvironment()->addGlobal('Posts',$user->posts);
    $this->container->view->getEnvironment()->addGlobal('Recommendations', $recommendations);
    //feed
    $this->container->view->getEnvironment()->addGlobal('Feed',$feed);
    $this->container->view->getEnvironment()->addGlobal('Followings', $user->follows);
    $this->container->view->getEnvironment()->addGlobal('Followers', $followers);

    return $this->view->render($response, 'templates/profile.twig');
  }

  public function getUpdateProfile($request, $response)
  {
    //grab current user
    $user = User::where('id', $_SESSION['user'])->first();
    $following = $user->isFollowing($user);

    $followers = "";
    $friendPosts = new \Illuminate\Database\Eloquent\Collection;
    //grab the user's followers
    $followers = Follow::where('follower_id', $user->id)->get();
    //user posts
    $posts = $user->posts;
    //get the posts made by friendllowers
    foreach ($user->follows as $follow) {
      $follow = User::where('id', $follow->follower_id)->first();
      //posts by people that user is following
      //what do I name this variable.. -_-
      $friendPosts = $follow->posts;
    }


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
    $this->container->view->getEnvironment()->addGlobal('Posts',$user->posts);
    //feed
    $this->container->view->getEnvironment()->addGlobal('Feed',$feed);
    // TODO: make two twig templates one for user only one for feed
    $this->container->view->getEnvironment()->addGlobal('Followings', $user->follows);
    $this->container->view->getEnvironment()->addGlobal('Followers', $followers);
    return $this->view->render($response, 'templates\edit-profile.twig');
  }

  public function postUpdateProfile($request, $response)
  {
    //grab user
    $user = User::where('id', $_SESSION['user'])->first();

    //if file is uploaded update it
    if (file_exists($_FILES['avatar']['tmp_name']) || is_uploaded_file($_FILES['avatar']['tmp_name'])) {
      //image location

      $userDirectory = "/TwitterClone/public/images/" . $_SESSION['user'] . "/";
      $user->avatar = $userDirectory . "avatar.jpg";
      $user->save();
      //create directory if it is not created already
      if(!is_dir("images/" .  $_SESSION['user'])) {
        mkdir("images/" . $_SESSION['user'],  0777, true);
      }

      //delete if already exists
      if(file_exists($user->avatar)) {
        unlink($user->avatar);
      }
      //move file to directory
      move_uploaded_file($_FILES['avatar']['tmp_name'], "images/". $_SESSION['user'] ."/avatar.jpg");
    }

    if (file_exists($_FILES['banner']['tmp_name']) || is_uploaded_file($_FILES['banner']['tmp_name'])) {
      //image location
      $userDirectory = "/TwitterClone/public/images/" . $_SESSION['user'] . "/";
      $user->banner = $userDirectory . "banner.jpg";
      $user->save();
      //create directory if it is not created already
      if(!is_dir("images/" .  $_SESSION['user'])) {
        mkdir("images/" . $_SESSION['user'],  0777, true);
      }
      //delete if already exists
      if(file_exists($user->banner)) {
        unlink($user->banner);
      }
      //move file to directory
      move_uploaded_file($_FILES['banner']['tmp_name'], "images/". $_SESSION['user'] ."/banner.jpg");
    }
    //update if fields are not empty
    if ($request->getParam('name') != null) {
      $user->name = $request->getParam('name');
    }
    if ($request->getParam('about') != null) {
      $user->about = $request->getParam('about');
    }
    //save
    $user->save();
    //redirect
    return $response->withRedirect($this->router->pathfor('home'));
  }

  public function getFollowers($request, $response, $args)
  {
    //grab current user
    $user = User::where('name', $args['name'])->first();
    //get people who are following user
    $following = $user->isFollowing($user);
    //initialize so no error thrown if no followers
    $followers = "";
    //get requested user
    $user = User::where('name', $args['name'])->first();
    //get followers
    $followerIds = Follow::where('follower_id', $user->id)->orderBy('created_at', 'DESC')->get();
    //counter for posts
    $userPostsCounter = Yell::where('user_id', $user->id)->count();
    //grab users, append following since
    foreach ($followerIds as $follower) {
      $followerUserModel = User::where('id', $follower->user_id)->first();
      $followerUserModel['followingSince'] = $follower->created_at->diffForHumans();
      $followers[] = $followerUserModel;
    }

    //pass globals
    $this->container->view->getEnvironment()->addGlobal('Followers', $followers);
    $this->container->view->getEnvironment()->addGlobal('isFollowing', $following);
    $this->container->view->getEnvironment()->addGlobal('sessionId', $_SESSION['user']);
    $this->container->view->getEnvironment()->addGlobal('User', $user);
    $this->container->view->getEnvironment()->addGlobal('UserPosts', $userPostsCounter);
    $this->container->view->getEnvironment()->addGlobal('Followings', $user->follows);
    //redirect
    return $this->view->render($response, 'templates/followers.twig');
  }
  public function getFollowings($request, $response, $args)
  {
    //grab current user
    $user = User::where('name', $args['name'])->first();
    //get people who are following user
    $following = $user->isFollowing($user);
    //initialize so no error thrown if no followers
    $followers = "";
    //get requested user
    $user = User::where('name', $args['name'])->first();
    //counter for post counter
    $userPostsCounter = Yell::where('user_id', $user->id)->orderBy('created_at', 'DESC')->count();
    $followingIds = Follow::where('user_id', $user->id)->get();
    //counter for follower counter
    $followersCount = Follow::where('follower_id', $user->id)->count();
    //grab users, append following since
    foreach ($followingIds as $follower) {
      $followerUserModel = User::where('id', $follower->follower_id)->first();
      $followerUserModel['followingSince'] = $follower->created_at->diffForHumans();
      $followings[] = $followerUserModel;
    }
    //pass globals
    $this->container->view->getEnvironment()->addGlobal('Followings', $followings);
    $this->container->view->getEnvironment()->addGlobal('isFollowing', $following);
    $this->container->view->getEnvironment()->addGlobal('Followers', $followersCount);
    $this->container->view->getEnvironment()->addGlobal('sessionId', $_SESSION['user']);
    $this->container->view->getEnvironment()->addGlobal('User', $user);
    $this->container->view->getEnvironment()->addGlobal('UserPosts', $userPostsCounter);
    //redirect
    return $this->view->render($response, 'templates/followings.twig');
  }


}


 ?>
