<?php
use CodeIgniter\Router\RouteCollection;
$routes->get('/', 'Ai::index');
$routes->post('api/generate', 'Ai::generate');
$routes->get('api/history', 'Ai::history');
