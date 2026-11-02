#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
mkdir -p var/dist
release_dir="$(mktemp -d)"
trap 'rm -rf -- "$release_dir"' EXIT

# Liste explicite : aucun .env.local, secret Symfony, cache ou outil de test.
tar --exclude='config/secrets' --exclude='config/reference.php' \
    --exclude='assets/vendor' --exclude='public/assets' --exclude='public/bundles' \
    -cf - assets bin config migrations public src templates translations \
    .env .php-version composer.json composer.lock symfony.lock importmap.php README.md \
    | tar -xf - -C "$release_dir"

export APP_ENV=prod APP_DEBUG=0
composer install --working-dir="$release_dir" --no-dev --no-scripts \
    --no-interaction --prefer-dist --no-progress --classmap-authoritative
php "$release_dir/bin/console" importmap:install
php "$release_dir/bin/console" assets:install "$release_dir/public"
php "$release_dir/bin/console" asset-map:compile

# Le cache de construction ne doit pas être livré au serveur.
rm -rf -- "$release_dir/var"
tar -czf var/dist/ft-api.tar.gz -C "$release_dir" .

archive_entries="$(tar -tzf var/dist/ft-api.tar.gz)"
if grep -Eq '^\./(\.env\.[^/]+|config/secrets(/|$)|var(/|$)|tests(/|$)|\.git(/|$)|vendor/(phpunit/|friendsofphp/|phpstan/(phpstan|phpstan-doctrine)/))' <<< "$archive_entries"; then
    echo 'Le livrable contient un fichier local ou un outil de développement interdit.' >&2
    exit 1
fi
for required_file in ./vendor/autoload.php ./public/index.php ./public/assets/manifest.json; do
    if ! grep -Fxq "$required_file" <<< "$archive_entries"; then
        echo "Fichier manquant dans le livrable : $required_file" >&2
        exit 1
    fi
done
(cd var/dist && sha256sum ft-api.tar.gz > ft-api.tar.gz.sha256)
echo 'Livrable créé : var/dist/ft-api.tar.gz'
