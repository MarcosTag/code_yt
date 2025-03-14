<?php

namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

class AppController extends Action {

	public function record_expense() {

        echo '<pre>';
        print_r($_POST);
        echo '</pre>';
		//$this->render('index');
	}

}


?>