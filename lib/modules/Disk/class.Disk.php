<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Memory Module for LoadAvg
* 
* @version SVN: $Id$
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/

class Disk extends LoadAvg
{
	public $logfile; // Stores the logfile name & path

	/**
	 * __construct
	 *
	 * Class constructor, appends Module settings to default settings
	 *
	 */
	public function __construct()
	{
		$this->setSettings(__CLASS__, parse_ini_file(strtolower(__CLASS__) . '.ini.php', true));
	}

	/**
	 * logDiskUsageData
	 *
	 * Retrives data and logs it to file
	 *
	 * @param string $type type of logging default set to normal but it can be API too.
	 * @return string $string if type is API returns data as string
	 *
	 * NEED TO START LOGGING SWAP AS WELL
	 *
	 */

	public function logData( $type = false )
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;
				
		$drive = $settings['settings']['drive'];
		
		if (is_dir($drive)) {
				
			$spaceBytes = disk_total_space($drive);
			$freeBytes = disk_free_space($drive);

			$usedBytes = $spaceBytes - $freeBytes;
						
			//$freeBytes = dataSize($Bytes);
			//$percentBytes = $freeBytes ? round($freeBytes / $totalBytes, 2) * 100 : 0;
		}

		//get disk space used for swap here
		exec("free -o | grep Swap | awk -F' ' '{print $3}'", $swapBytes);

		$swapBytes = implode(chr(26), $swapBytes);

	    $string = time() . '|' . $usedBytes  . '|' . $spaceBytes . '|' . $swapBytes . "\n";
		
		$filename = sprintf($this->logfile, date('Y-m-d'));
		$this->safefilerewrite($filename,$string,"a",true);

