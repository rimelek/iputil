#!/usr/bin/env bash

set -eu -o pipefail

IMAGE_NAME=rimelek-iputil-test

CURR_DIR="$(cd "$(dirname "$0")" && pwd)"

cd "$CURR_DIR"

ARGS=("$@")

declare -A tmp_alternatives=(
  [Darwin]="/private/var/folders/dd"
  [Linux]="/tmp"
)

tmp_path="${tmp_alternatives[$(uname)]}"

MOUNT_PATHS=(
  -v "$PWD:$PWD"
  -v "$tmp_path:$tmp_path"
)

if (( "$#" > 1 )); then
    COVERAGE_CLOVER_ARG=$(echo "${ARGS[*]}" | grep -E -o '\-\-coverage-clover ([^ ]+)' || true);
    if [ "${COVERAGE_CLOVER_ARG}" != "" ]; then
        COVERAGE_CLOVER_DIR="$(dirname "${COVERAGE_CLOVER_ARG//--coverage-clover /}")";
        MOUNT_PATHS+=(-v "$COVERAGE_CLOVER_DIR:$COVERAGE_CLOVER_DIR")

        export COVERAGE_CLOVER_DIR
    fi
fi

IMAGE_COUNT=$(docker image list | grep -c "$IMAGE_NAME" || true)

if (( "$IMAGE_COUNT" == 0 )); then
    docker build --no-cache -t "$IMAGE_NAME" "$CURR_DIR"
    IMAGE_COUNT=$(docker image list | grep -c "$IMAGE_NAME" || true)
    if (( "$IMAGE_COUNT" == 0 )); then
        >&2 echo "The image cannot be built: $IMAGE_NAME";
        exit 1
    fi
fi

if (( "$IMAGE_COUNT" > 1 )); then
    >&2 echo "There more image name containing the string: $IMAGE_NAME";
    exit 1
fi

docker run --rm "${MOUNT_PATHS[@]}" -u "$(id -u):$(id -g)" -w "$PWD" "$IMAGE_NAME" php "${ARGS[@]}"
