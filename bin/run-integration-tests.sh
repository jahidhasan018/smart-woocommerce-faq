#!/usr/bin/env bash
#
# Runs the integration test suite under the PHPUnit 9.6 phar (the WP core test
# library targets PHPUnit 9.x). Requires bin/install-wp-tests.sh to have run.
#
#   WP_TESTS_DIR=/path/to/wordpress-tests-lib composer test:integration
#
set -euo pipefail

if [ -z "${WP_TESTS_DIR:-}" ]; then
	echo "error: WP_TESTS_DIR is not set. Run bin/install-wp-tests.sh first." >&2
	exit 1
fi

TMPDIR_WP=${TMPDIR:-/tmp}/wsfq-wp-tests
PHPUNIT_PHAR="$TMPDIR_WP/phpunit-9.6.phar"

if [ ! -f "$PHPUNIT_PHAR" ]; then
	echo "error: $PHPUNIT_PHAR not found. Run bin/install-wp-tests.sh first." >&2
	exit 1
fi

exec php "$PHPUNIT_PHAR" --configuration phpunit.integration.xml.dist "$@"