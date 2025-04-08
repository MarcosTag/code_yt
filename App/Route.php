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

		$routes['edit_entry'] = [
			'route'			=>	'/edit_entry',
			'controller'	=>	'AppController',
			'action'		=>	'edit_entry'
		];

		$routes['remove_entry'] = [
			'route'			=>	'/remove_entry',
			'controller'	=>	'AppController',
			'action'		=>	'remove_entry'
		];

		$routes['value-input-entry_qty_installments'] = [
			'route'			=>	'/value-input-entry_qty_installments',
			'controller'	=>	'AppController',
			'action'		=>	'value_input_entry_qty_installments_ajax'
		];

		$this->setRoutes( $routes );
	}

}

?>