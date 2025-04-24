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

        $rolyday = false;

        $arrayDate = explode( '-', $date );

        
        if( array_search( $date, $this->get_nations_rolydays_for_year( $arrayDate[0] ) ) ) {
            $rolyday = true;
        }

        return $rolyday;
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
     * retorna o próximo dia útil de uma determinada data, caso seja feriado ou FDS, se não, retorna a mesma data
     */
    public function get_next_workday( $date ) {

        if( ! ( $this->is_rolyday( $date ) || $this->is_weekend( $date ) ) ) {

            return $date;

        } else {

            $nextWorkDay = new \DateTime( $date );
            
            while ( $this->is_rolyday( $nextWorkDay->format( 'Y-m-d' ) ) || $this->is_weekend( $nextWorkDay->format( 'Y-m-d' ) ) ) {
                $nextWorkDay->modify( '+1 day' );
            }

            return $nextWorkDay->format( 'Y-m-d' );
        }
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

        if( ! ( $this->is_rolyday( $date ) || $this->is_weekend( $date ) ) ) {

            return $date;

        } else {

            $lastWorkdayMonth = new \DateTime( $date );
            $lastWorkdayMonth->modify( 'last day of this month' );

            while ( $this->is_rolyday( $lastWorkdayMonth->format( 'Y-m-d' ) ) || $this->is_weekend( $lastWorkdayMonth->format( 'Y-m-d' ) ) ) {
                $lastWorkdayMonth->modify( '-1 day' );
            }

            return $lastWorkdayMonth->format( 'Y-m-d' );

        }
    }

}

?>