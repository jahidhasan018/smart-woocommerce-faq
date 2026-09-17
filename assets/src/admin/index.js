/**
 * Smart FAQ settings app — entry point.
 *
 * Mounted into #wsfq-settings-root on the wsfq-smart-faq admin page. Talks to
 * wsfq/v1/settings via apiFetch.
 *
 * @package
 */

import { createRoot, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TabPanel, Notice, Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

import './style.scss';
import { DisplayTab } from './tabs/display';

const TABS = [
	{ name: 'general', title: __( 'General', 'smart-woocommerce-faq' ) },
	{ name: 'ai', title: __( 'AI Providers', 'smart-woocommerce-faq' ) },
	{ name: 'display', title: __( 'Display', 'smart-woocommerce-faq' ) },
	{ name: 'design', title: __( 'Design', 'smart-woocommerce-faq' ) },
	{ name: 'advanced', title: __( 'Advanced', 'smart-woocommerce-faq' ) },
];

/**
 * Placeholder copy for tabs whose features have not shipped yet.
 */
const EMPTY_STATES = {
	general: {
		icon: 'dashicons-admin-generic',
		title: __( 'General', 'smart-woocommerce-faq' ),
		text: __(
			'Store-wide defaults for the FAQ library. Not built yet.',
			'smart-woocommerce-faq'
		),
	},
	ai: {
		icon: 'dashicons-lightbulb',
		title: __( 'AI Providers', 'smart-woocommerce-faq' ),
		text: __(
			'Connect OpenAI, Anthropic, or Google with your own API key to draft answers. Not available yet.',
			'smart-woocommerce-faq'
		),
	},
	design: {
		icon: 'dashicons-art',
		title: __( 'Design', 'smart-woocommerce-faq' ),
		text: __(
			'Colours, spacing, and type for the accordion, applied without a rebuild. Not available yet.',
			'smart-woocommerce-faq'
		),
	},
	advanced: {
		icon: 'dashicons-admin-tools',
		title: __( 'Advanced', 'smart-woocommerce-faq' ),
		text: __(
			'Import and export your FAQ library as JSON. Not available yet.',
			'smart-woocommerce-faq'
		),
	},
};

/**
 * Load settings via the REST API.
 *
 * @return {Promise<Object>} Settings payload.
 */
function loadSettings() {
	return apiFetch( { path: '/wsfq/v1/settings' } );
}

/**
 * Save settings via the REST API.
 *
 * @param {Object} settings Settings to save.
 * @return {Promise<Object>} Saved settings.
 */
function saveSettings( settings ) {
	return apiFetch( {
		path: '/wsfq/v1/settings',
		method: 'POST',
		data: settings,
	} );
}

/**
 * Shown while settings load, so the page does not jump when they arrive.
 *
 * @return {import('react').JSX.Element} Skeleton element.
 */
function SettingsSkeleton() {
	return (
		<div className="wsfq-skeleton">
			<span className="screen-reader-text">
				{ __( 'Loading settings…', 'smart-woocommerce-faq' ) }
			</span>
			<div className="wsfq-skeleton__panel" aria-hidden="true">
				<div className="wsfq-skeleton__tabs" />
				<div className="wsfq-skeleton__body">
					<div className="wsfq-skeleton__heading" />
					<div className="wsfq-skeleton__row" />
					<div className="wsfq-skeleton__row" />
					<div className="wsfq-skeleton__heading wsfq-skeleton__heading--gap" />
					<div className="wsfq-skeleton__row" />
					<div className="wsfq-skeleton__row" />
				</div>
			</div>
		</div>
	);
}

/**
 * Shown when settings could not be fetched.
 *
 * @param {Object}   root0         Props.
 * @param {Function} root0.onRetry Retry callback.
 * @return {import('react').JSX.Element} Error element.
 */
function LoadErrorPanel( { onRetry } ) {
	return (
		<Notice
			status="error"
			isDismissible={ false }
			actions={ [
				{
					label: __( 'Try again', 'smart-woocommerce-faq' ),
					onClick: onRetry,
					variant: 'primary',
				},
			] }
		>
			{ __(
				'Could not load your settings. Check your connection, then try again.',
				'smart-woocommerce-faq'
			) }
		</Notice>
	);
}

/**
 * Placeholder body for a tab that is not built yet.
 *
 * @param {Object} root0      Props.
 * @param {string} root0.name Tab name.
 * @return {import('react').JSX.Element|null} Empty state element.
 */
function EmptyTab( { name } ) {
	const state = EMPTY_STATES[ name ];

	if ( ! state ) {
		return null;
	}

	return (
		<div className="wsfq-empty">
			<span
				className={ `dashicons ${ state.icon } wsfq-empty__icon` }
				aria-hidden="true"
			/>
			<h2 className="wsfq-empty__title">{ state.title }</h2>
			<p className="wsfq-empty__text">{ state.text }</p>
		</div>
	);
}

/**
 * Settings save readout.
 *
 * @param {Object} root0        Props.
 * @param {string} root0.status One of 'saving' or 'saved'.
 * @return {import('react').JSX.Element} Status element.
 */
function SaveStatus( { status } ) {
	const saving = status === 'saving';

	return (
		<div className="wsfq-settings__status" role="status">
			<span className="wsfq-save-status">
				{ saving ? (
					<Spinner />
				) : (
					<span
						className="dashicons dashicons-yes-alt wsfq-save-status__icon"
						aria-hidden="true"
					/>
				) }
				{ saving
					? __( 'Saving changes…', 'smart-woocommerce-faq' )
					: __( 'All changes saved', 'smart-woocommerce-faq' ) }
			</span>
		</div>
	);
}

/**
 * Settings app root.
 */
function SettingsApp() {
	const [ settings, setSettings ] = useState( null );
	const [ loadError, setLoadError ] = useState( false );
	const [ reloadKey, setReloadKey ] = useState( 0 );
	const [ saveStatus, setSaveStatus ] = useState( 'saved' );
	const [ saveError, setSaveError ] = useState( false );

	useEffect( () => {
		let active = true;

		setLoadError( false );

		loadSettings()
			.then( ( data ) => {
				if ( active ) {
					setSettings( data );
					setSaveStatus( 'saved' );
				}
			} )
			.catch( () => {
				if ( active ) {
					setLoadError( true );
				}
			} );

		return () => {
			active = false;
		};
	}, [ reloadKey ] );

	if ( loadError ) {
		return (
			<LoadErrorPanel
				onRetry={ () => setReloadKey( ( key ) => key + 1 ) }
			/>
		);
	}

	if ( ! settings ) {
		return <SettingsSkeleton />;
	}

	const saving = saveStatus === 'saving';

	/**
	 * Persist a partial settings update.
	 *
	 * @param {Object} update Partial settings.
	 */
	const handleSave = ( update ) => {
		setSaveStatus( 'saving' );
		setSaveError( false );

		saveSettings( update )
			.then( ( data ) => {
				setSettings( data );
				setSaveStatus( 'saved' );
			} )
			.catch( () => {
				setSaveStatus( 'saved' );
				setSaveError( true );
			} );
	};

	return (
		<>
			{ saveError ? (
				<Notice status="error" onRemove={ () => setSaveError( false ) }>
					{ __(
						'Could not save your change, so the setting was left unchanged. Try again.',
						'smart-woocommerce-faq'
					) }
				</Notice>
			) : (
				<SaveStatus status={ saveStatus } />
			) }

			<TabPanel className="wsfq-settings-tabs" tabs={ TABS }>
				{ ( tab ) =>
					tab.name === 'display' ? (
						<DisplayTab
							settings={ settings }
							saving={ saving }
							onSave={ handleSave }
						/>
					) : (
						<EmptyTab name={ tab.name } />
					)
				}
			</TabPanel>
		</>
	);
}

const root = document.getElementById( 'wsfq-settings-root' );
if ( root ) {
	createRoot( root ).render( <SettingsApp /> );
}
