<?php

namespace App\Models;

use MF\Model\Curl;

class BrAPI extends Curl {

    /**
     * 
     * 
     * retorna um array com os feriados nacionais por ano
     */
    public function get_nations_rolydays_for_year( $year ) {

        $data = $this->curl_api( 
            'https://brasilapi.com.br/api/feriados/v1/' . $year
        );
        
        try {

            return array_column( json_decode( $data ), 'date' );

        } catch (\Throwable $th) {
            echo '<pre>';
            echo '<h2>Por favor, verifique o correto funcionamento da api que está sendo consumida</h2>';
            print_r( $th );
            echo '</pre>';
            return;
        }
        
    }

    /**
     * 
     * 
     * verifica se uma determinada data é um feriado
     */
    public function is_rolyday( $date ) {

        //$rolyday = false;

        $arrayDate = $this->get_array_date_int( $date );
        
        if( array_search( $date, $this->get_nations_rolydays_for_year( $arrayDate['year'] ) ) != '' ) {
            return true;
        }

        return false;
    }

    /**
     * 
     * 
     * retorna uma string com 3 caracteries informando o dia da semana em inglês
     */
    public function get_weekday( $date ) {
        return date("D", strtotime( $date ) );
    }

    /**
     * 
     * 
     * verifica se uma determinada data é um FDS
     */
    public function is_weekend( $date ) {

        if( $this->get_weekday( $date ) == 'Sat' || $this->get_weekday( $date ) == 'Sun' ) {
            return true;
        }
        
        return false;
        
    }   

    /**
     * 
     * 
     * retorna true caso $date não seja um weekend ou rolyday, se não, retorna false;
     */
    public function is_workday( $date ) {

        if( gettype( $date ) == 'array' ) {
            $date = $this->transform_array_date_int_in_string( $date );
        }

        if( $this->is_rolyday( $date ) || $this->is_weekend( $date ) ) {
            return false;
        }
        
        return true;
    }

    /**
     * 
     * 
     * retorna o próximo dia
     */
    public function get_next_day( string $date ) {

        $prevDay = new \DateTime( $date );
        $prevDay->modify( '+1 day' );

        return $prevDay->format( 'Y-m-d' );
    }

    /**
     * 
     * 
     * retorna o próximo dia útil de uma determinada data, caso seja feriado ou FDS, se não, retorna a mesma data
     */
    public function get_next_workday( $date ) {

        if( gettype( $date ) == 'array' ) {
            $date = $this->transform_array_date_int_in_string( $date );
        }

        $nextDay = $this->get_next_day( $date );
        
        while ( ! $this->is_workday( $nextDay ) ) {
           $nextDay = $this->get_prev_day( $nextDay );
        }

        return  $nextDay;
    }

    /**
     * 
     * 
     * retorna o dia anterior a data
     */
    public function get_prev_day( $date ) {

        if( gettype( $date ) == 'string' ) {
            $date = $this->get_array_date_int( $date );
        }
        
        if( $date['day'] == 1 ) {
            $date['day'] = 31;

            if( $date['month'] == 1 ) {
                $date['month'] = 12;

                $date['year'] --;
            } else {
                $date['month'] --;
            }
        } else {
            $date['day'] --;
        }

        if( $this->is_valid_date( $date ) ) {
            return $this->transform_array_date_int_in_string( $date );
        } 

        while ( ! $this->is_valid_date( $date ) ) {
            $date['day'] --;
        }

        return $this->transform_array_date_int_in_string( $date );
    }


    /**
     * 
     * 
     * retorna o próximo dia útil de uma determinada data, caso seja feriado ou FDS, se não, retorna a mesma data
     */
    public function get_prev_workday( $date ) {

        if( gettype( $date ) == 'array' ) {
            $date = $this->transform_array_date_int_in_string( $date );
        }

        $prevDay = $this->get_prev_day( $date );
        
        while ( ! $this->is_workday( $prevDay ) ) {
           $prevDay = $this->get_prev_day( $prevDay );
        }

        return  $prevDay;
        
    }

    /**
     * 
     * 
     * retorna o último dia de um mês de uma determinada data;
     */
    public function get_last_day_month( $date ) {

        $lastWorkdayMonth = new \DateTime( $date );
        $lastWorkdayMonth->modify( 'last day of this month' );

        return $lastWorkdayMonth->format( 'Y-m-d' );
    }

    /**
     * 
     * 
     * retorna o último dia útil do mês de uma determinada data;
     */
    public function get_last_workday_month( $date ) {

        $lastWorkdayMonth = new \DateTime( $date );
        $lastWorkdayMonth->modify( 'last day of this month' );

        while ( $this->is_rolyday( $lastWorkdayMonth->format( 'Y-m-d' ) ) || $this->is_weekend( $lastWorkdayMonth->format( 'Y-m-d' ) ) ) {
            $lastWorkdayMonth->modify( '-1 day' );
        }

        return $lastWorkdayMonth->format( 'Y-m-d' );


    }

    /**
     * 
     * 
     * verifica se uma data existe ou não
     */
    public function is_valid_date( array $arrayDate ) {
        return checkdate( $arrayDate['month'], $arrayDate['day'], $arrayDate['year'] );
    }

    /**
     * 
     * 
     * retorna a própria data acrescida de um mês
     */
    public function get_next_month( $date, bool $returnArray = false ) {

        if( gettype( $date ) == 'string' ) {
            $date = $this->get_array_date_int( $date );
        }

        if( $date['month'] == 12 ) {
            $date['month'] = 1;

            $date['year'] ++;

        } else {
            $date['month'] ++;
        }

        if( ! $returnArray ) {

            if( ! $this->is_valid_date( $date ) ) {
                return $this->get_prev_day( $date );
            }
    
            return $this->transform_array_date_int_in_string( $date );

        } else {

            if( ! $this->is_valid_date( $date ) ) {
                return $this->get_array_date_int( $this->get_prev_day( $date ) );
            }

            return $date;
        }

        

    }


    /**
     * 
     * 
     * retorna um array com formatação dos valores do tipo integer com as chaves year, month e day
     */
    public function get_array_date_int( string $date ) {

        [$year, $month, $day] = explode( '-', $date );

        return $arrayDate = [
            'year'  =>  ( int ) $year,
            'month' =>  ( int ) $month,
            'day'   =>  ( int ) $day,
        ];
    }

    /**
     * 
     * 
     * transforma um arrayDate em string
     */
    public function transform_array_date_int_in_string( array $arrayDate ) {

        $arrayDate['year'] = strval( $arrayDate['year'] );
        $arrayDate['month'] = strval( $arrayDate['month'] );
        $arrayDate['day'] = strval( $arrayDate['day'] );

        $arrayDate['day'] = strlen( $arrayDate['day'] ) < 2 ? '0' . $arrayDate['day'] : $arrayDate['day'];
        $arrayDate['month'] = strlen( $arrayDate['month'] ) < 2 ? '0' . $arrayDate['month'] : $arrayDate['month'];

        return implode( '-', $arrayDate );
    }
}

?>