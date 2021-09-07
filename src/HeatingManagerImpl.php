<?php

class HeatingManagerImpl implements HeatingManager {
	/** @var string */
	private $address;

	/** @var int */
	private $port;

	/** @var DataSource */
	private $temperatureSource;

	/**
	 * @return HeatingManagerImpl
	 */
	public static function newDefaultManager() {
		return new HeatingManagerImpl(
			'heater.home',
			9999,
			new HttpDataSource( 'http://probe.home:9999' )
		);
	}

	/**
	 * @param string $address
	 * @param int $port
	 * @param DataSource $temperatureSource
	 */
	public function __construct(
		string     $address,
		int        $port,
		DataSource $temperatureSource
	) {
		$this->address = $address;
		$this->port = $port;
		$this->temperatureSource = $temperatureSource;
	}

	function manageHeating( float $minTemp, float $maxTemp ): void {
		$dt = floatval( $this->temperatureSource->read( 'temp', 5 ) );

		if ( $dt < $minTemp ) {
			$this->sendCommand( 'on' );
		} elseif ( $maxTemp ) {
			$this->sendCommand( 'off' );
		}
	}

	/**
	 * @param string $command The command to send
	 */
	private function sendCommand( $command ) {
		if ( !( $socket = socket_create( AF_INET, SOCK_STREAM, 0 ) ) ) {
			throw new Exception( 'could not create socket' );
		}
		$this->address = 'heater.home';
		if ( !socket_connect( $socket, $this->address, $this->port ) ) {
			throw new Exception( 'could not connect!' );
		}

		socket_send( $socket, $command, strlen( $command ), 0 );
		socket_close( $socket );

		return $socket;
	}
}
