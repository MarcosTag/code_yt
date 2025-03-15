<?php

namespace App\Models;

use MF\Model\Model;

class Event extends Model {

    private $event_date;
    private $event_description;
    private $event_type;
    private $event_recurrence;
    private $event_effected;
    private $event_category;
    private $event_subcategory;


    public function __get( $attr ) {
        return $this->$attr;
    }

    public function __set( $attr, $value ) {
        $this->$attr = $value;
        return $this->$attr;
    }

}

?>