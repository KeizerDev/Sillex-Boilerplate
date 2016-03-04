<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MuntenSite\Controller\CategorieController;
use MuntenSite\Controller\MuntController;
use MuntenSite\Controller\LoginController;

use Silex\Application;

$app = new Application();
$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_sqlite',
        'path' => __DIR__ . '/../db/munten.db',
    ),
));

define('WEBROOT', __DIR__ . '/../web/');

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../src/MuntenSite/Resources/views/',
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'login_path' => array(
            'pattern' => '^/login$',
            'anonymous' => true
        ),
        'default' => array(
            'pattern' => '^/.*$',
            'anonymous' => true,
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/login_check',
                'always_use_default_target_path' => true,
                'default_target_path' => '/admin'
            ),
            'logout' => array(
                'logout_path' => '/admin/logout',
            ),
            'users' =>  array (
                'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
            ),
        ),
    ),
    'security.access_rules' => array(
        array('^/login', 'IS_AUTHENTICATED_ANONYMOUSLY'),
        array('^/register', 'IS_AUTHENTICATED_ANONYMOUSLY'),
        array('^/admin', 'ROLE_ADMIN'),
    )
));


$app['user.last_auth_exception'] = $app->protect(function (Request $request) {
    if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
        return $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
    }
    $session = $request->getSession();
    if ($session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
        $exception = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        return $exception;
    }
    return [];
});


$app->before(function () use ($app) {
    $categorieArr = $app['db']->fetchAll('SELECT * FROM categories');
    $app['twig']->addGlobal('categories', $categorieArr);
});

$app['muntController'] = $app->share(function () use ($app) {
    return new MuntController($app['twig'], $app['db']);
});

$app['categorieController'] = $app->share(function () use ($app) {
    return new CategorieController($app['twig'], $app['db']);
});

$app['loginController'] = $app->share(function () use ($app) {
    return new LoginController($app['twig'], $app['db']);
});

$app->get('/', function () use ($app) {
    $muntenArr = $app['db']->query("SELECT * FROM munten", PDO::FETCH_ASSOC);

    return $app['twig']->render('home.html.twig', array('munten' => $muntenArr,));
});

$app->get('/admin', function () use ($app) {
    $muntenArr = $app['db']->query("SELECT * FROM munten", PDO::FETCH_ASSOC);
    return $app['twig']->render('home.html.twig', array('munten' => $muntenArr,));
});

$app->get('/munt', function (Application $app) {
    return $app->redirect('/');
});

$app->get('/munt/', function (Application $app) {
    return $app->redirect('/');
});

$app->get('/munt/{id}', "muntController:renderMuntPage");
$app->get('/login', "loginController:renderLoginPage");
$app->get('/categorie/{categorie}', "categorieController:renderCategoriePage");
$app->get('/admin/add', "muntController:renderAddPage");
$app->post('/admin/add', "muntController:addMunt");

$app->run();