<?php

	$help_msg = <<<EOT
Usage:
php tweet_cleaned.php input_file output_file
========================================================
tweet_cleaned.php by Loke Kok Keong <kkloke@ukm.edu.my>
========================================================

EOT;

	//Read tweet input
	if(!isset($argv[1]) || !file_exists($argv[1])){
		echo <<<EOT
[ERROR] Missing tweet input file!

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

	$stats = array
	(
		'totalProcessedLines' => 0,
		'totalTweets' => 0,
		'totalExtractedTweets' => 0,
		'totalTweetsHasUnicode' => 0,
	);

	$handle = fopen($argv[1], "r");
	if ($handle){
		while (($line = fgets($handle)) !== false) {

			$tweetline = json_decode($line, true);

			if(is_array($tweetline)){
				$stats['totalTweets']++;
			}

			if(isset($tweetline['text']) && isset($tweetline['created_at'])){
				//<contents of "text" field> (timestamp: <contents of "created_at" field>)

				//replace some escaped chars.
				$replaces = array("\n", "\t");
				$tweetline['text'] = str_replace($replaces, " ", $tweetline['text']);

				$hasUnicode = false;
				if(strlen(utf8_decode($tweetline['text'])) != strlen($tweetline['text'])){
					$hasUnicode = true;
					$stats['totalTweetsHasUnicode']++;
				}

				//Remove unicode
				$clearUnicode = "";
				if($hasUnicode){
					//Reverse back to json string
					$jsonstr = json_encode(
						array
						(
							'text' => $tweetline['text']
						)
					);

					$unicode_parts = explode('\u', $jsonstr);
					if(count($unicode_parts) > 0){
						foreach($unicode_parts AS $unicode_part){
							$check_unicode = '\u' . substr($unicode_part, 0, 4);
							if(mb_detect_encoding($check_unicode)){
/*								
								echo <<<EOT
[INFO] Found unicode: {$check_unicode}

EOT;
*/
								$jsonstr = str_replace($check_unicode, '', $jsonstr);
							}
						}
						$clearUnicode = json_decode($jsonstr, true);
						if(isset($clearUnicode['text'])){
							$tweetline['text'] = $clearUnicode['text'];
						}
					}
				}

				$formatted_out = <<<EOT
{$tweetline['text']} (timestamp: {$tweetline['created_at']})
EOT;
				file_put_contents($outfile, $formatted_out . "\n", FILE_APPEND);

				// process the line read.
				$stats['totalProcessedTweet']++;
			}

			// process the line read.
			$stats['totalProcessedLines']++;			
		}

		fclose($handle);
	} else {
		// error opening the file.
		echo <<<EOT
[ERROR] Failed to open the input file.

EOT;
		exit();
	}

	//Print total unicoded tweets to output file
	file_put_contents($outfile, "\n" . $stats['totalTweetsHasUnicode'] . " tweets contained unicode.", FILE_APPEND);

//Verbose output
/*
		echo <<<EOT
[INFO] Total Processed Lines: {$stats['totalProcessedLines']}
[INFO] Total Tweets: {$stats['totalTweets']}
[INFO] Total Extracted Tweets: {$stats['totalExtractedTweets']}
[INFO] Total Tweets has unicode: {$stats['totalTweetsHasUnicode']}
[INFO] Done.

EOT;
*/

	exit();
?>