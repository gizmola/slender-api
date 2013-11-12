<?php

class Foo {
     
     private $methods = array();
     private $test ="test";

     public function addBar($barFunc) {

       $this->methods['bar'] = \Closure::bind($barFunc, $this, get_class());
     }

     function __call($method, $args) {
          if(is_callable($this->methods[$method]))
          {
            return call_user_func_array($this->methods[$method], $args);
          }
     }

 }

$barFunc = function () {
 echo $this->test;
};

 $foo = new Foo;
 $foo->addBar($barFunc);
 $foo->bar();
 dd();

//dd(App::make('route-creator'));

use Slender\Api\Facade\RouteCreator;

//dd(RouteCreator::loadCoreRoutes());