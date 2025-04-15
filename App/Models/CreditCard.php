<?php

namespace App\Models;

use MF\Model\Model;

class CreditCard extends Model {

    private $card_alias;
    private $card_adm;
    private $card_number;
    private $card_flag;
    private $card_shut;
    private $card_expected_date;
    

    public function __get( $attr ) {
        return $this->$attr;
    }

    public function __set( $attr, $value ) {
        $this->$attr = $value;
        // return $this->$attr;
    }

    
}

?>