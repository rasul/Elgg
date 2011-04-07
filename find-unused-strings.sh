#!/bin/bash

script_parameters="plugin_name"

if [ $# -ne 1 ]
then
	echo "Usage: `basename $0` $script_parameters"
	echo "Determine what language strings are not being used in a plugin."

	exit 1
fi

plugin=$1
plugin_dir=./mod/$plugin
lang_file="$plugin_dir/languages/en.php"

if [ ! -f $lang_file ]
then
	echo 'This plugin does not have english translations.'
	exit 0
fi

# find availabe strings
cat $lang_file | grep '=>' | sed "s:[^']*'\([^']*\)'[ 	]*=>.*:\1:" | sort | uniq > /tmp/strings_avail

# find used strings
grep -R --exclude-dir='.svn' --color "elgg_echo *(" $plugin_dir/* | sed -n "s:.*elgg_echo *(\('\([^'$]\+\)'\|"'"\([^"$]\+\)"\).*:\2\3:p' | sort | uniq > /tmp/strings_used

diff --unified=0 /tmp/strings_avail /tmp/strings_used | tail -n +4 | grep '^-' | cut -c 2- > /tmp/strings_unused

if [ `wc -l < /tmp/strings_unused` -eq 0 ]
then
	echo 'This plugin contains no unused strings.'
	exit 0
fi

echo "The following strings may be unused in this plugin:"
cat /tmp/strings_unused
