<?php

namespace App\Models;

use MF\Model\Model;

class Entry extends Model {

    private $entry_nature;
    private $entry_value;
    private $entry_expected_date;
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


    /**
     * 
     * 
     * salva o lançamento no banco de dados
     */
    public function entry_save( array $array ) {
        
        foreach ( $array as $key => $value ) {
            $this->__set( $key, $value );
        }

        $query = '
            insert into entry( 
                entry_nature, entry_value, entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            ) values(
                :entry_nature, :entry_value, :entry_expected_date, :entry_type, :entry_description, :entry_recurrence, :entry_qty_installments, :entry_effected, :entry_category, :entry_subcategory
            )

        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':entry_nature', $this->__get( 'entry_nature' ) );
        $stmt->bindValue( ':entry_value', $this->__get( 'entry_value' ) );
        $stmt->bindValue( ':entry_expected_date', $this->__get( 'entry_expected_date' ) );
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
    
    /**
     * 
     * 
     * retorna os lançamentos por mês
     */
    public function get_entrys_for_month( int $month ) {

        $query = '
            select 
                id, entry_value, entry_nature,  DATE_FORMAT(entry_date, "%d/%m/%Y") as entry_date, DATE_FORMAT(entry_expected_date, "%d/%m/%Y") as entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            from
                entry
            where
                MONTH(entry_date) = :month
            order by
                entry_expected_date asc
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


    /**
     * 
     * 
     * formata o valor dos lançamentos vindo do banco de dados
     */
    public function format_entry_value( $value, $coin = 'R$' ) {
        $value = $coin . ' ' . str_replace( '.', ',', $value );
        return $value;
    }

    /**
     * 
     * 
     * retorna a data de vencimento do lançamento no padrão html
     */
    public function get_entry_expected_date_html_for_id( $id ) {
        
        $query = '
            select
                entry_expected_date
            from
                entry
            where
                id = :id

        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':id', $id );

        try {

            $stmt->execute();
		    return $stmt->fetch( \PDO::FETCH_ASSOC )['entry_expected_date'];

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';

        }

    }


    /**
     * 
     * 
     * formata o texto vindo do input recorrencia
     */
    public function format_entry_recurrence( $recurrence ) {
        switch ( $recurrence ) {
            case 'no_recurrence':
                return 'Sem recorrência';
                break;

            case 'installment':
                return 'Parcelado';
                break;

            case 'fixed':
                return 'Fixo';
                break;
            
            default:
                return;
                break;
        }
    }


    /**
     * 
     * 
     * retorna a soma dos lançamentos por natureza "receita ou despesa"
     */
    public function get_value_total_entrys( $entrys, $nature ) {

        $valuesForSum = [];

        foreach ( $entrys as $key => $entry ) {

            if( array_search( $nature, $entry ) && $entry['entry_nature'] == $nature ) {
                $valuesForSum[] = $entry['entry_value'];
            }
        }

        return array_sum( $valuesForSum );
        
    }

    /**
     * 
     * 
     * retorna o saldo "receita - despesa"
     */
    public function get_value_balance( $entrys ) {
        
        $balance = $this->get_value_total_entrys( $entrys, 'revenue' ) - $this->get_value_total_entrys( $entrys, 'expense' );

        return $balance;

    }

    public function update_entry( array $array, $idEntry ) {

        foreach ( $array as $key => $value ) {
            $this->__set( $key, $value );
        }

        $query = '
            update
                entry
            set
                entry_nature = :entry_nature, entry_value = :entry_value, entry_expected_date = :entry_expected_date, entry_type = :entry_type, entry_description = :entry_description, entry_recurrence = :entry_recurrence, entry_qty_installments = :entry_qty_installments, entry_category = :entry_category, entry_subcategory = :entry_subcategory
            where
                id = :idEntry
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':entry_nature', $this->__get('entry_nature') );
        $stmt->bindValue( ':entry_value', $this->__get('entry_value') );
        $stmt->bindValue( ':entry_expected_date', $this->__get('entry_expected_date') );
        $stmt->bindValue( ':entry_type', $this->__get('entry_type') );
        $stmt->bindValue( ':entry_description', $this->__get('entry_description') );
        $stmt->bindValue( ':entry_recurrence', $this->__get('entry_recurrence') );
        $stmt->bindValue( ':entry_qty_installments', $this->__get('entry_qty_installments') );
        $stmt->bindValue( ':entry_category', $this->__get('entry_category') );
        $stmt->bindValue( ':entry_subcategory', $this->__get('entry_subcategory') );
        $stmt->bindValue( ':idEntry', $idEntry );

        try {

            $stmt->execute();
		    // return $stmt->fetchAll( \PDO::FETCH_ASSOC );
            return;

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';
        }
    }

    /**
     * 
     * 
     * atualiza a coluna de lançamento efetivado
     */
    public function update_entry_effected( $dataUpdate, $idEntry ) {

        $query = '
            update
                entry
            set
                entry_effected = :dataUpdate
            where 
                id = :idEntry
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':dataUpdate', $dataUpdate );
        $stmt->bindValue( ':idEntry', $idEntry );

        try {

            $stmt->execute();
		    // return $stmt->fetchAll( \PDO::FETCH_ASSOC );
            return;

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';
        }

    }
}

?>