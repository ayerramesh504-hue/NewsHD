<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('sitemap', 'Sitemap::index');
$routes->get('sitemap.xml', 'Sitemap::index');

$routes->get('article/(:any)', 'Article::view/$1');
$routes->get('category/(:any)', 'Category::view/$1');
$routes->get('search', 'Search::index');
$routes->get('nepse', 'Nepse::index');
$routes->match(['GET', 'POST'], 'contact', 'Contact::index');

$routes->match(['GET', 'POST'], 'login', 'Auth::login');
$routes->match(['GET', 'POST'], 'register', 'Auth::register');
$routes->get('logout', 'Auth::logout');
$routes->get('verify/(:any)', 'Auth::verify/$1');
$routes->match(['GET', 'POST'], 'forgot-password', 'Auth::forgotPassword');
$routes->match(['GET', 'POST'], 'profile', 'Profile::index');

$routes->get('api/search', 'Api\SearchApi::index');
$routes->get('api/nepse', 'Api\NepseApi::index');
$routes->get('api/feed', 'Api\FeedApi::index');
$routes->post('api/bookmarks', 'Api\BookmarksApi::index');
$routes->post('api/newsletter', 'Api\NewsletterApi::index');
$routes->post('api/admin/upload', 'Admin\Articles::upload', ['filter' => 'adminAccess']);

// Admin area (admin only)
$routes->group('admin', ['filter' => 'adminAccess'], static function ($routes) {
    $routes->get('/', 'Admin\AdminController::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');

    $routes->get('articles', 'Admin\Articles::index');
    $routes->get('articles/create', 'Admin\Articles::create');
    $routes->get('articles/edit/(:num)', 'Admin\Articles::edit/$1');
    $routes->get('articles/preview/(:num)', 'Admin\Articles::preview/$1', ['filter' => 'adminOnly']);
    $routes->post('articles/save', 'Admin\Articles::save');
    $routes->post('articles/delete/(:num)', 'Admin\Articles::delete/$1');
    $routes->post('articles/status/(:num)', 'Admin\Articles::status/$1', ['filter' => 'adminOnly']);
    $routes->post('articles/upload', 'Admin\Articles::upload');

    $routes->get('categories', 'Admin\Categories::index', ['filter' => 'adminOnly']);
    $routes->post('categories/save', 'Admin\Categories::save', ['filter' => 'adminOnly']);
    $routes->post('categories/delete/(:num)', 'Admin\Categories::delete/$1', ['filter' => 'adminOnly']);

    $routes->get('users', 'Admin\Users::index', ['filter' => 'adminOnly']);
    $routes->post('users/save', 'Admin\Users::save', ['filter' => 'adminOnly']);

    $routes->get('messages', 'Admin\Messages::index', ['filter' => 'adminOnly']);
    $routes->post('messages/action/(:num)', 'Admin\Messages::action/$1', ['filter' => 'adminOnly']);

    $routes->get('settings', 'Admin\Settings::index', ['filter' => 'adminOnly']);
    $routes->post('settings/save', 'Admin\Settings::save', ['filter' => 'adminOnly']);
});

// Author area (author only)
$routes->group('author', ['filter' => 'authorAccess'], static function ($routes) {
    $routes->get('/', 'Author\AuthorController::index');
    $routes->get('dashboard', 'Author\Dashboard::index');

    $routes->get('articles', 'Admin\Articles::index');
    $routes->get('articles/create', 'Admin\Articles::create');
    $routes->get('articles/edit/(:num)', 'Admin\Articles::edit/$1');
    $routes->post('articles/save', 'Admin\Articles::save');
    $routes->post('articles/delete/(:num)', 'Admin\Articles::delete/$1');
    $routes->post('articles/upload', 'Admin\Articles::upload');
});

// Dedicated staff login paths. Configure them through .env for each deployment.
$routes->match(['GET', 'POST'], env('security.adminLoginPath', 'secure-admin-login'), 'Admin\AdminController::login');
$routes->match(['GET', 'POST'], env('security.authorLoginPath', 'secure-author-login'), 'Author\AuthorController::login');
