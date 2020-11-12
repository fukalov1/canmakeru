<?php

namespace App\Lib\KitOnline;

class Curl
{
    private $url = '';

    private $ch = NULL;

    public function __construct( $url )
    {
        $this->url = $url;

        $this->init();

    }

    public function __destruct()
    {
        curl_close( $this->ch );
    }

    public function init()
    {
        $this->ch = curl_init( $this->url );

        curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US)" );
    }

    public function setHttps( )
    {
        curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, 0 );
    }

    public function setPost( $postdata )
    {
        curl_setopt( $this->ch, CURLOPT_POST, 1 );
        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $postdata );
    }

    public function exec( )
    {
        $ret = curl_exec( $this->ch );

        return $ret;
    }
}
