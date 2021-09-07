<?php

class HttpDataSource implements DataSource {

	/** @var string */
	private $url;

	/**
	 * @param $url
	 */
	public function __construct( string $url ) {
		$this->url = $url;
	}

	/**
	 * @param string $path
	 * @param int $maxLength
	 *
	 * @return false|string
	 */
	public function read( string $path, int $maxLength ) {
		$c = curl_init();

		$urlString = $this->url . '/' . $path;
		curl_setopt( $c, CURLOPT_URL, $urlString );
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );

		$o = curl_exec( $c );

		if ( $o === false ) {
			throw new Exception( "Failed to read data from $urlString" );
		}

		curl_close( $c );

		return substr( $o, 0, $maxLength );
	}
}
