<?php

	//Env settings
	date_default_timezone_set('UTC');

	$help_msg = <<<EOT
Usage:
php average_degree.php input_file output_file
========================================================
average_degree.php by Loke Kok Keong <kkloke@ukm.edu.my>
========================================================

EOT;

	//Read tweet input
	if(!isset($argv[1]) || !file_exists($argv[1])){
		echo <<<EOT
[ERROR] Missing cleaned tweets file!

{$help_msg}
EOT;
		exit();
	}

	if(!isset($argv[2]) || !file_exists($argv[2])){
		echo <<<EOT
[ERROR] Missing output file location!

{$help_msg}
EOT;
		exit();
	}	

	$outfile = $argv[2];
	file_put_contents($outfile, "");

	$handle = fopen($argv[1], "r");
	if ($handle){
		$counted_tweets = array();
		$minTS = "";
		$maxTS = "";
		while (($line = fgets($handle)) !== false) {

			$tweetTS = "";
			$counted_tweets[] = $line;
			if(preg_match("/\(timestamp: (.*)\)/", $line, $matches)){
				$tweetTS = strtotime($matches[1]);

				if($minTS == "" && $maxTS == ""){
					$minTS = $tweetTS;
					$maxTS = $tweetTS;
				}				

				if($tweetTS > $maxTS){
					$maxTS = $tweetTS;
				}

				if(($maxTS - $minTS) > 60){
					array_shift($counted_tweets);
				}
			}

			
			$counted_nodes = array();
			$connected_edges = array();
			$totalNodeDegrees = 0;
			foreach($counted_tweets AS $ctweet){
				$tweet_hashtags = array();
				$hashtags = explode('#', $ctweet);
				if(count($hashtags) > 1){
					array_shift($hashtags);
					foreach($hashtags AS $hashtag){
						$terms = explode(" ", trim($hashtag));
						if(count($terms) > 0){
							$tweet_hashtags[] = trim($terms[0]);
							$key = trim($terms[0]);
							$counted_nodes[$key] = 1;
						}
					}
				}

				//More than two distinct tags
				$current_edges = array();
				$totalEdges = 0;

				if(count($tweet_hashtags) > 1){
					for($i = 0; $i < count($tweet_hashtags); $i++){
						for($j = 1; $j < count($tweet_hashtags); $j++){
							if($tweet_hashtags[$i] != $tweet_hashtags[$j]){
								$node_key = $tweet_hashtags[$i] . '_' . $tweet_hashtags[$j];
								$node_rkey = $tweet_hashtags[$j] . '_' . $tweet_hashtags[$i];
								if(!isset($current_edges[$node_rkey])){
									$current_edges[$node_key] = 1;
								}

								//Add to connected edges list
								if(!in_array($node_key, $connected_edges) && !in_array($node_rkey, $connected_edges)){
									$connected_edges[$node_key] = 1;
								}

							}
						}
					}

					//Calculate total degrees
					$nodeDegrees = array();
					foreach($connected_edges AS $k => $v){
						$acc_nodes = explode("_", $k);
						for($i=0; $i < count($acc_nodes); $i++){
							$key = $acc_nodes[$i];
							if(!isset($nodeDegrees[$key])){
								$nodeDegrees[$key]	= 1;
							} else {
								$nodeDegrees[$key]++;	
							}							
						}
					}

					foreach($nodeDegrees AS $k => $v){
						$totalNodeDegrees += $v;
					}
				}
			}

			//Writing output
			//echo 'Total Nodes: ' . count($counted_nodes) . "\n";
			//echo 'Total Edges: ' . count($connected_edges) . "\n";
			$avg_degrees = 0;
			if(count($counted_nodes) > 0){
				$avg_degrees = round($totalNodeDegrees / count($counted_nodes), 2);	
			}

			file_put_contents($outfile, number_format($avg_degrees, 2) . "\n", FILE_APPEND);
		}
		fclose($handle);
	} else {		
		// error opening the file.
		echo <<<EOT
[ERROR] Failed to open the input file.

EOT;
		exit();
	}

	exit();

?>