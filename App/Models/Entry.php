<?php

namespace App\Models;

use MF\Model\Model;

class Entry extends Model {

    private $entry_nature;
    private $entry_value;
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

        $query = '
            insert into entry( 
                entry_nature, entry_value, entry_release_date, entry_type, entry_description, entry_recurrence, entry_effected, entry_category, entry_subcategory
            ) values(
                :entry_nature, :entry_value, :entry_release_date, :entry_type, :entry_description, :entry_recurrence, :entry_effected, :entry_category, :entry_subcategory
            )

        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':entry_nature', $this->__get( 'entry_nature' ) );
        $stmt->bindValue( ':entry_value', $this->__get( 'entry_value' ) );
        $stmt->bindValue( ':entry_release_date', $this->__get( 'entry_release_date' ) );
        $stmt->bindValue( ':entry_type', $this->__get( 'entry_type' ) );
        $stmt->bindValue( ':entry_description', $this->__get( 'entry_description' ) );
        $stmt->bindValue( ':entry_recurrence', $this->__get( 'entry_recurrence' ) );
        $stmt->bindValue( ':entry_effected', $this->__get( 'entry_effected' ) );
        $stmt->bindValue( ':entry_category', $this->__get( 'entry_category' ) );
        $stmt->bindValue( ':entry_subcategory', $this->__get( 'entry_subcategory' ) );

        try {
            $stmt->execute();
        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';

        }

        return $this;
    }
    

}

?>