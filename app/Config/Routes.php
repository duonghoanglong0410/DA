<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/trang-chu', 'Home::index');
$routes->get('/scan', 'Home::getScan');

