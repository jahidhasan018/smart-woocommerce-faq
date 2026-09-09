#!/usr/bin/env bash
#
# Seeds demo WooCommerce products + demo FAQs so any agent/dev gets working
# test data instantly. Run via wp-env:
#
#   npm run env:cli -- wp wsfq demo:seed   (once the plugin CLI exists)
#   # or directly:
#   npm run env:cli -- bash /var/www/html/wp-content/plugins/smart-woocommerce-faq/bin/seed-demo-data.sh
#
set -euo pipefail

WP="wp"

echo "== Seeding demo products =="

# Create 3 demo products (simple), idempotently by slug.
seed_product() {
	local slug="$1" name="$2" price="$3"
	local existing
	existing="$($WP post list --post_type=product --name="$slug" --field=ID --format=ids)"
	if [ -n "$existing" ]; then
		echo "Product '$name' already exists (ID $existing), skipping."
		return
	fi
	$WP wc product create \
		--name="$name" \
		--slug="$slug" \
		--regular_price="$price" \
		--type=simple \
		--status=publish \
		--user=admin >/dev/null
	echo "Created product '$name'."
}

seed_product "smart-faq-demo-bluetooth" "Smart Bluetooth Speaker" "49.99"
seed_product "smart-faq-demo-bottle"      "Insulated Water Bottle"       "24.95"
seed_product "smart-faq-demo-headphones"  "Noise-Cancelling Headphones"  "89.00"

echo "== Seeding demo FAQs =="

# Guard: the wsfq_faq CPT is registered in Phase 2. Until then, FAQ seeding is a no-op.
if ! "$WP" post-type exists wsfq_faq >/dev/null 2>&1; then
	echo "wsfq_faq post type not registered yet (Phase 2) — skipping FAQ seeding."
	exit 0
fi

seed_faq() {
	local slug="$1" question="$2" answer="$3"
	local existing
	existing="$($WP post list --post_type=wsfq_faq --name="$slug" --field=ID --format=ids)"
	if [ -n "$existing" ]; then
		echo "FAQ '$question' already exists (ID $existing), skipping."
		return
	fi
	$WP post create \
		--post_type=wsfq_faq \
		--post_title="$question" \
		--post_content="$answer" \
		--post_name="$slug" \
		--post_status=publish >/dev/null
	echo "Created FAQ '$question'."
}

seed_faq "demo-shipping"        "How long does shipping take?"        "Standard shipping takes 3–5 business days."
seed_faq "demo-returns"         "What is your return policy?"         "Returns are accepted within 30 days in original condition."
seed_faq "demo-warranty"        "Does this come with a warranty?"     "Yes — a 12-month limited warranty is included."
seed_faq "demo-payment"         "Which payment methods do you accept?" "We accept all major credit cards, PayPal, and Apple Pay."

echo "== Done =="