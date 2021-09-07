<?php

class HeatingManagerImpl implements HeatingManager {
	private $port;

	public function __construct() {
		$this->address = 'heater.home'; // TODO: require parameter
		$this->port = 9999; // TODO: require parameter
	}

	function manageHeating( string $t, string $threshold, bool $active ): void {
		// TODO: $threshold and $active should be properties, not parameters
		// TODO: $t and $threshold should not be strings
		$dt = floatval( $t );
		$dThreshold = floatval( $threshold );
		try {
			if ( !$active ) {
				// do nothing
			} elseif ( $dt < $dThreshold ) {
				$this->sendCommand( 'on' );
			} elseif ( $dt > $dThreshold ) {
				$this->sendCommand( 'off' );
			}
		} catch ( Exception $e ) {
			// TODO: don't catch here!
			echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	}

	/**
	 * @param string $command The command to send
	 */
	private function sendCommand( $command ) {
		if ( !( $socket = socket_create( AF_INET, SOCK_STREAM, 0 ) ) ) {
			die( 'could not create socket' ); // TODO: throw instead!
		}
		$this->address = 'heater.home';
		if ( !socket_connect( $socket, $this->address, $this->port ) ) {
			die( 'could not connect!' ); // TODO: throw instead!
		}

		socket_send( $socket, $command, strlen( $command ), 0 );
		socket_close( $socket );

		return $socket;
	}
}
