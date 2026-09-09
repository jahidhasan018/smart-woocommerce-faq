import { test, expect } from '@playwright/test';

/**
 * Verifies the FAQ accordion renders and toggles on a standalone page using the
 * [wsfq_faq_all] shortcode (theme-independent surface).
 */
test( 'FAQ accordion toggles and expands', async ( { page } ) => {
	await page.goto( '/faq-test/' );

	const accordion = page.locator( '[data-wsfq-accordion]' ).first();
	await expect( accordion ).toBeVisible();

	const firstToggle = accordion.locator( '[data-wsfq-toggle]' ).first();
	await expect( firstToggle ).toHaveAttribute( 'aria-expanded', 'false' );

	// Derive the answer id from the toggle's aria-controls rather than hard-coding.
	const answerId = await firstToggle.getAttribute( 'aria-controls' );
	const firstAnswer = page.locator( `#${ answerId }` );
	await expect( firstAnswer ).toBeHidden();

	// Click to expand.
	await firstToggle.click();
	await expect( firstToggle ).toHaveAttribute( 'aria-expanded', 'true' );
	await expect( firstAnswer ).toBeVisible();

	// Click again to collapse.
	await firstToggle.click();
	await expect( firstToggle ).toHaveAttribute( 'aria-expanded', 'false' );
	await expect( firstAnswer ).toBeHidden();
} );
