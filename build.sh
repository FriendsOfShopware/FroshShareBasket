#!/usr/bin/env bash

commit=$1
if [ -z ${commit} ]; then
    commit=$(git tag | tail -n 1)
    if [ -z ${commit} ]; then
        commit="master";
    fi
fi

# Remove old release
rm -rf FroshShareBasket FroshShareBasket-*.zip

# Build new release
mkdir -p FroshShareBasket
git archive ${commit} | tar -x -C FroshShareBasket
composer install --no-dev -n -o -d FroshShareBasket
zip -x "*build.sh*" -x "*.MD" -r FroshShareBasket-${commit}.zip FroshShareBasket
