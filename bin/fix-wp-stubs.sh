#!/bin/bash
#
# Exclude pluggable functions in WordPress stubs.
#
# WordPress uses conditional function and class definition for override purposes.
#
# See: https://github.com/szepeviktor/phpstan-wordpress/tree/v1.1.2#dirty-corner-faq
# See: https://codex.wordpress.org/Pluggable_Functions
#

if [[ "$OSTYPE" == "darwin"* ]]; then
	SEDOPTION="-i ''"
else
	SEDOPTION='-i'
fi;

#
# Exclude pluggable functions overridden by roots/wp-password-bcrypt
#
# Arguments:
#    1. ...files - The PHP files to patch.
#
fix_for_wp_password_bcrypt() {
	for file in "$@"; do
		sed -e 's/function wp_check_password/function __wp_check_password/' \
			-e 's/function wp_hash_password/function __wp_hash_password/' \
			-e 's/function wp_set_password/function __wp_set_password/' \
			$SEDOPTION $file
	done
}

FILE=vendor/php-stubs/wordpress-stubs/wordpress-stubs.php
if [[ -f "$FILE" ]]; then
	echo "- Excluding pluggable functions overridden by roots/wp-password-bcrypt"
	fix_for_wp_password_bcrypt $FILE
fi
