<?php
use core\Router;

$router = new Router();

$router->get('/', 'HomeController@index');

$router->get('/signin', 'LoginController@signin');
$router->post('/signin', 'LoginController@signinAction');

$router->get('/signup', 'LoginController@signup');
$router->post('/signup', 'LoginController@signupAction');

$router->get('/logout', 'LoginController@logout');

$router->post('/post/new', 'PostController@new');

$router->get('/profile/{id}/photos', 'ProfileController@photos');
$router->get('/profile/{id}/friends', 'ProfileController@friends');
$router->get('/profile/{id}/follow', 'ProfileController@follow');
$router->get('/profile/{id}', 'ProfileController@index');

$router->get('/profile', 'ProfileController@index');

$router->get('/friends', 'ProfileController@friends');

$router->get('/photos', 'ProfileController@photos');

// $router->get('/search', '');
// $router->get('/settings', '');