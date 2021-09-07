<?php

interface DataSource {

	/**
	 * @param string $path
	 * @param int $maxLength
	 *
	 * @return string
	 */
	public function read( string $path, int $maxLength );
}
