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
    private $entry_qty_installments;
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

    public function entry_save( array $array ) {
        
        foreach ( $array as $key => $value ) {
            $this->__set( $key, $value );
        }

        $query = '
            insert into entry( 
                entry_nature, entry_value, entry_release_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            ) values(
                :entry_nature, :entry_value, :entry_release_date, :entry_type, :entry_description, :entry_recurrence, :entry_qty_installments, :entry_effected, :entry_category, :entry_subcategory
            )

        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':entry_nature', $this->__get( 'entry_nature' ) );
        $stmt->bindValue( ':entry_value', $this->__get( 'entry_value' ) );
        $stmt->bindValue( ':entry_release_date', $this->__get( 'entry_release_date' ) );
        $stmt->bindValue( ':entry_type', $this->__get( 'entry_type' ) );
        $stmt->bindValue( ':entry_description', $this->__get( 'entry_description' ) );
        $stmt->bindValue( ':entry_recurrence', $this->__get( 'entry_recurrence' ) );
        $stmt->bindValue( ':entry_qty_installments', $this->__get( 'entry_qty_installments' ) );
        $stmt->bindValue( ':entry_effected', $this->__get( 'entry_effected' ) );
        $stmt->bindValue( ':entry_category', $this->__get( 'entry_category' ) );
        $stmt->bindValue( ':entry_subcategory', $this->__get( 'entry_subcategory' ) );

        try {

            $stmt->execute();
            return $this;

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';

        }

    }
    
    public function get_entrys_for_month( int $month ) {

        $query = '
            select 
                id, entry_value, entry_nature,  DATE_FORMAT(entry_date, "%d/%m/%Y") as entry_date, DATE_FORMAT(entry_release_date, "%d/%m/%Y") as entry_release_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            from
                entry
            where
                MONTH(entry_date) = :month;
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':month', $month );

        try {

            $stmt->execute();
		    return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';

        }
    }

    public function format_entry_value( $value, $coin = 'R$' ) {
        $value = $coin . ' ' . str_replace( '.', ',', $value );
        return $value;
    }

}

?>