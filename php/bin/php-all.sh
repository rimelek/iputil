#!/usr/bin/env bash

set -eu -o pipefail

CURR_DIR="$(cd "$(dirname "$0")" && pwd)"

cd "$CURR_DIR"

./php.sh 5.6 "$@"
./php.sh 7.0 "$@"
./php.sh 7.1 "$@"
./php.sh 7.2 "$@"
./php.sh 7.3 "$@"
./php.sh 7.4 "$@"
./php.sh 8.0 "$@"
./php.sh 8.1 "$@"
./php.sh 8.2 "$@"