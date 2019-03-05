<?php
$router = \App\Router::getInstance();

//index page
$router->get('/', 'AdminController@index');

//products
$router->post('/products', 'ProductController@store');
$router->get('/products', 'ProductController@index');
$router->get('/products/([0-9]+)', 'ProductController@show');
$router->put('/products', 'ProductController@update');
$router->delete('/products', 'ProductController@delete');
$router->delete('/products/mass-delete', 'ProductController@massDelete');
$router->post('/products/mass-update', 'ProductController@massUpdate');

//reviews
$router->delete('/reviews', 'ReviewController@delete');
$router->post('/reviews/mass-update', 'ReviewController@massUpdate');

//sections
$router->get('/sections', 'SectionController@index');
$router->post('/sections', 'SectionController@store');
$router->put('/sections', 'SectionController@update');
$router->get('/sections/([0-9]+)', 'SectionController@show');
$router->delete('/sections', 'SectionController@delete');
$router->delete('/sections/mass-delete', 'SectionController@massDelete');
$router->post('/sections/mass-update', 'SectionController@massUpdate');