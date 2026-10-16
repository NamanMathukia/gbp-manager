<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ---------------------------------------------------------------
// API v1 (JSON). All backend API routes live under /api/v1.
// ---------------------------------------------------------------
$routes->group('api/v1', static function (RouteCollection $routes): void {
    $routes->get('health', 'Api\V1\Health::index');
});