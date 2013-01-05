<?php

	/**
	 * Profiler Data Class
	 *
	 * @package Scabbia
	 * @subpackage UtilityExtensions
	 */
	class profilerData {
		/**
		 * @ignore
		 */
		public $name;
		/**
		 * @ignore
		 */
		public $parameters;
		/**
		 * @ignore
		 */
		public $source;
		/**
		 * @ignore
		 */
		public $startTime;
		/**
		 * @ignore
		 */
		public $startMemory;
		/**
		 * @ignore
		 */
		public $consumedTime;
		/**
		 * @ignore
		 */
		public $consumedMemory;

		/**
		 * @ignore
		 */
		public function __construct($uName, $uParameters = null, $uSource) {
			$this->name = $uName;
			$this->parameters = $uParameters;
			$this->source = $uSource;
		}

		/**
		 * @ignore
		 */
		public function start() {
			$this->startTime = microtime(true);
			$this->startMemory = memory_get_peak_usage();
		}

		/**
		 * @ignore
		 */
		public function stop() {
			$this->consumedTime = microtime(true) - $this->startTime;
			$this->consumedMemory = memory_get_peak_usage() - $this->startMemory;
		}

		/**
		 * @ignore
		 */
		public function addParameters($uNewParameters) {
			$this->parameters += $uNewParameters;
		}
	}

?>