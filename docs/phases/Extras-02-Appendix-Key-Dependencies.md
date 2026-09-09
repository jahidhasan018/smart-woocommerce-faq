# Appendix — Key Dependencies


**Composer (require-dev unless noted):**
`phpunit/phpunit`, `brain/monkey`, `wp-coding-standards/wpcs`, `phpcompatibility/phpcompatibility-wp`, `szepeviktor/phpstan-wordpress`, `phpstan/phpstan`, `yoast/phpunit-polyfills`

**npm (devDependencies):**
`@wordpress/env`, `@wordpress/scripts`, `@wordpress/element`, `@wordpress/components`, `@wordpress/api-fetch`, `@wordpress/i18n`, `@wordpress/e2e-test-utils-playwright`, `@playwright/test`

**Runtime PHP:** none beyond WordPress/WooCommerce core APIs — deliberately zero bundled runtime dependencies, to avoid the conflict/prefixing problem entirely.