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

/**
 * The expand-all control opens every item on the first press and closes them
 * all on the next, which is the behaviour that was previously dead because the
 * control renders outside the accordion it targets.
 */
test( 'expand all opens then collapses every item', async ( { page } ) => {
	await page.goto( '/faq-test/' );

	const accordion = page.locator( '[data-wsfq-accordion]' ).first();
	const control = page.locator( '[data-wsfq-expand-all]' ).first();
	const toggles = accordion.locator( '[data-wsfq-toggle]' );
	const answers = accordion.locator( '.wsfq-answer' );

	await expect( control ).toBeVisible();

	const count = await toggles.count();
	expect( count ).toBeGreaterThan( 1 );

	// Start collapsed.
	await expect( control ).toHaveAttribute( 'aria-expanded', 'false' );
	await expect( control ).toContainText( 'Expand all' );

	// First press: everything opens.
	await control.click();
	await expect( control ).toHaveAttribute( 'aria-expanded', 'true' );
	await expect( control ).toContainText( 'Collapse all' );

	for ( let i = 0; i < count; i++ ) {
		await expect( toggles.nth( i ) ).toHaveAttribute(
			'aria-expanded',
			'true'
		);
		await expect( answers.nth( i ) ).toBeVisible();
	}

	// Second press: everything closes.
	await control.click();
	await expect( control ).toHaveAttribute( 'aria-expanded', 'false' );
	await expect( control ).toContainText( 'Expand all' );

	for ( let i = 0; i < count; i++ ) {
		await expect( toggles.nth( i ) ).toHaveAttribute(
			'aria-expanded',
			'false'
		);
		await expect( answers.nth( i ) ).toBeHidden();
	}
} );

/**
 * Opening every item by hand flips the control to the collapsed state, so the
 * control never lies about what a press will do.
 */
test( 'expand all stays in step with individual toggles', async ( {
	page,
} ) => {
	await page.goto( '/faq-test/' );

	const accordion = page.locator( '[data-wsfq-accordion]' ).first();
	const control = page.locator( '[data-wsfq-expand-all]' ).first();
	const toggles = accordion.locator( '[data-wsfq-toggle]' );
	const count = await toggles.count();

	for ( let i = 0; i < count; i++ ) {
		await toggles.nth( i ).click();
	}

	await expect( control ).toHaveAttribute( 'aria-expanded', 'true' );
	await expect( control ).toContainText( 'Collapse all' );

	// One collapse-all press closes the lot.
	await control.click();
	await expect( control ).toHaveAttribute( 'aria-expanded', 'false' );

	for ( let i = 0; i < count; i++ ) {
		await expect( toggles.nth( i ) ).toHaveAttribute(
			'aria-expanded',
			'false'
		);
	}
} );
