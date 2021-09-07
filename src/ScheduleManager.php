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

	/** @var DataSource */
	private $temperatureSource;

	/** @var string $threshold */
	private $threshold;

	/**
	 * @param HeatingManager $heatingManager
	 * @param DataSource $scheduleSource
	 * @param DataSource $temperatureSource
	 * @param string $threshold
	 */
	public function __construct(
		HeatingManager $heatingManager,
		DataSource     $scheduleSource,
		DataSource     $temperatureSource,
		string         $threshold
	) {
		$this->heatingManager = $heatingManager;
		$this->scheduleSource = $scheduleSource;
		$this->temperatureSource = $temperatureSource;

		// TODO: $threshold should not be a string. But HeatingManager::manageHeating wants one.
		$this->threshold = $threshold;
	}

	/**
	 * This method is the entry point into the code. You can assume that it is
	 * called at regular interval with the appropriate parameters.
	 */
	public static function manage( HeatingManager $hM, string $threshold ): void {
		$manager = new ScheduleManager(
			$hM,
			new HttpDataSource( 'http://timer.home:9990' ),
			new HttpDataSource( 'http://probe.home:9999' ),
			$threshold
		);

		$manager->manageHeating();
	}

	public function manageHeating() {
		$timeofday = gettimeofday( true );
		$startHour = self::getStartHour();
		$endHour = self::getEndHour();

		$t = $this->getTemperature();

		$active = ( $timeofday > $startHour && $timeofday < $endHour );

		// XXX: During "inactive" hours, we send $active = false.
		//      But that does not turn off the heat, it just disables HeatingManager.
		//      If the heat was on at the end of the "active" period, it will stay on
		//      during the "inactive" period. That's probably a bug.
		$this->heatingManager->manageHeating( $t, $this->threshold, $active );
	}

	private function getTemperature(): string {
		// TODO: should not return a string. But HeatingManager::manageHeating wants one.
		return $this->temperatureSource->read( 'temp', 4 );
	}

	private function getEndHour(): float {
		return $this->scheduleSource->read( 'end', 5 );
	}

	private function getStartHour(): float {
		return $this->scheduleSource->read( 'start', 5 );
	}
}
