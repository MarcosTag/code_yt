<?php

namespace App;

use MF\Init\Bootstrap;

class Route extends Bootstrap {

	protected function initRoutes() {

		$routes['home'] = [
			'route' 		=> '/',
			'controller' 	=> 'indexController',
			'action' 		=> 'index'
		];

		$routes['expense'] = [
			'route'			=>	'/expense',
			'controller'	=>	'AppController',
			'action'		=>	'record_expense'
		];

		$this->setRoutes($routes);
	}

}

?>