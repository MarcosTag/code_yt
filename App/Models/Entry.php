<?php

namespace App\Models;

use MF\Model\Model;
use App\Models\BrAPI;

class Entry extends Model {

    private $entry_nature;
    private $entry_value;
    private $entry_expected_date;
    private $entry_type;
    private $entry_credit_card;
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

        $brApi = new BrAPI();
        $dateTime = $this->__get( 'entry_expected_date' );
        $arrayDate = $brApi->get_array_date_int( $this->__get( 'entry_expected_date' ) );
        $entryDay = $arrayDate['day'];
        // $recurrence = $this->__get( 'entry_recurrence' );
        $installments = $this->__get( 'entry_qty_installments' );
        
        $id_for_entry = $this->format_id_for_entry();

        $parcelas = $this->calculate_installments( $this->__get( 'entry_value' ), $installments );
        // $nextWorkday = '';
        
        for ( $i = 0; $i < $installments; $i++ ) { 

            $arrayDate['day'] = $entryDay;
            $expectedDate = $brApi->transform_array_date_int_in_string( $arrayDate );

            /**
             * 
             * 
             * Se um lançamento de categoria Imposto for lançado no último dia do mês, verifica se o dia é útil e decrementa o dia para o dia útil anterior a data do lançamento
             */
            if( $this->__get( 'entry_category' ) == 'Impostos' ) {

                if( ! $brApi->is_workday( $arrayDate ) || ! $brApi->is_valid_date( $arrayDate ) ) {
                    $expectedDate = $brApi->get_prev_workday( $arrayDate );
                } else {
                    $expectedDate = $brApi->transform_array_date_int_in_string( $arrayDate );
                }
            }

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
            $stmt->bindValue( ':entry_expected_date', $expectedDate );
            $stmt->bindValue( ':entry_type', $this->__get( 'entry_type' ) );
            $stmt->bindValue( ':entry_description', $this->__get( 'entry_description' ) );
            $stmt->bindValue( ':entry_recurrence', $this->__get( 'entry_recurrence' ) );
            $stmt->bindValue( ':entry_qty_installments', $this->__get( 'entry_qty_installments' ) );
            $stmt->bindValue( ':entry_effected', $this->__get( 'entry_effected' ) );
            $stmt->bindValue( ':entry_category', $this->__get( 'entry_category' ) );
            $stmt->bindValue( ':entry_subcategory', $this->__get( 'entry_subcategory' ) );

            $arrayDate = $brApi->get_next_month( $arrayDate, true );

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

    public function get_entry_by_id( $id ) {

        $query = '
            select
                entry_value, entry_nature, entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            from
                entry
            where
                id = :id
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':id', $id );

        try {

            $stmt->execute();
            $entry = $stmt->fetch( \PDO::FETCH_ASSOC );
            return $entry;

        } catch ( \Throwable $th ) {

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
    public function get_entrys_for_month( int $month, int $year, $effected = false ) {

        $query = '
            select 
                id,  id_for_entry, entry_value, entry_nature, entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            from
                entry
            where
                MONTH(entry_expected_date) = :month && YEAR(entry_expected_date) = :year
            order by
                entry_expected_date asc
        ';

        if ( $effected ) {
            $query = '
            select 
                id,  id_for_entry, entry_value, entry_nature, entry_expected_date, entry_type, entry_description, entry_recurrence, entry_qty_installments, entry_effected, entry_category, entry_subcategory
            from
                entry
            where
                MONTH(entry_expected_date) = :month && YEAR(entry_expected_date) = :year && entry_effected = true
            order by
                entry_expected_date asc
        ';
        }

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

    public function count_entrys_for_day( int $day, int $month, int $year, $effected = false ) {
        $query = '
            select
                count(id_for_entry)
            as
                total_entrys
            from
                entry
            where
                MONTH(entry_expected_date) = :month && YEAR(entry_expected_date) = :year && DAY(entry_expected_date) = :day && entry_effected = false
            
        ';

        if ( $effected ) {
            $query = '
            select
                count(id_for_entry)
            as
                total_entrys
            from
                entry
            where
                MONTH(entry_expected_date) = :month && YEAR(entry_expected_date) = :year && DAY(entry_expected_date) = :day && entry_effected = true
            
        ';
        }

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':day', $day );
        $stmt->bindValue( ':month', $month );
        $stmt->bindValue( ':year', $year );

        try {

            $stmt->execute();
		    return $stmt->fetch( \PDO::FETCH_ASSOC )['total_entrys'];

        } catch (\Throwable $th) {
            echo '<pre>';
            print_r( $th );
            echo '</pre>';

        }
    }

    public function get_entry_recurrence_by_id( $id ) {

        $query = '
            select
                entry_recurrence
            from 
                entry
            where
                id = :id
        ';

        $stmt = $this->db->prepare( $query );

        $stmt->bindValue( ':id', $id );

        try {

            $stmt->execute();
            $recurrence = $stmt->fetch( \PDO::FETCH_ASSOC )['entry_recurrence'];
            return $recurrence;

        } catch ( \Throwable $th ) {

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

        if( $this->get_entry_recurrence_by_id( $idEntry ) == 'fixed' && $dataUpdate == 1 ) {

            $brApi = new BrAPI();

            $entry = $this->get_entry_by_id( $idEntry );
            $entry['entry_expected_date'] = $brApi->get_next_month( $entry['entry_expected_date'] );
            
            $this->entry_save( $entry );
        }

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

    public function get_days_with_entry_per_month( $month, $year ) {

        $arrayEntrysDate = [];
        $entrysForMonth = $this->get_entrys_for_month( $month, $year );

        foreach ( $entrysForMonth as $key => $entry ) {
            $arrayEntrysDate[] = $entry['entry_expected_date'];
        }

        return array_values( array_unique( $arrayEntrysDate ) );

    }

    public function get_qtt_entrys_effected_per_day( $month, $day ) {
        
        $query = '
            count  
                id_for_entry
            where 



        ';

    }   

    public function get_format_display_entry_sumary( $month, $year ) {

        $brApi = new BrAPI();

        $entrys = $this->get_entrys_for_month( $month, $year );
        $daysWithEntry = $this->get_days_with_entry_per_month( $month, $year );
        $i = 0;

        if( count( $entrys ) >= 1 ) :  ?>
    
        <div id="display-entry-sumary">

            <div class="day-line-box">

                <!-- <div class="sumary-of-entries">
                    TESTE
                </div> -->

                <div class="entry-box-for-day">
                <?php foreach( $entrys as $key => $entry ) : ?>

                    <?php 
                    
                    if( $entry['entry_expected_date'] == $daysWithEntry[$i] ) { ?>

                        <!-- card-sumary começa aqui -->
                        <div class="card-sumary <?php echo $entry['entry_nature'] == 'expense' ? 'expense' : 'revenue' ?> <?php echo $entry['entry_recurrence'] == 'installment' ? 'installment' : '' ?>" id="entry-card-<?php  echo $entry['id']; ?>">
                    
                            <div class="icon-card">
                                <?php echo $entry['entry_nature'] == 'expense' ? '<i class="fa-solid fa-arrow-trend-down"></i>' : '<i class="fa-solid fa-arrow-trend-up"></i>' ?>
                            </div>
                
                            <div class="box-input box-toggle">
                                <input type="checkbox" name="entry_id" class="effected-yes" id="entry-<?php echo $entry['id']; ?>" value="1" hidden <?php echo $entry['entry_effected']; ?> <?php echo $entry['entry_effected'] != '0' ? 'checked' : ''; ?>>
                                <label for="entry-<?php echo $entry['id']; ?>" class="input-legend event-key-toggle" id="" tabindex="0">
                                    <?php echo $entry['entry_expected_date'] ?>
                                    <div class="input-toggle"></div>
                                </label>
                            </div>
                
                            <div class="content-card-sumary">
                                <div class="arrown-one">
                                    <h3 class="entry-name"><?php echo $entry['entry_description'] ?></h3>
                                    <a href="" class="edit-entry">
                                        <span class="btn-icon">
                                            <i class="fas fa-pencil-alt"></i>
                                        </span>
                                    </a>
                                    
                                </div>
                        
                                <div class="arrown-two">
                                    <?php echo $this->format_entry_recurrence( $entry['entry_recurrence'] ); ?>
                
                                    <?php if( $entry['entry_recurrence'] == 'installment' ) { ?>
                                        - <?php echo $this->get_installment_for_month( $month, $entry['id_for_entry'] ); ?> / <?php echo $entry['entry_qty_installments'] . ' ( '. $this->sum_values_entry_installments( $entry['id_for_entry'] ) .' )' ?>
                                    <?php } ?>
                
                                </div>
                        
                                <div class="arrown-tree">
                                    <div class="entry-tags">
                                        <span class="entry-price"><?php echo $this->format_entry_value( $entry['entry_value'] ); ?></span>
                                        <span class="entry-category btn-icon"><i class="fa-brands fa-cc-mastercard"></i></i><?php echo $entry['entry_category']; ?></span>
                                    </div>
                                    <a href="" class="remove-entry" id="remove-entry-<?php echo $entry['id']; ?>">
                                        <span class="btn-icon">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                
                            <form action="edit_entry" method="post" class="form-edity-entry" id="form-edity-entry">
                                <fieldset class="input-legend">
                                    <legend class="input-legend">Natureza</legend>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_nature" id="edit_entry_nature-expense-<?php echo $entry['id']?>" value="expense" <?php echo $entry['entry_nature'] == 'expense' ? 'checked' : '' ; ?>>
                                        <label for="edit_entry_nature-expense-<?php echo $entry['id']?>">Despesa</label>
                                    </div>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_nature" id="edit_entry_nature-revenue-<?php echo $entry['id']?>" value="revenue" <?php echo $entry['entry_nature'] == 'revenue' ? 'checked' : '' ; ?>>
                                        <label for="edit_entry_nature-revenue-<?php echo $entry['id']?>">Receita</label>
                                    </div>
                                    
                                </fieldset>
                
                                <div class="box-input label-row">
                                    <label for="edit_entry_value_<?php echo $entry['id']; ?>" class="input-legend">Valor</label>
                                    <input type="tel" name="edit_entry_value" id="edit_entry_value_<?php echo $entry['id']; ?>" class="input-entry-value" step=".01" placeholder="0,00" <?php echo isset( $entry['entry_value'] ) ? 'value="' . str_replace( '.', ',', $entry['entry_value'] ) . '"' : '' ; ?>>
                                </div>
                
                                <div class="box-input label-row">
                                    <label for="edit_entry_expected_date-<?php echo $entry['id']?>" class="input-legend">Data de lançamento</label>
                                    <input type="date" name="edit_entry_expected_date" id="edit_entry_expected_date-<?php echo $entry['id']?>" <?php echo isset( $entry['entry_expected_date'] ) ? 'value="' . $this->get_entry_expected_date_html_for_id( $entry['id'] ) . '"' : '' ?>>
                                    
                                </div>
                
                                <fieldset class="input-legend">
                                    <legend class="input-legend">Tipo</legend>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_type" id="account-<?php echo $entry['id']?>" value="account" <?php echo $entry['entry_type'] == 'account' ? 'checked' : '' ; ?>>
                                        <label for="account-<?php echo $entry['id']?>">Conta corrente</label>    
                                    </div>        
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_type" id="credit-<?php echo $entry['id']?>" value="credit" <?php echo $entry['entry_type'] == 'credit' ? 'checked' : '' ; ?>>
                                        <label for="credit-<?php echo $entry['id']?>">Crédito</label>                
                                    </div>        
                
                                </fieldset>
                
                                <div class="box-input label-row">
                                    <label for="edit_entry_description-<?php echo $entry['id']?>" class="input-legend">Descrição da despesa</label>
                                    <textarea name="edit_entry_description" id="edit_entry_description-<?php echo $entry['id']?>"><?php echo isset( $entry['entry_description'] ) ? $entry['entry_description'] : '' ?></textarea>
                                </div>
                                
                                <fieldset class="input-legend">
                                    <legend class="input-legend">Recorrencia</legend>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_recurrence" id="edit_no_recurrence-<?php echo $entry['id']; ?>" value="no_recurrence" <?php echo $entry['entry_recurrence'] == 'no_recurrence' ? 'checked' : '' ; ?>>
                                        <label for="edit_no_recurrence-<?php echo $entry['id']; ?>">Sem recorrência</label>
                                    </div>        
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_recurrence" id="edit_installment-<?php echo $entry['id']; ?>" class="installment-recurrence" value="installment" <?php echo $entry['entry_recurrence'] == 'installment' ? 'checked' : '' ; ?>>
                                        <label for="edit_installment-<?php echo $entry['id']; ?>">Parcelar</label>            
                                    </div>        
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_recurrence" id="edit_fixed-<?php echo $entry['id']; ?>" value="fixed" <?php echo $entry['entry_recurrence'] == 'fixed' ? 'checked' : '' ; ?>>
                                        <label for="edit_fixed-<?php echo $entry['id']; ?>">Fixa</label>            
                                    </div>        
                                </fieldset>
                
                                <?php 
                                    if( $entry['entry_recurrence'] == 'installment' ) { ?>
                                        <div id="installments-<?php echo $entry['entry_recurrence']; ?>" class="box-input label-row installments-toggle">
                                            <label for="edit_entry_qty_installments" class="input-legend">Quantidade de parcelas</label>
                                            <input type="number" name="edit_entry_qty_installments" id="edit_entry_qty_installments" value="<?php echo $entry['entry_qty_installments']?>">
                                        </div>
                                    <?php }
                                ?>
                                    
                                <div class="box-input label-row">
                                    <label for="edit_entry_category-<?php echo $entry['id']?>" class="input-legend">Categoria</label>
                                    <input type="text" name="edit_entry_category" id="edit_entry_category-<?php echo $entry['id']?>" <?php echo isset( $entry['entry_category'] ) ? 'value="' . $entry['entry_category'] . '"' : '' ?>>
                                </div>
                                
                                <div class="box-input label-row">
                                    <label for="edit_entry_subcategory-<?php echo $entry['id']?>" class="input-legend">Subcategoria</label>
                                    <input type="text" name="edit_entry_subcategory" id="edit_entry_subcategory-<?php echo $entry['id']?>" <?php echo isset( $entry['entry_subcategory'] ) ? 'value="' . $entry['entry_subcategory'] . '"' : '' ?>>
                                </div>
                
                                <input type="hidden" name="entry_id" value="<?php echo $entry['id']; ?>">
                                
                                <button type="submit" class="btn btn-primary">Alterar</button>
                            </form>
            
                        </div>

                        <?php
                        continue;
                    } else { ?>
                        </div>
                        <div class="sumary-of-entries">
                            <div>Total de Lançamentos do dia: <?php echo $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, true ) + $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, false ) ?></div>
                            <div>Lançamentos Efetivados: <?php echo $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, true ) ?></div>
                            <div>Lançamentos NÃO Efetivados: <?php echo $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, false ) ?></div>
                        </div>
                        <div class="day-line-main"><?php print_r( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'] ); ?></div>
                        </div>
                        <div class="day-line-box">
                            <div class="entry-box-for-day">

                            <!-- card-sumary começa aqui -->
                            <div class="card-sumary <?php echo $entry['entry_nature'] == 'expense' ? 'expense' : 'revenue' ?> <?php echo $entry['entry_recurrence'] == 'installment' ? 'installment' : '' ?>" id="entry-card-<?php  echo $entry['id']; ?>">
                    
                            <div class="icon-card">
                                <?php echo $entry['entry_nature'] == 'expense' ? '<i class="fa-solid fa-arrow-trend-down"></i>' : '<i class="fa-solid fa-arrow-trend-up"></i>' ?>
                            </div>
                
                            <div class="box-input box-toggle">
                                <input type="checkbox" name="entry_id" class="effected-yes" id="entry-<?php echo $entry['id']; ?>" value="1" hidden <?php echo $entry['entry_effected']; ?> <?php echo $entry['entry_effected'] != '0' ? 'checked' : ''; ?>>
                                <label for="entry-<?php echo $entry['id']; ?>" class="input-legend event-key-toggle" id="" tabindex="0">
                                    <?php echo $entry['entry_expected_date'] ?>
                                    <div class="input-toggle"></div>
                                </label>
                            </div>
                
                            <div class="content-card-sumary">
                                <div class="arrown-one">
                                    <h3 class="entry-name"><?php echo $entry['entry_description'] ?></h3>
                                    <a href="" class="edit-entry">
                                        <span class="btn-icon">
                                            <i class="fas fa-pencil-alt"></i>
                                        </span>
                                    </a>
                                    
                                </div>
                        
                                <div class="arrown-two">
                                    <?php echo $this->format_entry_recurrence( $entry['entry_recurrence'] ); ?>
                
                                    <?php if( $entry['entry_recurrence'] == 'installment' ) { ?>
                                        - <?php echo $this->get_installment_for_month( $month, $entry['id_for_entry'] ); ?> / <?php echo $entry['entry_qty_installments'] . ' ( '. $this->sum_values_entry_installments( $entry['id_for_entry'] ) .' )' ?>
                                    <?php } ?>
                
                                </div>
                        
                                <div class="arrown-tree">
                                    <div class="entry-tags">
                                        <span class="entry-price"><?php echo $this->format_entry_value( $entry['entry_value'] ); ?></span>
                                        <span class="entry-category btn-icon"><i class="fa-brands fa-cc-mastercard"></i></i><?php echo $entry['entry_category']; ?></span>
                                    </div>
                                    <a href="" class="remove-entry" id="remove-entry-<?php echo $entry['id']; ?>">
                                        <span class="btn-icon">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                
                            <form action="edit_entry" method="post" class="form-edity-entry" id="form-edity-entry">
                                <fieldset class="input-legend">
                                    <legend class="input-legend">Natureza</legend>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_nature" id="edit_entry_nature-expense-<?php echo $entry['id']?>" value="expense" <?php echo $entry['entry_nature'] == 'expense' ? 'checked' : '' ; ?>>
                                        <label for="edit_entry_nature-expense-<?php echo $entry['id']?>">Despesa</label>
                                    </div>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_nature" id="edit_entry_nature-revenue-<?php echo $entry['id']?>" value="revenue" <?php echo $entry['entry_nature'] == 'revenue' ? 'checked' : '' ; ?>>
                                        <label for="edit_entry_nature-revenue-<?php echo $entry['id']?>">Receita</label>
                                    </div>
                                    
                                </fieldset>
                
                                <div class="box-input label-row">
                                    <label for="edit_entry_value_<?php echo $entry['id']; ?>" class="input-legend">Valor</label>
                                    <input type="tel" name="edit_entry_value" id="edit_entry_value_<?php echo $entry['id']; ?>" class="input-entry-value" step=".01" placeholder="0,00" <?php echo isset( $entry['entry_value'] ) ? 'value="' . str_replace( '.', ',', $entry['entry_value'] ) . '"' : '' ; ?>>
                                </div>
                
                                <div class="box-input label-row">
                                    <label for="edit_entry_expected_date-<?php echo $entry['id']?>" class="input-legend">Data de lançamento</label>
                                    <input type="date" name="edit_entry_expected_date" id="edit_entry_expected_date-<?php echo $entry['id']?>" <?php echo isset( $entry['entry_expected_date'] ) ? 'value="' . $this->get_entry_expected_date_html_for_id( $entry['id'] ) . '"' : '' ?>>
                                    
                                </div>
                
                                <fieldset class="input-legend">
                                    <legend class="input-legend">Tipo</legend>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_type" id="account-<?php echo $entry['id']?>" value="account" <?php echo $entry['entry_type'] == 'account' ? 'checked' : '' ; ?>>
                                        <label for="account-<?php echo $entry['id']?>">Conta corrente</label>    
                                    </div>        
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_type" id="credit-<?php echo $entry['id']?>" value="credit" <?php echo $entry['entry_type'] == 'credit' ? 'checked' : '' ; ?>>
                                        <label for="credit-<?php echo $entry['id']?>">Crédito</label>                
                                    </div>        
                
                                </fieldset>
                
                                <div class="box-input label-row">
                                    <label for="edit_entry_description-<?php echo $entry['id']?>" class="input-legend">Descrição da despesa</label>
                                    <textarea name="edit_entry_description" id="edit_entry_description-<?php echo $entry['id']?>"><?php echo isset( $entry['entry_description'] ) ? $entry['entry_description'] : '' ?></textarea>
                                </div>
                                
                                <fieldset class="input-legend">
                                    <legend class="input-legend">Recorrencia</legend>
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_recurrence" id="edit_no_recurrence-<?php echo $entry['id']; ?>" value="no_recurrence" <?php echo $entry['entry_recurrence'] == 'no_recurrence' ? 'checked' : '' ; ?>>
                                        <label for="edit_no_recurrence-<?php echo $entry['id']; ?>">Sem recorrência</label>
                                    </div>        
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_recurrence" id="edit_installment-<?php echo $entry['id']; ?>" class="installment-recurrence" value="installment" <?php echo $entry['entry_recurrence'] == 'installment' ? 'checked' : '' ; ?>>
                                        <label for="edit_installment-<?php echo $entry['id']; ?>">Parcelar</label>            
                                    </div>        
                
                                    <div class="box-input">
                                        <input type="radio" name="edit_entry_recurrence" id="edit_fixed-<?php echo $entry['id']; ?>" value="fixed" <?php echo $entry['entry_recurrence'] == 'fixed' ? 'checked' : '' ; ?>>
                                        <label for="edit_fixed-<?php echo $entry['id']; ?>">Fixa</label>            
                                    </div>        
                                </fieldset>
                
                                <?php 
                                    if( $entry['entry_recurrence'] == 'installment' ) { ?>
                                        <div id="installments-<?php echo $entry['entry_recurrence']; ?>" class="box-input label-row installments-toggle">
                                            <label for="edit_entry_qty_installments" class="input-legend">Quantidade de parcelas</label>
                                            <input type="number" name="edit_entry_qty_installments" id="edit_entry_qty_installments" value="<?php echo $entry['entry_qty_installments']?>">
                                        </div>
                                    <?php }
                                ?>
                                    
                                <div class="box-input label-row">
                                    <label for="edit_entry_category-<?php echo $entry['id']?>" class="input-legend">Categoria</label>
                                    <input type="text" name="edit_entry_category" id="edit_entry_category-<?php echo $entry['id']?>" <?php echo isset( $entry['entry_category'] ) ? 'value="' . $entry['entry_category'] . '"' : '' ?>>
                                </div>
                                
                                <div class="box-input label-row">
                                    <label for="edit_entry_subcategory-<?php echo $entry['id']?>" class="input-legend">Subcategoria</label>
                                    <input type="text" name="edit_entry_subcategory" id="edit_entry_subcategory-<?php echo $entry['id']?>" <?php echo isset( $entry['entry_subcategory'] ) ? 'value="' . $entry['entry_subcategory'] . '"' : '' ?>>
                                </div>
                
                                <input type="hidden" name="entry_id" value="<?php echo $entry['id']; ?>">
                                
                                <button type="submit" class="btn btn-primary">Alterar</button>
                            </form>
            
                            </div>

                        <?php
                        $i++;
                    }
                    
                    ?>
            
                <?php endforeach; ?>

                </div>
                <div class="sumary-of-entries">
                    <div>Total de Lançamentos do dia: <?php echo $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, true ) + $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, false ) ?></div>
                    <div>Lançamentos Efetivados: <?php echo $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, true ) ?></div>
                    <div>Lançamentos NÃO Efetivados: <?php echo $this->count_entrys_for_day( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'], $month, $year, false ) ?></div>

                </div>
                <div class="day-line-main"><?php print_r( $brApi->get_array_date_int( $daysWithEntry[$i] )['day'] ); ?></div>
            </div>

        </div>
    <?php endif ; }
}

?>