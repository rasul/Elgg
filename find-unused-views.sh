#!/bin/bash

if [ $# -eq 1 ] && [ $1 = '--help' ]
then
	echo "Usage: `basename $0` [--help]"
	echo "Attempt to determine what views are not being used in a particular Elgg installation."
	echo "Used views are determined by scanning for elgg_view() calls. Like find-unused-strings.sh,"
	echo "this script handles only view names passed as literal strings. It does not try to evaluate PHP variables."

	exit 0
fi


# find all core and plugin views
find ./views ./mod/*/views -iname '*.php' | \
# strip "./[mod/*plugin name*]/views/*view type*/" from the beginning of paths. also strip the .php suffix
sed 's:\./\(mod/[^/]\+/\)\?views/[^/]\+/\([^.]*\).php:\2:' | sort | uniq > /tmp/views_avail

# find used views
grep --recursive --exclude-dir='.svn' --no-filename --color 'elgg_view *(' * | sed -n "s:.*elgg_view *(\('\([^'$]\+\)'\|"'"\([^"$]\+\)"\).*:\2\3:p' | sort | uniq > /tmp/views_used

# find the diff, strip off the first 3 lines of the diff
diff -u /tmp/views_avail /tmp/views_used | tail -n +4 > /tmp/views_diff

# find unused views
cat /tmp/views_diff | grep '^-' | cut -c 2- > /tmp/views_unused

# find unavailable views
cat /tmp/views_diff | grep '^+' | cut -c 2- > /tmp/views_unavail

echo 'The following views may be unused in your current Elgg setup:'
cat /tmp/views_unused
echo
