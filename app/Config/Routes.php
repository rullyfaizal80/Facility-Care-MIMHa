<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// --- ROUTE AUTENTIKASI ---
$routes->get('/', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('login/process', 'Auth::process');
$routes->get('logout', 'Auth::logout');
$routes->get('unauthorized', 'Auth::unauthorized');

// --- ROUTE ADMIN ---
// Hanya role 'admin' yang bisa masuk ke group url '/admin/...'
$routes->group('admin', ['filter' => 'roleFilter:admin'], static function ($routes) {
    $routes->get('dashboard', function() {
        return '<h1>Dashboard Admin</h1> Halo ' . session()->get('nama_lengkap') . '! <br> <a href="/logout">Logout</a>';
    });
});

// --- ROUTE PIMPINAN ---
$routes->group('pimpinan', ['filter' => 'roleFilter:pimpinan'], static function ($routes) {
    $routes->get('dashboard', function() {
        return '<h1>Dashboard Pimpinan</h1> Halo ' . session()->get('nama_lengkap') . '! <br> <a href="/logout">Logout</a>';
    });
});

// --- ROUTE PELAPOR ---
$routes->group('pelapor', ['filter' => 'roleFilter:pelapor'], static function ($routes) {
    $routes->get('dashboard', function() {
        return '<h1>Dashboard Pelapor</h1> Halo ' . session()->get('nama_lengkap') . '! <br> <a href="/logout">Logout</a>';
    });
});

// --- ROUTE CARAKA ---
$routes->group('caraka', ['filter' => 'roleFilter:caraka'], static function ($routes) {
    $routes->get('dashboard', function() {
        return '<h1>Dashboard Caraka</h1> Halo ' . session()->get('nama_lengkap') . '! <br> <a href="/logout">Logout</a>';
    });
});