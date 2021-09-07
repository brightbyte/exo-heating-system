<?php

interface HeatingManager {
	function manageHeating( string $t, string $threshold, boolean $active ): void;
}
