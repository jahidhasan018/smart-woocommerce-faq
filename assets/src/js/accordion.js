/**
 * Accordion controller.
 *
 * Initializes every [data-wsfq-accordion] within a root element:
 * - click toggles an item (aria-expanded + hidden)
 * - Enter / Space toggles the focused toggle
 * - Up / Down / Home / End navigate between questions
 * - [data-wsfq-expand-all] expands / collapses every item
 *
 * @param {Document|Element} root Root to search for accordions.
 * @return {void}
 */
export function initAccordion( root ) {
	const accordions = root.querySelectorAll( '[data-wsfq-accordion]' );
	accordions.forEach( ( accordion ) => {
		bindToggles( accordion );
		bindKeyboard( accordion );
		bindExpandAll( accordion );
	} );
}

/**
 * Bind click toggles.
 *
 * @param {HTMLElement} accordion Accordion element.
 * @return {void}
 */
function bindToggles( accordion ) {
	accordion.querySelectorAll( '[data-wsfq-toggle]' ).forEach( ( toggle ) => {
		toggle.addEventListener( 'click', () => {
			toggleItem( toggle );
		} );
	} );
}

/**
 * Bind keyboard navigation between toggles.
 *
 * @param {HTMLElement} accordion Accordion element.
 * @return {void}
 */
function bindKeyboard( accordion ) {
	const toggles = Array.from(
		accordion.querySelectorAll( '[data-wsfq-toggle]' )
	);

	toggles.forEach( ( toggle, index ) => {
		toggle.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Enter' || event.key === ' ' ) {
				event.preventDefault();
				toggleItem( toggle );
				return;
			}

			let next = null;
			switch ( event.key ) {
				case 'ArrowDown':
					next = toggles[ index + 1 ] ?? toggles[ 0 ];
					break;
				case 'ArrowUp':
					next =
						toggles[ index - 1 ] ?? toggles[ toggles.length - 1 ];
					break;
				case 'Home':
					next = toggles[ 0 ];
					break;
				case 'End':
					next = toggles[ toggles.length - 1 ];
					break;
				default:
					return;
			}

			event.preventDefault();
			next.focus();
		} );
	} );
}

/**
 * Bind the expand-all / collapse-all control.
 *
 * @param {HTMLElement} accordion Accordion element.
 * @return {void}
 */
function bindExpandAll( accordion ) {
	const control = accordion.querySelector( '[data-wsfq-expand-all]' );
	if ( ! control ) {
		return;
	}

	// The control renders as a sibling of the accordion wrapper.
	const container = control.closest(
		'.wsfq-accordion, .wsfq-expand-all'
	)?.parentNode;
	const scope = container ? container : accordion;

	control.addEventListener( 'click', () => {
		const willExpand = control.getAttribute( 'aria-expanded' ) !== 'true';
		scope
			.querySelectorAll( '[data-wsfq-toggle]' )
			.forEach( ( toggle ) => setItem( toggle, willExpand ) );
		control.setAttribute( 'aria-expanded', willExpand ? 'true' : 'false' );
	} );
}

/**
 * Toggle one item open/closed.
 *
 * @param {HTMLElement} toggle Toggle button.
 * @return {void}
 */
function toggleItem( toggle ) {
	const expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
	setItem( toggle, ! expanded );
}

/**
 * Set one item open/closed.
 *
 * @param {HTMLElement} toggle   Toggle button.
 * @param {boolean}     expanded Whether to expand.
 * @return {void}
 */
function setItem( toggle, expanded ) {
	const answerId = toggle.getAttribute( 'aria-controls' );
	const answer = answerId ? document.getElementById( answerId ) : null;

	toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
	if ( answer ) {
		if ( expanded ) {
			answer.removeAttribute( 'hidden' );
		} else {
			answer.setAttribute( 'hidden', '' );
		}
	}
}
