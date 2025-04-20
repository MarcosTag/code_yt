<?php

namespace MF\Model;

abstract class Curl {

    public function curl_api( $url, $api_data_key = '' ) {

        // if( empty( $api_data_key ) ) {
        //     $api_data_key = $this->api_key;
        // }
    
        $ch = curl_init( $url );
    
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    

        if( !empty( $api_data_key ) ) {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [
                $api_data_key,
            ]);
        }
        
        $data = curl_exec( $ch );
        //$info = curl_getinfo( $ch );

        curl_close( $ch );
        
        return $data;
    }

}