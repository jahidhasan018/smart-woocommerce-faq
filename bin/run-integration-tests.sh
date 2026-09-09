#!/usr/bin/env bash
#
# Runs the integration test suite under the PHPUnit 9.6 phar (the WP core test
# library targets PHPUnit 9.x). Requires bin/install-wp-tests.sh to have run.
#
#   WP_TESTS_DIR=/path/to/wordpress-tests-lib composer test:integration
#
# WP_TESTS_DIR is required. Optionally set WP_TEST_DB_HOST (host:port) if it has
# drifted from the generated wp-tests-config.php (wp-env re-assigns MySQL ports).
#
set -euo pipefail

if [ -z "${WP_TESTS_DIR:-}" ]; then
	echo "error: WP_TESTS_DIR is not set. Run bin/install-wp-tests.sh first." >&2
	exit 1
fi

if [ -n "${WP_TEST_DB_HOST:-}" ]; then
	# Rewrite DB_HOST in the generated config to match the current wp-env port.
	CONFIG="$WP_TESTS_DIR/wp-tests-config.php"
	sed -i.bak "s|define( 'DB_HOST', '[^']*' )|define( 'DB_HOST', '${WP_TEST_DB_HOST}' )|" "$CONFIG"
	rm -f "$CONFIG.bak"
fi

TMPDIR_WP=${TMPDIR:-/tmp}/wsfq-wp-tests
PHPUNIT_PHAR="$TMPDIR_WP/phpunit-9.6.phar"

if [ ! -f "$PHPUNIT_PHAR" ]; then
	echo "error: $PHPUNIT_PHAR not found. Run bin/install-wp-tests.sh first." >&2
	exit 1
fi

exec php "$PHPUNIT_PHAR" --configuration phpunit.integration.xml.dist "$@"