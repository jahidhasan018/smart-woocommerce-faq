#!/usr/bin/env bash
#
# Installs the git pre-push hook so cs/stan/unit run locally before any push.
# Run: bin/install-git-hooks.sh
#
set -euo pipefail

HOOK_SRC="$(cd "$(dirname "$0")/hooks" && pwd)"
HOOK_DST="$(git rev-parse --git-dir)/hooks"

mkdir -p "$HOOK_DST"
for hook in pre-push; do
	cp "$HOOK_SRC/$hook" "$HOOK_DST/$hook"
	chmod +x "$HOOK_DST/$hook"
	echo "Installed $hook hook."
done

echo "Done."