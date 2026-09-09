import { test, expect } from '@playwright/test';

/**
 * Verifies the admin settings page mounts and shows the tabs.
 */
test( 'admin settings page shows tabs', async ( { page } ) => {
	// Log in as wp-env admin.
	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', 'admin' );
	await page.fill( '#user_pass', 'password' );
	await page.click( '#wp-submit' );

	// Go to the settings page.
	await page.goto( '/wp-admin/admin.php?page=wsfq-smart-faq' );

	// The React app should mount the root and render tab labels.
	await expect( page.locator( '#wsfq-settings-root' ) ).toBeVisible();
	await expect( page.getByText( 'Display', { exact: true } ) ).toBeVisible();
	await expect(
		page.getByText( 'AI Providers', { exact: true } )
	).toBeVisible();

	// Switch to the Display tab and confirm position toggles render.
	await page.getByText( 'Display', { exact: true } ).click();
	await expect(
		page.getByText( 'Product tab', { exact: true } )
	).toBeVisible();
	await expect(
		page.getByText( 'Checkout page', { exact: true } )
	).toBeVisible();
} );
