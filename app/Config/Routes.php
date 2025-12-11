<?php
use CodeIgniter\Router\RouteCollection;
$routes->get('/', 'Ai::index');
$routes->get('/v1', 'Ai::v1'); // Konsep Editorial
$routes->get('/v2', 'Ai::v2'); // Konsep HUD
$routes->get('ai/history', 'Ai::history');
$routes->post('ai/generate', 'Ai::generate');
$routes->delete('/ai/clear', 'Ai::clear');
