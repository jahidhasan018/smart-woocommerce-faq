import { defineConfig } from '@playwright/test';

const WP_BASE_URL = process.env.WP_BASE_URL || 'http://localhost:8888';

export default defineConfig( {
	testDir: './tests/e2e',
	fullyParallel: false,
	workers: 1,
	timeout: 60_000,
	reporter: [ [ 'list' ] ],
	use: {
		baseURL: WP_BASE_URL,
		headless: true,
	},
	projects: [
		{
			name: 'wp-env',
			use: {
				baseURL: WP_BASE_URL,
			},
		},
	],
} );
