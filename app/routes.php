<?php
use SMA\Middleware\AuthMiddleware;
use SMA\Middleware\GuestMiddleware;

//route for contact
$app->get('/contact', 'HomeController:getContact')->setName('contact');
//sign in routes
$app->get('/signin', 'AuthController:getSignIn')->setName('auth.signin');
$app->post('/signin', 'AuthController:postSignIn');
//search routes
$app->get('/search', 'SearchController:getSearch')->setName('search');
// sign up routes
$app->get('/signup', 'AuthController:getSignUp')->setName('auth.signup');
$app->post('/signup', 'AuthController:postSignUp');
//get user
$app->get('/user/{name}', 'HomeController:getUser')->setName('user');

//need sign in for these routes
$app->group('', function(){
  $this->get('/', 'HomeController:getSessionProfile')->setName('home');
  //signout routes
  $this->get('/signout', 'AuthController:getSignOut')->setName('auth.signout');
  //use profile
  $this->get('/profile/{name}', 'UserController:getProfile')->setName('profile');
  //update profile
  $this->put('/profile', 'UserController:updateProfile');
  //delete profile
  $this->delete('/profile', 'UserController:deleteProfile');
  //post yell
  $this->post('/yell', 'YellController:postYell')->setName('yell');
  //delete yell
  $this->post('/delete/{id}', 'YellController:deleteYell')->setName('deleteYell');
  //follow/unfollow
  $this->post('/profile/{name}/follow', 'FollowController:follow')->setName('follow');
  $this->post('/profile/{name}/unfollow', 'FollowController:unfollow')->setName('unfollow');
  //update profile
  $this->get('/update-profile', 'UserController:getUpdateProfile')->setName('updateProfile');
  $this->post('/update-profile', 'UserController:postUpdateProfile');
  //get followers
  $this->get('/profile/{name}/followers', 'UserController:getFollowers')->setName('followers');
  //get followings
  $this->get('/profile/{name}/followings', 'UserController:getFollowings')->setName('followings');
  //get yells
  $this->get('/profile/{name}/yells', 'YellController:getYells')->setName('yells');
})->add(new AuthMiddleware($container));
 ?>
