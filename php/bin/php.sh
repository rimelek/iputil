#!/usr/bin/env bash

set -eu -o pipefail

IMAGE_NAME_PREFIX=rimelek-iputil-test

CURR_DIR="$(cd "$(dirname "$0")" && pwd)"

cd "$CURR_DIR"

PHP_VERSION="$1"
shift

IMAGE_NAME="$IMAGE_NAME_PREFIX-php-$PHP_VERSION"

declare -A PHP_XDEBUG_VERSIONS=(
  [7.2]="3.1.6"
  [7.3]="3.1.6"
  [7.4]="3.1.6"
  [8.0]="3.2.0"
  [8.1]="3.2.0"
  [8.2]="3.2.0"
)

XDEBUG_VERSION="${PHP_XDEBUG_VERSIONS[$PHP_VERSION]}"
PROJECT_ROOT="$(cd "$CURR_DIR/../.." && pwd)"
VENDOR_DIR="$PROJECT_ROOT/vendor"
PHP_UNIT_PATH="$VENDOR_DIR/phpunit/phpunit/phpunit"
PHP_UNIT_XML="$PROJECT_ROOT/php/phpunit/phpunit.xml"
PHP_UNIT_XML_CACHE_DIR="$PROJECT_ROOT/php/phpunit"
PHP_UNIT_XML_CACHE="$PHP_UNIT_XML_CACHE_DIR/phpunit.php-$PHP_VERSION.xml"

ARGS=("$@")

if [[ "${ARGS[0]}" == "$PHP_UNIT_PATH" ]] \
&& [[ "${ARGS[1]}" == "--configuration" ]] \
&& [[ "${ARGS[2]}" == "$PHP_UNIT_XML" ]]; then
  mkdir -p "$PHP_UNIT_XML_CACHE_DIR"
  export PHP_UNIT_TESTSUITE_NAME="test $PHP_VERSION"
  export PHP_UNIT_BOOTSTRAP_PATH="$VENDOR_DIR/autoload.php"
  envsubst < "$PHP_UNIT_XML" > "$PHP_UNIT_XML_CACHE"
  ARGS[2]="$PHP_UNIT_XML_CACHE"
  ARGS+=("--cache-result-file" "$PHP_UNIT_XML_CACHE_DIR/.phpunit.result.php-$PHP_VERSION.cache")
fi

declare -A tmp_alternatives=(
  [Darwin]="/private/var/folders/dd"
  [Linux]="/tmp"
)

tmp_path="${tmp_alternatives[$(uname)]}"

MOUNT_PATHS=(
  -v "$PROJECT_ROOT:$PROJECT_ROOT"
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
    docker build "$CURR_DIR" \
      --file "$PROJECT_ROOT/Dockerfile" \
      --build-arg "PHP_VERSION=$PHP_VERSION" \
      --build-arg "XDEBUG_VERSION=$XDEBUG_VERSION" \
      --no-cache \
      --tag "$IMAGE_NAME"
      
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
