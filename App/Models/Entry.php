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

    public function format_id_for_entry() {
        $dateTime = new \DateTime();
        $dateTime = $dateTime->format( 'YmdHisvu' );

        return $_SERVER['REMOTE_PORT'] . $dateTime;
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

        $recurrence = $this->__get( 'entry_recurrence' );
        $installments = $this->__get( 'entry_qty_installments' );
        $dateTime = new \DateTime( $this->__get( 'entry_expected_date' ) );
        $id_for_entry = $this->format_id_for_entry();
        
        if( $recurrence == 'installment' && $installments > 1 ) {

            $parcelas = $this->calculate_installments( $this->__get( 'entry_value' ), $installments );
            
            for ( $i = 0; $i < $installments; $i++ ) { 

                $query = '
                    insert into entry( 
                        id_for_entry, entry_nature, entry_value, entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
                    ) values(
                        :id_for_entry, :entry_nature, :entry_value, :entry_expected_date, :entry_type, :entry_description, :entry_recurrence, :entry_qty_installments, :entry_effected, :entry_category, :entry_subcategory
                    )

                ';

                $stmt = $this->db->prepare( $query );

                $stmt->bindValue( ':id_for_entry', $id_for_entry );
                $stmt->bindValue( ':entry_nature', $this->__get( 'entry_nature' ) );
                $stmt->bindValue( ':entry_value', $parcelas[$i] );
                $stmt->bindValue( ':entry_expected_date', $dateTime->format( 'Y-m-d' ) );
                $stmt->bindValue( ':entry_type', $this->__get( 'entry_type' ) );
                $stmt->bindValue( ':entry_description', $this->__get( 'entry_description' ) );
                $stmt->bindValue( ':entry_recurrence', $this->__get( 'entry_recurrence' ) );
                $stmt->bindValue( ':entry_qty_installments', $this->__get( 'entry_qty_installments' ) );
                $stmt->bindValue( ':entry_effected', $this->__get( 'entry_effected' ) );
                $stmt->bindValue( ':entry_category', $this->__get( 'entry_category' ) );
                $stmt->bindValue( ':entry_subcategory', $this->__get( 'entry_subcategory' ) );

                $dateTime->modify( '+1 month' );

                try {
                    $stmt->execute();
                    
                } catch (\Throwable $th) {
                    echo '<pre>';
                    print_r( $th );
                    echo '</pre>';
                }
            }

        } else {

            $query = '
                insert into entry( 
                    id_for_entry, entry_nature, entry_value, entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
                ) values(
                    :id_for_entry, :entry_nature, :entry_value, :entry_expected_date, :entry_type, :entry_description, :entry_recurrence, :entry_qty_installments, :entry_effected, :entry_category, :entry_subcategory
                )

            ';

            $stmt = $this->db->prepare( $query );

            $stmt->bindValue( ':id_for_entry', $id_for_entry );
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
                
            } catch (\Throwable $th) {
                echo '<pre>';
                print_r( $th );
                echo '</pre>';
            }
        }

        return $this;

    }
    
    /**
     * 
     * 
     * retorna os lançamentos por mês
     */
    public function get_entrys_for_month( int $month, int $year ) {

        $query = '
            select 
                id,  id_for_entry, entry_value, entry_nature, DATE_FORMAT(entry_expected_date, "%d/%m/%Y") as entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            from
                entry
            where
                MONTH(entry_expected_date) = :month && YEAR(entry_expected_date) = :year
            order by
                entry_expected_date asc
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':month', $month );
        $stmt->bindValue( ':year', $year );

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
     * retorna os lançamentos por mês
     */
    public function get_entrys_for_key( $entryKey ) {

        $query = '
            select 
                id, id_for_entry, entry_value, entry_nature, entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            from
                entry
            where
                id_for_entry = :id_for_entry
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':id_for_entry', $entryKey );

        try {

            $stmt->execute();
		    return $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';

        }
    }

    public function get_entry_key_for_id( $id ) {
        
        $query = '
            select
                id_for_entry
            from   
                entry
            where
                id = :id
        ';

        $stmt = $this->db->prepare( $query );
        $stmt->bindValue( ':id', $id );

        try {

            $stmt->execute();
		    return $stmt->fetch( \PDO::FETCH_ASSOC )['id_for_entry'];

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
        $value = $coin . ' ' . /*str_replace( '.', ',', $value )*/ number_format( $value, 2, ',', '.' );
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

    /**
     * 
     * 
     * remove um lançamento baseado no id
     */
    public function remove_entry( $entryId ) {

        $entryKey = $this->get_entry_key_for_id( $entryId );

        $query = '
            delete from
                entry
            where
                id_for_entry = :entryKey
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':entryKey', $entryKey );

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

    public function calculate_installments( $value, $installment ) {
        
        $parcelas = [];
        $diferenca = 0;

        for ( $i = 0; $i < $installment ; $i++ ) { 

            if( $i == $installment ) {

                if( $parcelas[0] * $installment > $value ) {
                    $diferenca = $parcelas[0] * $installment - $value;
                    $parcelas[0] = $parcelas[0] + $diferenca;
                    $diferenca = 0;
                } else {
                    $diferenca = $parcelas[0] * $installment - $value;
                    $parcelas[0] = $parcelas[0] - $diferenca;

                    $diferenca = 0;
                }

                
            }

            $parcelasFormatada = ( $value / $installment ) - $diferenca;


            $parcelas[] = $parcelasFormatada;
        }
        
        return $parcelas;
        //return $this->format_entry_value( $value / $installment );
    }

    public function get_installment_for_month( $month, $entryKey ) {

        $entrysInstallments = $this->get_entrys_for_key( $entryKey );

        $dateExpected = array_column( $entrysInstallments, 'entry_expected_date' );
        $months = [];

        foreach ( $dateExpected as $key => $date ) {
            $months[] = date( "m", strtotime( $date ) );
        }

        $installment = array_search( $month, $months ) + 1;

        return $installment;

    }

    public function sum_values_entry_installments( $entryKey, bool $formatCoin = true ) {
        $entryValues = array_column( $this->get_entrys_for_key( $entryKey ), 'entry_value' );

        return $formatCoin ? $this->format_entry_value( array_sum( $entryValues ) ) : array_sum( $entryValues );
    }

    public function get_qty_installment_for_id( $entryId ) {

        $query = '
            select
                entry_qty_installments
            from
                entry
            where
                id = :entryId
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':entryId', $entryId );

        try {

            $stmt->execute();
		    return $stmt->fetch( \PDO::FETCH_ASSOC ); 

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';
        }

    }
}

?>