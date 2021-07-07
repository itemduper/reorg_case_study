#!/bin/sh

set -e

# Change directory to location of this script and store the its location
cd `dirname $0`
PROJ_DIR=$(pwd)

if [ ! -f $PROJ_DIR/.env ]; then
    # Copy Example .env
    echo "Generating .env from .env.example"
    cp .env.example .env

    # Generate APP_KEY
    php artisan key:generate

    # Configure DB for SQLite to make project more portable, easy to change to MySQL or Postgres if preferred
    ESCAPED_PROJ_DIR=$(printf '%s\n' "$PROJ_DIR" | sed -e 's/[\/&]/\\&/g')
    touch database/database.sqlite
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/g" .env
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$ESCAPED_PROJ_DIR\/database\/database\.sqlite/g" .env
    sed -i -E "s/^DB_(HOST|PORT|USERNAME|PASSWORD)/# DB_\1/g" .env

    echo "Your .env file was generated successfully!"
    exit 0
else
    >&2 echo "A .env file already exists, please remove it if you would like to create a new one."
    exit 1
fi