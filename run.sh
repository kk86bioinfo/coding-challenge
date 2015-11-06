#!/usr/bin/env bash

# example of the run script for running the word count

# I'll execute my programs, with the input directory tweet_input and output the files in the directory tweet_output
#python ./src/words_tweeted.py ./tweet_input/tweets.txt ./tweet_output/ft1.txt
#python ./src/median_unique.py ./tweet_input/tweets.txt ./tweet_output/ft2.txt

if which php >/dev/null; then

php ./php/tweets_cleaned.php ./data-gen/tweets.txt ./tweet_output/ft1.txt
php ./php/average_degree.php ./tweet_output/ft1.txt ./tweet_output/ft2.txt
    
else
    echo 'php is not installed. Please installed php-cli and re-run the script again.'
fi




