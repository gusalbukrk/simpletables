<?php

namespace Table;

require_once __DIR__ . "/../Core/View.php";

class View extends \Core\View
{
  public function __construct()
  {
    $this->controller = "Table";
  }
}
