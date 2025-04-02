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

		$routes['update_entry_effected'] = [
			'route'			=>	'/up_entry_effected',
			'controller'	=>	'AppController',
			'action'		=>	'controller_update_entry_effected'
		];

		$this->setRoutes( $routes );
	}

}

?>