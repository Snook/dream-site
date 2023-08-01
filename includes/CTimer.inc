<?php



	/* ------------------------------------------------------------------------------------------------
	 *	Class: CTimer
	 *
	 *	Data:
	 *
	 *	Methods:
	 *		Start		(Starts it)
	 *		Stop		(Stops it)
	 *		View		(returns (int) seconds, (int) milliseconds)
	 *
	 *	Description:
	 *		Timer object good for benchmarking SQL queries and stuff.
	 *
	 *
	 * -------------------------------------------------------------------------------------------------- */

	class CTimer {
		// public

		var $iSecondsStart, $iSecondsEnd, $iMilliStart, $iMilliEnd;
		var $stime, $etime;
		var $_DEBUG = FALSE;

		// constructor

		function CTimer () {


				if ($this->_DEBUG) echo ("<br />Timer object created");

		}



		function Start () {

			$this->stime = microtime();


		}

		function Stop(){


			$this->etime = microtime();


		}


		function View () {



			list($iMs, $iSs) = split (" ", $this->stime);
			list($iMe, $iSe) = split (" ", $this->etime);

			$diff = ($iSe+$iMe) -($iSs+$iMs);

			if ($this->_DEBUG) echo "<br />start : $iSs $iMs";
			if ($this->_DEBUG) echo "<br />End : $iSe $iMe<br />";

			return 	round ($diff, 5); #array ($this->iSecDiff, $this->iMilliDiff);

		}

	}
	?>