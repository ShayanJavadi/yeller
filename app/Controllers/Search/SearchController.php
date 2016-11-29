<?php

namespace SMA\Controllers\Search;

use SMA\Models\User;
use SMA\Models\Yell;
use SMA\Controllers\Controller;
use Slim\views\Twig as View;

class SearchController extends Controller
{

  public function getSearch($request, $response)
  {
    //search types that are allowed
    $searchTypes = array('user');
    //check get values to make sure they are the right type or are not empty
    if ((!in_array($_GET['searchType'], $searchTypes) || $_GET['searchInput'] == null)) {
      return $response->withRedirect($this->container->router->pathFor('home'));
    }
      //get the user
      $user = User::where('name', ucfirst($_GET['searchInput']))->first();
      if (count($user) != 0 ) {
        //redirect if found
        return $response->withRedirect($this->router->pathfor('profile', ['name' => $user->name]));
      }
      $this->flash->addMessage('error', 'User '. $_GET['searchInput'] . ' not found');
      return $response->withRedirect($this->container->router->pathFor('home'));
  }

  public function postSearch($request, $response)
  {
    return $this->view->render($response, 'home.twig');
  }

}


 ?>
