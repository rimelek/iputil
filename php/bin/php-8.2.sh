#!/usr/bin/env bash

set -eu -o pipefail

CURR_DIR="$(cd "$(dirname "$0")" && pwd)"

cd "$CURR_DIR"

./php.sh 8.2 "$@"