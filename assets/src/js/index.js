/**
 * Smart FAQ frontend controller.
 *
 * Wires accessible accordion behavior: toggle on click, Enter/Space keyboard,
 * arrow-key navigation between questions, and expand-all / collapse-all.
 *
 * @package
 */

import '../scss/frontend.scss';
import { initAccordion } from './accordion';

document.addEventListener( 'DOMContentLoaded', () => {
	initAccordion( document );
} );
