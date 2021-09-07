<?php

/**
 * The system obtains temperature data from a remote source,
 * compares it with a given threshold and controls a remote heating
 * unit by switching it on and off. It does so only within a time
 * period configured on a remote service (or other source)
 *
 * This is purpose-built crap.
 */
class ScheduleManager {
	/** @var HeatingManager */
	private $heatingManager;

	/** @var DataSource */
	private $scheduleSource;

	/** @var float $threshold */
	private $threshold;

	/** @var float $tolerance */
	private $tolerance;

	/**
	 * @param HeatingManager $heatingManager
	 * @param DataSource $scheduleSource
	 * @param float $threshold
	 * @param float $tolerance
	 */
	public function __construct(
		HeatingManager $heatingManager,
		DataSource     $scheduleSource,
		float          $threshold,
		float          $tolerance
	) {
		$this->heatingManager = $heatingManager;
		$this->scheduleSource = $scheduleSource;

		$this->threshold = $threshold;
		$this->tolerance = $tolerance;
	}

	/**
	 * This method is the entry point into the code. You can assume that it is
	 * called at regular interval with the appropriate parameters.
	 */
	public static function manage( HeatingManager $hM, string $threshold ): void {
		$manager = new ScheduleManager(
			$hM,
			new HttpDataSource( 'http://timer.home:9990' ),
			$threshold,
			1.0
		);

		try {
			$manager->manageHeating();
		} catch ( Exception $e ) {
			echo 'Caught exception: ', $e->getMessage(), "\n";
		}
	}

	public function manageHeating() {
		$timeofday = gettimeofday( true );
		$startHour = self::getStartHour();
		$endHour = self::getEndHour();

		$active = ( $timeofday > $startHour && $timeofday < $endHour );

		// In "inactive" hours, turn on the heating if the temperature falls below 5°C
		$threshold = $active ? $this->threshold : 5;

		// NOTE: Since the temperature and the threshold are both floats,
		//       the temperature will never be "exactly right", so the
		//       heating would constantly turn on and off. We use a
		//       tolerance setting to define a range of acceptable temperatures.
		$offset = $this->tolerance/2;
		$this->heatingManager->manageHeating( $threshold - $offset, $threshold + $offset );
	}

	private function getEndHour(): float {
		return $this->scheduleSource->read( 'end', 5 );
	}

	private function getStartHour(): float {
		return $this->scheduleSource->read( 'start', 5 );
	}
}
