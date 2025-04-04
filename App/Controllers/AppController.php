<?php

namespace App\Controllers;

//os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

class AppController extends Action {

    public function index() {

		$this->render('index');
	}

	public function record_expense() {

        $data_post_filter = [
            'entry_nature'              =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_nature'] ) ),
            'entry_value'               =>  floatval( str_replace( ',', '.', $_POST['entry_value'] ) ),
            'entry_expected_date'       =>  preg_replace( '/[^0-9|&#039;|^-]/', '', \htmlspecialchars( $_POST['entry_expected_date'] ) ),
            'entry_type'                =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_type'] ) ),
            'entry_description'         =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_description'] ) ),
            'entry_recurrence'          =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_recurrence'] ) ),
            'entry_qty_installments'    =>  preg_replace( '/[^1-9]/', '', \htmlspecialchars( $_POST['entry_qty_installments'] ?? 1 ) ),
            'entry_effected'            =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( ! isset( $_POST['entry_effected'] ) ? '0' : '1' ) ),
            'entry_category'            =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_category'] ) ),
            'entry_subcategory'         =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['entry_subcategory'] ) ),
        ];

        $entry = Container::getModel( 'Entry' );
        $entry->entry_save( $data_post_filter );

        header( 'location: /' );
	}

    /**
     * 
     * 
     * atualiza o estado do lançamento como efetivado ou não via ajax
     */
    public function controller_update_entry_effected() {

        /** recebe um valor via post do ajax */
        $entry = Container::getModel( 'Entry' );

        $entry->update_entry_effected( $_POST['val'], $_POST['id'] );

        /** para retornar algum valor para o ajax é necessário echoar algo */

        /**
         * 
         * DEBUG
         */
        // $codificado = $_POST;
        // file_put_contents('novo.json', $codificado);

    }

    /**
     * 
     * 
     * edita o lançamento no banco de dados
     */
    public function edit_entry() {

        $data_post_filter = [
            'entry_nature'              =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['edit_entry_nature'] ) ),
            'entry_value'               =>  floatval( str_replace( ',', '.', $_POST['edit_entry_value'] ) ),
            'entry_expected_date'       =>  preg_replace( '/[^0-9|&#039;|^-]/', '', \htmlspecialchars( $_POST['edit_entry_expected_date'] ) ),
            'entry_type'                =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['edit_entry_type'] ) ),
            'entry_description'         =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['edit_entry_description'] ) ),
            'entry_recurrence'          =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['edit_entry_recurrence'] ) ),
            'entry_qty_installments'    =>  preg_replace( '/[^1-9]/', '', \htmlspecialchars( $_POST['edit_entry_qty_installments'] ?? 1 ) ),
            'entry_category'            =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['edit_entry_category'] ) ),
            'entry_subcategory'         =>  preg_replace( '[&lt;|&gt;|/|(alert)|(script)|(select)|(from)|(insert)|(into)|\(|\)|&#039;]', '', \htmlspecialchars( $_POST['edit_entry_subcategory'] ) ),
        ];

        $entryId = preg_replace( '/[^1-9]/', '', \htmlspecialchars( $_POST['entry_id'] ) );

        $entry = Container::getModel( 'Entry' );

        $entry->update_entry( $data_post_filter, $entryId );

        header( 'location: /' );


    }

}


?>