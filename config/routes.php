<?php
use Cake\Routing\Router;

Router::plugin('Alaxos', function($routes) {
    
    $routes->extensions(['js']);
    
    /**
     * Connect a route for the index action of any controller.
     * And a more general catch all route for any action.
     *
     * The `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'InflectedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'InflectedRoute']);`
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
    */
    
    $routes->connect('/as', ['controller' => 'Javascripts', 'action' => 'antispam'], ['routeClass' => 'InflectedRoute']);
    
    $routes->fallbacks();
});