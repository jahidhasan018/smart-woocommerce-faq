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

	// Switch to the Display tab.
	await page.getByRole( 'tab', { name: 'Display', exact: true } ).click();

	// The product-page location is a single choice, so it renders as a select.
	const productPosition = page.locator( '.wsfq-field select' );
	await expect( productPosition ).toBeVisible();
	await expect( productPosition.locator( 'option' ) ).toHaveCount( 6 );

	// The page-level locations stay independent toggles.
	await expect(
		page.getByText( 'Shop and category pages', { exact: true } )
	).toBeVisible();
	await expect(
		page.getByText( 'Checkout page', { exact: true } )
	).toBeVisible();

	// Choosing a product location persists through the REST API.
	await productPosition.selectOption( 'product_tab' );
	await expect( page.locator( '.wsfq-save-status' ) ).toContainText(
		'All changes saved'
	);
	await page.reload();
	await page.getByRole( 'tab', { name: 'Display', exact: true } ).click();
	await expect( page.locator( '.wsfq-field select' ) ).toHaveValue(
		'product_tab'
	);
} );