		if ( $type == "api")
			return $string;
		else
			return true;		
	}


	/**
	 * getDiskSize
	 *
	 * Gets size of disk based on logger and offsets
	 *
	 * @return the disk size
	 *
	 */
	
	public function getDiskSize( $chartArray, $totalchartArray  )
	{

			//need to get disk size in order to process data properly
			//is it better before loop or in loop
			//what happens if you resize disk on the fly ? in loop would be better
			$diskSize = 0;

			//map the collectd disk size to our disk size here
			//subtract 1 from size of array as a array first value is 0 but gives count of 1

			//set the disk size - bad way to do this in the loop !
			//but we read this data from the drive logs
			//$diskSize = $data[2] / 1048576;
			if ( LOGGER == "collectd")
			{	
				$diskSize = ( 	$chartArray[$totalchartArray-1][1] + 
								$chartArray[$totalchartArray-1][2] + 
								$chartArray[$totalchartArray-1][3] ) / 1048576;
			} else {

				$diskSize = $chartArray[$totalchartArray-1][2] / 1048576;
			}

			return $diskSize;

	}

	/**
	 * reMapData
	 *
	 * remap data based on loogger
	 *
	 * @data sent over by caller
	 * @return none
	 *
	 */
	
	public function reMapData( &$data )
	{
		if ( LOGGER == "collectd")
		{

			$used =  $data[2] + $data[3]; 
			$space = $data[1] + $data[2] + $data[3];

			$data[1] = $used;
			$data[2] = $space; //ignored as computed above one time... not per dataset

		}
	}

	/**
	 * getDiskUsageData
	 *
	 * Gets data from logfile, formats and parses it to pass it to the chart generating function
	 *
	 * @return array $return data retrived from logfile
	 *
	 */
	
	public function getUsageData(  )
	{
		$class = __CLASS__;
		$settings = LoadAvg::$_settings->$class;

		//define some core variables here
		$dataArray = $dataRedline = $usage = array();
		$dataArrayOver = $dataArrayOver_2 = array();
		$dataArraySwap = array();

		//display switch used to switch between view modes - data or percentage
		// true - show MB
		// false - show percentage
		$displayMode =	$settings['settings']['display_limiting'];	
		
		/*
		 * grab the log file data needed for the charts as array of strings
		 * takes logfiles(s) and gives us back contents
		 */		

		$contents = array();
		$logStatus = LoadAvg::parseLogFileData($this->logfile, $contents);

		/*
		 * build the chartArray array here as array of arrays needed for charting
		 * takes in contents and gives us back chartArray
		 */

		$chartArray = array();
		$totalchartArray = 0;

		if ($logStatus) {

			//takes the log file and parses it into chartable data 
			$this->getChartData ($chartArray, $contents );
			$totalchartArray = (int)count($chartArray);
		}

		/*
		 * now we loop through the dataset and build the chart
		 * uses chartArray which contains the dataset to be charted
		 */

		if ( $totalchartArray > 0 ) {

			//get the size of the disk we are charting
			$diskSize = $this->getDiskSize($chartArray, $totalchartArray);


			// main loop to build the chart data
			for ( $i = 0; $i < $totalchartArray; ++$i) {	
				$data = $chartArray[$i];

				// clean data for missing values
				$redline = ($this->checkRedline($data));

				//remap data if it needs mapping based on different loggers
				$this->reMapData($data);

				if (  (!$data[1]) ||  ($data[1] == null) || ($data[1] == "")  )
					$data[1]=0.0;
				
				//used to filter out redline data from usage data as it skews it
				//usage is used to calculate view perspectives
				if (!$redline) {
					$usage[] = ( $data[1] / 1048576 );

					if ($data[2] > 0)
						$percentage_used =  ( $data[1] / $data[2] ) * 100;
					else
						$percentage_used =  0;						
				
				} else {
					$percentage_used = 0;
				}

				$timedata = (int)$data[0];
				$time[( $data[1] / 1048576 )] = date("H:ia", $timedata);

				$usageCount[] = ($data[0]*1000);

				if ( LoadAvg::$_settings->general['chart_type'] == "24" ) 
					$timestamps[] = $data[0];

				if ($displayMode == 'true' ) {
					// display data using MB
					$dataArray[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1048576 ) ."]";

					if ( $percentage_used > $settings['settings']['overload'])
						$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", ". ( $data[1] / 1048576 ) ."]";

				} else {
					// display data using percentage
					$dataArray[$data[0]] = "[". ($data[0]*1000) .", ". $percentage_used ."]";

					if ( $percentage_used > $settings['settings']['overload'])
						$dataArrayOver[$data[0]] = "[". ($data[0]*1000) .", ". $percentage_used ."]";
				}
			}


			//echo '<pre>PRESETTINGS</pre>';
			//echo '<pre>';var_dump($usage);echo'</pre>';

			/*
			 * now we collect data used to build the chart legend 
			 * 
			 */

			if ($displayMode == 'true' )
			{
				$disk_high = max($usage);
				$disk_low  = min($usage); 
				$disk_mean = array_sum($usage) / count($usage);

				//to scale charts
				$ymax = $disk_high;
				$ymin = $disk_low;

			} else {

				$disk_high=   ( max($usage) / $diskSize ) * 100 ;				
				$disk_low =   ( min($usage) / $diskSize ) * 100 ;
				$disk_mean =  ( (array_sum($usage) / count($usage)) / $diskSize ) * 100 ;

				//these are the min and max values used when drawing the charts
				//can be used to zoom into datasets
				$ymin = 0;
				$ymax = 100;

			}

			$disk_high_time = $time[max($usage)];
			$disk_low_time = $time[min($usage)];

			$disk_latest = ( ( $usage[count($usage)-1]  )    )    ;		

			$disk_total = $diskSize;
			$disk_free = $disk_total - $disk_latest;
		
			$variables = array(
				'disk_high' => number_format($disk_high,2),
				'disk_high_time' => $disk_high_time,
				'disk_low' => number_format($disk_low,2),
				'disk_low_time' => $disk_low_time,
				'disk_mean' => number_format($disk_mean,2),
				'disk_total' => number_format($disk_total,1),
				'disk_free' => number_format($disk_free,1),
				'disk_latest' => number_format($disk_latest,1),
			);

			/*
			 * all data to be charted is now cooalated into $return
			 * and is returned to be charted
			 * 
			 */

			$return  = array();
			
			// get legend layout from ini file		
			$return = $this->parseInfo($settings['info']['line'], $variables, __CLASS__);

			if (count($dataArrayOver) == 0) { $dataArrayOver = null; }

			ksort($dataArray);
			if (!is_null($dataArrayOver)) ksort($dataArrayOver);

			$dataString = "[" . implode(",", $dataArray) . "]";
			$dataOverString = is_null($dataArrayOver) ? null : "[" . implode(",", $dataArrayOver) . "]";

			$return['chart'] = array(
				'chart_format' => 'line',
				'chart_avg' => 'avg',
				
				'ymin' => $ymin,
				'ymax' => $ymax,
				'xmin' => date("Y/m/d 00:00:01"),
				'xmax' => date("Y/m/d 23:59:59"),
				'mean' => $disk_mean,
				'dataset_1' => $dataString,
				'dataset_1_label' => 'Disk Usage',

				'dataset_2' => $dataOverString,
				'dataset_2_label' => 'Overload',
				
				'overload' => $settings['settings']['overload']
			);

			return $return;	
			
		} else {

			return false;
		}
	}

	/**
	 * genChart
	 *
	 * Function witch passes the data formatted for the chart view
	 *
	 * @param array @moduleSettings settings of the module
	 * @param string @logdir path to logfiles folder
	 *
	 */

	public function genChart($moduleSettings, $logdir)
	{
		$charts = $moduleSettings['chart']; //contains args[] array from modules .ini file

		$module = __CLASS__;
		$i = 0;
		foreach ( $charts['args'] as $chart ) {
			$chart = json_decode($chart);

			//get data range we are looking at - need to do some validation in this routine
			$dateRange = $this->getDateRange();

			//get the log file NAME or names when there is a range
			//returns multiple files when multiple log files
			$this->logfile = $this->getLogFile($chart->logfile,  $dateRange, $module );

			// find out main function from module args that generates chart data
			// in this module its getData above
			$caller = $chart->function;

			//check if function takes settings via GET url_args 
			$functionSettings =( (isset($moduleSettings['module']['url_args']) && isset($_GET[$moduleSettings['module']['url_args']])) ? $_GET[$moduleSettings['module']['url_args']] : '2' );

			if (!empty($this->logfile)) {

				$i++;				
				$logfileStatus = true;

				//call modules main function and pass over functionSettings
				if ($functionSettings) {
					$chartData = $this->$caller(  $functionSettings );
				} else {
					$chartData = $this->$caller(  );
				}

			} else {

				//no log file so draw empty charts
				$i++;				
				$logfileStatus = false;

			}

			//now draw chart to screen
			include APP_PATH . '/views/chart.php';
		}
	}

}
