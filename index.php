<?php
/**
 * File:  index.php
 * Creation Date: 04/12/2017
 * description:
 *
 * @author: canals
 */

require_once __DIR__ . '/vendor/autoload.php';

//Init Eloquent

//Init Slim
$app = new Slim\App();

//Init Twig
// Fetch DI Container
$container = $app->getContainer();
// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('src/vues', [
        'cache' => 'src/cache'
    ]);

    // Instantiate and add Slim specific extension
    $router = $c->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};


$app->get('/accueil/{name}', function ($request, $response, $args) {
    $controller = new \mywishlist\controleurs\ControleurAccueil($response, $this->view);
    return $controller->afficherAccueil($args["name"]);
});

$app->run();
