<?php

namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

class AppController extends Action {


	public function record_expense() {

        // foreach ($_POST as $key => $value) {
        //     echo 'key: ' . $key . '<br>' . 'value: ' . $value;
        //     echo '<hr>';
        // }

        // $entry = Container::getModel( 'Entry' );
        // $entry->entry_save( $_POST );

        // echo '<pre>';
        // print_r( $entry );
        // echo '</pre>';

        // $entry_description = filter_input( INPUT_POST, 'entry_description', FILTER_SANI );

        //$data_post = filter_input_array(  );

        $data_post = [
            'entry_nature'          =>  FILTER_SANITIZE_STRIPPED,
            'entry_release_date'    =>  FILTER_SANITIZE_STRIPPED,
            'entry_type'            =>  FILTER_SANITIZE_STRIPPED,
        ];

        echo '<pre>';
        //var_dump($_POST['entry_description'] );
        echo '<hr>';
        var_dump( preg_replace( '/\/S/', '', \htmlspecialchars( $_POST['entry_description'] ) ) );

        
        echo '</pre>';
        echo '#\\^[^0-9]\\$#';


	}

}


?>