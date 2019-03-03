<?php
$router = new \App\Router(new \App\Request());
$router->get('/', 'AdminController@index');
$router->post('/products', 'ProductController@store');
$router->get('/products', 'ProductController@show');
$router->put('/products', 'ProductController@update');
$router->delete('/products', 'ProductController@delete');