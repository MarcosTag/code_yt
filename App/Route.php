<?php

namespace App;

use MF\Init\Bootstrap;

class Route extends Bootstrap {

	protected function initRoutes() {

		$routes['home'] = [
			'route' 		=> '/',
			'controller' 	=> 'AppController',
			'action' 		=> 'index'
		];

		$routes['expense'] = [
			'route'			=>	'/entry',
			'controller'	=>	'AppController',
			'action'		=>	'record_expense'
		];

		$this->setRoutes( $routes );
	}

}

?>