<?php
use CodeIgniter\Router\RouteCollection;
$routes->get('/', 'Ai::index');
$routes->get('/v1', 'Ai::v1'); // Konsep Editorial
$routes->get('/v2', 'Ai::v2'); // Konsep HUD
$routes->post('api/generate', 'Ai::generate');
$routes->get('api/history', 'Ai::history');
