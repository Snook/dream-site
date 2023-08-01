<?php

	/*****************************************
	 *
	 * Created By: CarlS, DavidB
	 * Date: 9/22/2006
	 * 
	 * This file contains miscellaneous math
	 * functions.
	 *
	 ****************************************/



	function distance($lat1, $lon1, $lat2, $lon2)
	{
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		if (is_nan($dist))
			return 0;

		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;

		return round($miles, 1);
	}








?>
