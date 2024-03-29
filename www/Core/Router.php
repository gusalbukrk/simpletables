<?php

require_once __DIR__ . "/Utils.php";

require_once __DIR__ . "/../Table/Controller.php";
require_once __DIR__ . "/../User/Controller.php";

class Router
{
  protected $routes = [];

  // get routes array
  public function get()
  {
    return $this->routes;
  }

  // add route to routes array
  public function add($controller, $method, $subdomain, $path)
  {
    if ($subdomain == "*") $subdomain = "[\w-]+\."; // * => wildcard subdomain

    array_push($this->routes, ["controller" => $controller, "method" => $method,  "regex" => "/^{$subdomain}simpletables.xyz\/{$path}$/"]);
  }

  // find route that matches current url, returns null if no route found
  public function match()
  {
    $url = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; // current URL

    foreach ($this->routes as $route) {
      if (preg_match($route["regex"], $url, $matches)) {
        return $route;
      }
    }

    return null;
  }

  // instantiate controller and run desired method
  public function dispatch()
  {
    $route = $this->match();

    $controller_name = ucfirst($route["controller"]) . "\Controller";
    $method = preg_replace("/-/", "_", $route["method"]); // replace slash with underline

    if (
      !is_null($route) &&
      class_exists($controller_name) &&
      is_callable([($controller = new $controller_name()), $method])
    ) {
      $controller->{$method}();
    } else {
      Core\Controller::not_found();
    }
  }
}
