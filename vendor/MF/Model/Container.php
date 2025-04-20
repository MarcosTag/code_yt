<?php

namespace MF\Model;

use App\Connection;

class Container {

	public static function getModel( $model ) {
		$class = "\\App\\Models\\".ucfirst( $model );
		$conn = Connection::getDb();

		return new $class( $conn );
	}

	public static function getModelNotDb( $model ) {

		$class = "\\App\\Models\\".ucfirst( $model );
		return new $class();

	}
}


?>