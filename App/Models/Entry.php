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
        $dateTime = new \DateTime( $this->__get( 'entry_expected_date' ) );
        // $recurrence = $this->__get( 'entry_recurrence' );
        $installments = $this->__get( 'entry_qty_installments' );
        
        $id_for_entry = $this->format_id_for_entry();

        $parcelas = $this->calculate_installments( $this->__get( 'entry_value' ), $installments );
        // $nextWorkday = '';
        
        for ( $i = 0; $i < $installments; $i++ ) { 

            $expectedDate = $brApi->get_next_workday( $dateTime->format( 'Y-m-d' ) );
            $entryMonth = new \DateTime( $brApi->get_next_workday( $dateTime->format( 'Y-m-d' ) ) );

            /**
             * 
             * 
             * Se um lançamento de categoria Imposto for lançado no último dia do mês, verifica se o dia é útil e decrementa o dia para o dia útil anterior a data do lançamento
             */
            if( $this->__get( 'entry_category' ) == 'Impostos' && ( $dateTime->format( 'Y-m-d' ) == $brApi->get_last_day_month( $dateTime->format( 'Y-m-d' ) ) || $entryMonth->format( 'm' ) != $dateTime->format( 'm' ) || $brApi->get_array_date_int( $dateTime->format( 'Y-m-d' ) )['month'] != $brApi->get_array_date_int( $this->__get( 'entry_expected_date' ) )['month'] + $i ) ) {

                $adjustmentDateTime = new \DateTime( $dateTime->format( 'Y-m-d' ) );
                $adjustmentDateTime->modify( '-1 day' );

                $expectedDate = $brApi->get_last_workday_month( $adjustmentDateTime->format( 'Y-m-d' ) );
            
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

            $dateTime->modify( '+1 month' );

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

    public function get_format_display_entry_sumary( $month, $year ) {

        $entrys = $this->get_entrys_for_month( $month, $year );

        if( count( $entrys ) >= 1 ) :  ?>
    
        <div id="display-entry-sumary">
    
        <?php foreach( $entrys as $key => $entry ) : ?>

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
                                <label for="entry_qty_installments-<?php echo $entry['entry_recurrence']; ?>" class="input-legend">Quantidade de parcelas</label>
                                <input type="number" name="entry_qty_installments-<?php echo $entry['entry_recurrence']; ?>" id="entry_qty_installments-<?php echo $entry['entry_recurrence']; ?>" value="<?php echo $entry['entry_qty_installments']?>">
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
    
        <?php endforeach; ?>

        </div>
    <?php endif ; }
}

?>