#!/bin/sh
set +e
echo ">>> Entrypoint demarre"
echo ">>> Test connexion base..."
php bin/console dbal:run-sql "SELECT 1" 2>&1
echo ">>> Resultat test base (code: $?)"
echo ">>> Lancement migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1
echo ">>> Migrations terminees (code: $?)"
php bin/console cache:clear --env=prod --no-debug 2>&1
echo ">>> Cache clear termine (code: $?)"
echo ">>> Demarrage Apache..."
exec "$@"
