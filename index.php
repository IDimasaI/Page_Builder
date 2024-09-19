<?php
if (isset($_GET['i']) && in_array($_GET['i'], ['1', '2', '3'])) {
  $path = parse_url($_SERVER['REQUEST_URI'])['path'];
  header('location:' . $_SERVER['HOST'] . $path);
}
require 'process/OOP/autoloader.php';
include 'routers/controllers.php';
$router = new Router();

$collection = include 'routers/collection.php';

foreach ($collection as $pattern => $handler) {
  $router->addRoute($pattern, $handler);
}

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$grups_home=[
  '/About',
  '/view_news',
];
if ($url === '/') {
  $url = 'Home';
}
if(in_array($url,$grups_home)){
  $url='Home'.$url;
}

$router->handleRequest($url);
