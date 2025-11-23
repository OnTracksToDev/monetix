#!/bin/bash
set -e

cd /var/www/html

# Générer les clés JWT si elles n'existent pas
if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
    echo "Generating JWT keys..."
    mkdir -p config/jwt
    openssl genpkey -out config/jwt/private.pem -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
    chmod 644 config/jwt/private.pem config/jwt/public.pem
    chown www-data:www-data config/jwt/private.pem config/jwt/public.pem
    echo "JWT keys generated successfully"
fi

# Vérifier que les clés sont accessibles
if [ ! -r config/jwt/private.pem ] || [ ! -r config/jwt/public.pem ]; then
    echo "ERROR: JWT keys are not readable"
    exit 1
fi

echo "JWT keys are ready:"
ls -la config/jwt/

exec "$@"