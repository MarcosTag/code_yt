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

        $data_post_filter = [
            'entry_nature'          =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_nature'] ) ),
            'entry_value'           =>  preg_replace( '/[^0-9]/', '', \htmlspecialchars( $_POST['entry_value'] ) ),
            'entry_release_date'    =>  preg_replace( '/[^0-9|&#039;|^-]/', '', \htmlspecialchars( $_POST['entry_release_date'] ) ),
            'entry_type'            =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) ),
            'entry_description'     =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_description'] ) ),
            'entry_recurrence'      =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_recurrence'] ) ),
            'entry_effected'        =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_effected'] ) ),
            'entry_category'        =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_category'] ) ),
            'entry_subcategory'     =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_subcategory'] ) ),
        ];

        $entry = Container::getModel( 'Entry' );
        $entry->entry_save( $data_post_filter );

        //    $entry_nature = preg_replace( '[&lt;|&gt;|/|(script)|(select)|(from)|(insert)|(into)|\(|\)&#039;]', '', \htmlspecialchars( $_POST['entry_nature'] ) );
        //    $entry_release_date = preg_replace( '/[^0-9|&#039;|^-]/', '', \htmlspecialchars( $_POST['entry_release_date'] ) );
        //    $entry_type = preg_replace( '[&lt;|&gt;|/|(script)|(select)|(from)|(insert)|(into)|\(|\)&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) );
        //    $entry_description = preg_replace( '[&lt;|&gt;|/|(script)|(select)|(from)|(insert)|(into)|\(|\)&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) );
        //    $entry_recurrence = preg_replace( '[&lt;|&gt;|/|(script)|(select)|(from)|(insert)|(into)|\(|\)&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) );
        //    $entry_effected = preg_replace( '[&lt;|&gt;|/|(script)|(select)|(from)|(insert)|(into)|\(|\)&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) );
        //    $entry_category = preg_replace( '[&lt;|&gt;|/|(script)|(select)|(from)|(insert)|(into)|\(|\)&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) );
        //    $entry_subcategory = preg_replace( '[&lt;|&gt;|/|(script)|(select)|(from)|(insert)|(into)|\(|\)&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) );




        echo '<pre>';
        //var_dump($_POST['entry_description'] );
        echo '<hr>';
        print_r( $entry );

        
        echo '</pre>';


	}

}


?>