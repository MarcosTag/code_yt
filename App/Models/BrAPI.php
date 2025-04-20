<?php

namespace App\Models;

use MF\Model\Curl;

class BrAPI extends Curl {

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

    public function is_rolyday( $date ) {

        $rolyday = false;

        $arrayDate = explode( '-', $date );

        
        if( array_search( $date, $this->get_nations_rolydays_for_year( $arrayDate[0] ) ) ) {
            $rolyday = true;
        }

        return $rolyday;
    }

    public function get_weekday( $date ) {
        return date("D", strtotime( $date ) );
    }

    public function is_weekend( $date ) {

        if( $this->get_weekday( $date ) == 'Sat' || $this->get_weekday( $date ) == 'Sun' ) {
            return true;
        }
        
        return false;
        
    }   

}

?>