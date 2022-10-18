#!/bin/bash
#
# Exclude pluggable functions in WordPress stubs.
#
# WordPress uses conditional function and class definition for override purposes.
#
# See: https://github.com/szepeviktor/phpstan-wordpress/tree/v1.1.2#dirty-corner-faq
# See: https://codex.wordpress.org/Pluggable_Functions
#

# Exclude pluggable functions overridden by roots/wp-password-bcrypt
fix_for_wp_password_bcrypt()
{
	sed -e 's/function wp_check_password/function __wp_check_password/' \
		-e 's/function wp_hash_password/function __wp_hash_password/' \
		-e 's/function wp_set_password/function __wp_set_password/' \
		-i '' vendor/php-stubs/wordpress-stubs/wordpress-stubs.php
}

fix_for_wp_password_bcrypt

echo "Excluding pluggable functions overridden by roots/wp-password-bcrypt"
