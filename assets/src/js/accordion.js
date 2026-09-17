/**
 * Accordion controller.
 *
 * Initializes every [data-wsfq-accordion] within a root element:
 * - click toggles an item (aria-expanded + hidden)
 * - Up / Down / Home / End navigate between questions
 * - [data-wsfq-expand-all] expands / collapses every item in its accordion
 *
 * The expand-all control renders as a sibling of the accordion rather than a
 * descendant, so it is bound separately and targets its accordion through
 * aria-controls.
 *
 * @param {Document|Element} root Root to search for accordions.
 * @return {void}
 */

/** Elements already wired, so a second init pass is a no-op. */
const bound = new WeakSet();

export function initAccordion( root ) {
	const scope =
		root && typeof root.querySelectorAll === 'function' ? root : document;

	scope
		.querySelectorAll( '[data-wsfq-accordion]' )
		.forEach( ( accordion ) => {
			if ( bound.has( accordion ) ) {
				return;
			}
			bound.add( accordion );

			bindToggles( accordion );
			bindKeyboard( accordion );
			syncExpandAll( accordion );
		} );

	scope.querySelectorAll( '[data-wsfq-expand-all]' ).forEach( ( control ) => {
		if ( bound.has( control ) ) {
			return;
		}
		bound.add( control );

		bindExpandAll( control );
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
			setItem( toggle, ! isExpanded( toggle ) );
			syncExpandAll( accordion );
		} );
	} );
}

/**
 * Bind keyboard navigation between toggles.
 *
 * Enter and Space are deliberately not handled: the toggles are real
 * <button> elements, so the browser already turns those keys into a click.
 * Handling them here as well would toggle twice.
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
 * Bind an expand-all / collapse-all control.
 *
 * @param {HTMLElement} control Expand-all button.
 * @return {void}
 */
function bindExpandAll( control ) {
	const accordion = accordionForControl( control );
	if ( ! accordion ) {
		return;
	}

	control.addEventListener( 'click', () => {
		// Read the current state rather than assuming, so the control always
		// flips: expand on the first press, collapse on the next.
		const expand = control.getAttribute( 'aria-expanded' ) !== 'true';

		accordion
			.querySelectorAll( '[data-wsfq-toggle]' )
			.forEach( ( toggle ) => {
				setItem( toggle, expand );
			} );

		setControlState( control, expand );
	} );
}

/**
 * Find the accordion a control targets.
 *
 * @param {HTMLElement} control Expand-all button.
 * @return {HTMLElement|null} Accordion element.
 */
function accordionForControl( control ) {
	const id = control.getAttribute( 'aria-controls' );
	if ( id ) {
		const target = document.getElementById( id );
		if ( target ) {
			return target;
		}
	}

	// Fallback for markup without aria-controls: the control sits directly
	// before its accordion.
	const wrapper = control.closest( '.wsfq-expand-all' );
	const sibling = wrapper ? wrapper.nextElementSibling : null;

	return sibling && sibling.hasAttribute( 'data-wsfq-accordion' )
		? sibling
		: null;
}

/**
 * Find the control that targets an accordion.
 *
 * @param {HTMLElement} accordion Accordion element.
 * @return {HTMLElement|null} Expand-all button.
 */
function controlForAccordion( accordion ) {
	if ( ! accordion.id ) {
		return null;
	}

	const id =
		typeof window !== 'undefined' && window.CSS && window.CSS.escape
			? window.CSS.escape( accordion.id )
			: accordion.id;

	return document.querySelector(
		`[data-wsfq-expand-all][aria-controls="${ id }"]`
	);
}

/**
 * Keep a control in step when items are toggled one at a time, so it reads
 * "Collapse all" once every item is open.
 *
 * @param {HTMLElement} accordion Accordion element.
 * @return {void}
 */
function syncExpandAll( accordion ) {
	const control = controlForAccordion( accordion );
	if ( ! control ) {
		return;
	}

	const toggles = Array.from(
		accordion.querySelectorAll( '[data-wsfq-toggle]' )
	);

	if ( ! toggles.length ) {
		return;
	}

	setControlState( control, toggles.every( isExpanded ) );
}

/**
 * Set the state of an expand-all control.
 *
 * The visible label swap is CSS driven off aria-expanded.
 *
 * @param {HTMLElement} control  Expand-all button.
 * @param {boolean}     expanded Whether everything is expanded.
 * @return {void}
 */
function setControlState( control, expanded ) {
	control.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
}

/**
 * Whether an item is expanded.
 *
 * @param {HTMLElement} toggle Toggle button.
 * @return {boolean} Whether expanded.
 */
function isExpanded( toggle ) {
	return toggle.getAttribute( 'aria-expanded' ) === 'true';
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
