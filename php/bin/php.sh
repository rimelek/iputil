#!/usr/bin/env bash

set -eu -o pipefail

IMAGE_NAME_PREFIX=rimelek-iputil-test

CURR_DIR="$(cd "$(dirname "$0")" && pwd)"

cd "$CURR_DIR"

PHP_VERSION="$1"
shift

IMAGE_NAME="$IMAGE_NAME_PREFIX-php-$PHP_VERSION"

declare -A PHP_XDEBUG_VERSIONS=(
  [5.6]='2.5.5'
  [7.0]="2.7.2"
  [7.1]="2.9.8"
  [7.2]="3.1.6"
  [7.3]="3.1.6"
  [7.4]="3.1.6"
  [8.0]="3.2.0"
  [8.1]="3.2.0"
  [8.2]="3.2.0"
)

XDEBUG_VERSION="${PHP_XDEBUG_VERSIONS[$PHP_VERSION]}"
PROJECT_ROOT="$(cd "$CURR_DIR/../.." && pwd)"
PHP_UNIT_PATH="$PROJECT_ROOT/php/bin/phpunit.php"
PHP_UNIT_XML="$PROJECT_ROOT/php/phpunit/phpunit.xml"
PHP_UNIT_XML_CACHE_DIR="$PROJECT_ROOT/php/phpunit"

ARGS=("$@")

function arg_value_of() {
  local i
  local arg="$1"

  for i in "${!ARGS[@]}"; do
    if [[ "${ARGS[$i]}" == "$arg" ]]; then
      if (( "${#ARGS[@]}" > (i + 1) )); then
        echo "${ARGS[(( i + 1 ))]}"
        return 0 # The argument key and value exists
      fi
      return 1 # The argument value does not exist
    fi
  done
  return 2 # the argument key does not exist
}

function arg_value_is() {
  [[ "$(arg_value_of "$1")" == "$2" ]]
}

function arg_value_set() {
  local i
  local arg_key="$1"
  local arg_value="$2"

  for i in "${!ARGS[@]}"; do
    if [[ "${ARGS[$i]}" == "$arg_key" ]]; then
      ARGS[(( i + 1 ))]="$arg_value"
      return 0
    fi
  done
  ARGS+=("$arg_key" "$arg_value")
}

function arg_exists() {
  local ret=0
  arg_value_of "$1" >/dev/null || ret=$?
  
  (( ret < 2))
}

if arg_exists "$PHP_UNIT_PATH" \
&& arg_value_is "--configuration" "$PHP_UNIT_XML"; then
  mkdir -p "$PHP_UNIT_XML_CACHE_DIR"
  if [[ "$PHP_VERSION" != "7.0" ]] && [[ "$PHP_VERSION" != "5.6" ]]; then
    arg_value_set "--cache-result-file" "$PHP_UNIT_XML_CACHE_DIR/.phpunit.result.php-$PHP_VERSION.cache"
  fi 
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
    >&2 echo "There are multiple image names containing the string: $IMAGE_NAME";
    exit 1
fi

# XDEBUG_MODE is required for PHP 7.2 and above
docker run --rm "${MOUNT_PATHS[@]}" \
  -u "$(id -u):$(id -g)" \
  -w "$PWD" \
  -e XDEBUG_MODE=coverage \
  "$IMAGE_NAME" \
  php "${ARGS[@]}"
