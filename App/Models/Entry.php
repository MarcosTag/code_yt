<?php

namespace App\Models;

use MF\Model\Model;

class Entry extends Model {

    private $entry_nature;
    private $entry_release_date;
    private $entry_type;
    private $entry_description;
    private $entry_recurrence;
    private $entry_effected;
    private $entry_category;
    private $entry_subcategory;

    public function __get( $attr ) {
        return $this->$attr;
    }

    public function __set( $attr, $value ) {
        $this->$attr = $value;
        // return $this->$attr;
    }

    public function entry_save( $array ) {
        
        foreach ( $array as $key => $value ) {
            $this->__set( $key, $value );
        }

        

    }
    

}

?>