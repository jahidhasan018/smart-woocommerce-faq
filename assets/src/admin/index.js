/**
 * Smart FAQ settings app — entry point.
 *
 * Mounted into #wsfq-settings-root on the wsfq-smart-faq admin page. Talks to
 * wsfq/v1/settings via apiFetch.
 *
 * @package
 */

import { render, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	TabPanel,
	Card,
	CardBody,
	Notice,
	Spinner,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

import './style.scss';
import { DisplayTab } from './tabs/display';

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
 * Settings app root.
 */
function SettingsApp() {
	const [ settings, setSettings ] = useState( null );
	const [ notice, setNotice ] = useState( null );
	const [ saving, setSaving ] = useState( false );

	useEffect( () => {
		loadSettings()
			.then( ( data ) => setSettings( data ) )
			.catch( () =>
				setNotice( {
					type: 'error',
					text: __(
						'Failed to load settings.',
						'smart-woocommerce-faq'
					),
				} )
			);
	}, [] );

	if ( ! settings ) {
		return (
			<Card>
				<CardBody>
					<Spinner />
				</CardBody>
			</Card>
		);
	}

	/**
	 * Persist a partial settings update.
	 *
	 * @param {Object} update Partial settings.
	 */
	const handleSave = ( update ) => {
		setSaving( true );
		saveSettings( update )
			.then( ( data ) => {
				setSettings( data );
				setNotice( {
					type: 'success',
					text: __( 'Settings saved.', 'smart-woocommerce-faq' ),
				} );
			} )
			.catch( () =>
				setNotice( {
					type: 'error',
					text: __(
						'Failed to save settings.',
						'smart-woocommerce-faq'
					),
				} )
			)
			.finally( () => setSaving( false ) );
	};

	return (
		<>
			{ notice && (
				<Notice
					status={ notice.type }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.text }
				</Notice>
			) }
			<TabPanel
				className="wsfq-settings-tabs"
				tabs={ [
					{
						name: 'general',
						title: __( 'General', 'smart-woocommerce-faq' ),
					},
					{
						name: 'ai',
						title: __( 'AI Providers', 'smart-woocommerce-faq' ),
					},
					{
						name: 'display',
						title: __( 'Display', 'smart-woocommerce-faq' ),
					},
					{
						name: 'design',
						title: __( 'Design', 'smart-woocommerce-faq' ),
					},
					{
						name: 'advanced',
						title: __( 'Advanced', 'smart-woocommerce-faq' ),
					},
				] }
			>
				{ ( tab ) => {
					switch ( tab.name ) {
						case 'display':
							return (
								<DisplayTab
									settings={ settings }
									saving={ saving }
									onSave={ handleSave }
								/>
							);
						case 'general':
							return (
								<Card>
									<CardBody>
										<p>
											{ __(
												'General settings will appear here.',
												'smart-woocommerce-faq'
											) }
										</p>
									</CardBody>
								</Card>
							);
						case 'ai':
							return (
								<Card>
									<CardBody>
										<p>
											{ __(
												'AI provider settings will appear here.',
												'smart-woocommerce-faq'
											) }
										</p>
									</CardBody>
								</Card>
							);
						case 'design':
							return (
								<Card>
									<CardBody>
										<p>
											{ __(
												'Design settings will appear here.',
												'smart-woocommerce-faq'
											) }
										</p>
									</CardBody>
								</Card>
							);
						case 'advanced':
							return (
								<Card>
									<CardBody>
										<p>
											{ __(
												'Import/export and advanced settings will appear here.',
												'smart-woocommerce-faq'
											) }
										</p>
									</CardBody>
								</Card>
							);
						default:
							return null;
					}
				} }
			</TabPanel>
		</>
	);
}

const root = document.getElementById( 'wsfq-settings-root' );
if ( root ) {
	render( <SettingsApp />, root );
}
