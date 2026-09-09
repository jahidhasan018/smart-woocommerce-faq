#!/usr/bin/env bash
#
# Downloads the WordPress core test library and creates the test DB so
# integration tests can run against a real WP install.
#
# Usage:
#   bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-db]
#
# Local (wp-env exposes MySQL on localhost:3306, root/root by default):
#   bin/install-wp-tests.sh wordpress_test root root localhost latest
#
# CI (GitHub MySQL service container):
#   bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest
#
# Inspired by the classic WP-CLI wordpress-install-tests script.
set -euo pipefail

DB_NAME=${1:?db-name required}
DB_USER=${2:?db-user required}
DB_PASS=${3:?db-pass required}
DB_HOST=${4:-localhost}
WP_VERSION=${5:-latest}
SKIP_DB=${6:-false}

# Split a "host:port" value (wp-env exposes MySQL on a host port).
if [[ "$DB_HOST" == *":"* ]]; then
	DB_HOSTNAME="${DB_HOST%%:*}"
	DB_PORT="${DB_HOST##*:}"
else
	DB_HOSTNAME="$DB_HOST"
	DB_PORT="3306"
fi

TMPDIR_WP=${TMPDIR:-/tmp}/wsfq-wp-tests
WP_TESTS_DIR="$TMPDIR_WP/wordpress-tests-lib"
WP_CORE_DIR="$TMPDIR_WP/wordpress"

download() {
	if [ -f "$2" ]; then
		return
	fi
	echo "Downloading $1 ..."
	curl -sSL "$1" -o "$2.tmp"
	mv "$2.tmp" "$2"
}

if [ "$SKIP_DB" != "true" ]; then
	echo "== Preparing test database =="
	if command -v mysqladmin >/dev/null 2>&1; then
		mysqladmin --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOSTNAME" --port="$DB_PORT" drop "$DB_NAME" --force 2>/dev/null || true
		mysqladmin --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOSTNAME" --port="$DB_PORT" create "$DB_NAME"
	else
		echo "mysqladmin not found locally — skipping DB creation (assume CI service DB exists)."
	fi
fi

echo "== Fetching WordPress $WP_VERSION core =="
if [ "$WP_VERSION" == "latest" ]; then
	WP_TESTS_TAG="trunk"
else
	WP_TESTS_TAG="tags/$WP_VERSION"
fi

mkdir -p "$WP_CORE_DIR" "$WP_TESTS_DIR"

echo "== Fetching WordPress test library ($WP_TESTS_TAG) =="
SVN_URL="https://develop.svn.wordpress.org/$WP_TESTS_TAG/tests/phpunit"
if command -v svn >/dev/null 2>&1; then
	svn export --force --quiet "$SVN_URL/" "$WP_TESTS_DIR"
	if [ ! -f "$WP_CORE_DIR/wp-settings.php" ]; then
		ARCHIVE="$TMPDIR_WP/wordpress.tar.gz"
		download "https://wordpress.org/wordpress-$WP_VERSION.tar.gz" "$ARCHIVE"
		tar --strip-components=1 -xzf "$ARCHIVE" -C "$WP_CORE_DIR"
	fi
elif command -v git >/dev/null 2>&1; then
	echo "svn not found — cloning wordpress-develop (git) instead."
	if [ ! -d "$TMPDIR_WP/wp-develop/.git" ]; then
		git clone --quiet --depth 1 --branch "$WP_TESTS_TAG" \
			https://github.com/WordPress/wordpress-develop.git "$TMPDIR_WP/wp-develop"
	fi
	cp -R "$TMPDIR_WP/wp-develop/tests/phpunit/." "$WP_TESTS_DIR/"
	# Git layout: config sample at repo root, WP core in the repo's own src/.
	if [ -f "$TMPDIR_WP/wp-develop/wp-tests-config-sample.php" ]; then
		cp "$TMPDIR_WP/wp-develop/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config-sample.php"
	fi
	if [ -d "$TMPDIR_WP/wp-develop/src" ]; then
		cp -R "$TMPDIR_WP/wp-develop/src/." "$WP_CORE_DIR/"
	fi
else
	echo "Neither svn nor git found. Install one or set WP_TESTS_DIR manually."
	exit 1
fi

echo "== Writing wp-tests-config.php =="
if [ -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
	rm -f "$WP_TESTS_DIR/wp-tests-config.php"
fi

WP_CORE_DIR_ESCAPED=$(printf '%s' "$WP_CORE_DIR" | sed 's/:/\\:/g; s/\\/\\\\/g')

sed -e "s|dirname( __FILE__ ) . '/src/'|'$WP_CORE_DIR_ESCAPED/'|" \
	-e "s/youremptytestdbnamehere/$DB_NAME/" \
	-e "s/yourusernamehere/$DB_USER/" \
	-e "s/yourpasswordhere/$DB_PASS/" \
	-e "s|localhost|${DB_HOSTNAME}:${DB_PORT}|" \
	"$WP_TESTS_DIR/wp-tests-config-sample.php" > "$WP_TESTS_DIR/wp-tests-config.php"

echo "== Downloading PHPUnit 9.6 (phar) for the WP test suite =="
PHPUNIT_PHAR="$TMPDIR_WP/phpunit-9.6.phar"
if [ ! -f "$PHPUNIT_PHAR" ]; then
	download "https://phar.phpunit.de/phpunit-9.6.phar" "$PHPUNIT_PHAR"
fi
chmod +x "$PHPUNIT_PHAR" 2>/dev/null || true

echo "Done. Run integration tests with:"
echo "  WP_TESTS_DIR=$WP_TESTS_DIR composer test:integration"