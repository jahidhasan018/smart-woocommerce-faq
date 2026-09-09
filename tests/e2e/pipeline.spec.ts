import { test, expect } from '@playwright/test';

/**
 * Hello-world e2e test — proves Playwright can reach the wp-env site.
 */
test( 'wp-env homepage is reachable', async ( { page } ) => {
	await page.goto( '/' );
	await expect( page.locator( 'body' ) ).toBeVisible();
} );
