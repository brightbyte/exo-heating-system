<?php

interface HeatingManager {
	function manageHeating( float $minTemp, float $maxTemp ): void;
}
