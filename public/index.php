<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../resources/route.php';


$application = \App\Application::getInstance();

$conn = $application->getDoctrine();

$queryBuilder = $conn->createQueryBuilder();
$queryBuilder
	->select('*')
	->from('products');

$products = $conn->executeQuery($queryBuilder->getSQL());
while($product = $products->fetch()){
	//var_dump($product);
}


