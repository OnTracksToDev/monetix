FROM php:8.3-apache

WORKDIR /var/www/html

# Installer les dépendances
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip \
    nodejs npm \
    libpq-dev build-essential openssl \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Activer Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Copier d'abord composer.json
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Puis copier le reste
COPY . .

# Script d'entrée pour générer les clés JWT
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurer Apache avec redirection HTTPS
RUN echo "<VirtualHost *:80>" > /etc/apache2/sites-available/000-default.conf
RUN echo "    DocumentRoot /var/www/html/public" >> /etc/apache2/sites-available/000-default.conf
RUN echo "    <Directory /var/www/html/public>" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        AllowOverride None" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        Require all granted" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        FallbackResource /index.php" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        DirectoryIndex index.php" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        # Transmettre Authorization header à PHP" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        RewriteEngine On" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        RewriteCond %{HTTP:Authorization} ." >> /etc/apache2/sites-available/000-default.conf
RUN echo "        RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        # Gérer les requêtes OPTIONS pour CORS" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        RewriteCond %{REQUEST_METHOD} OPTIONS" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        RewriteRule ^(.*)$ $1 [R=200,L]" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        # Forcer HTTPS pour les assets et APIs" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        RewriteCond %{HTTP:X-Forwarded-Proto} !https" >> /etc/apache2/sites-available/000-default.conf
RUN echo "        RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]" >> /etc/apache2/sites-available/000-default.conf
RUN echo "    </Directory>" >> /etc/apache2/sites-available/000-default.conf
RUN echo "</VirtualHost>" >> /etc/apache2/sites-available/000-default.conf

# Headers CORS
RUN echo "Header always set Access-Control-Allow-Origin \"*\"" >> /etc/apache2/sites-available/000-default.conf
RUN echo "Header always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS, PATCH\"" >> /etc/apache2/sites-available/000-default.conf
RUN echo "Header always set Access-Control-Allow-Headers \"Content-Type, Authorization, X-Requested-With\"" >> /etc/apache2/sites-available/000-default.conf

# Builder Vue.js
RUN cd assets && npm install && npm run build

# Créer le dossier var/ et configurer les permissions
RUN mkdir -p var/cache var/log
RUN chown -R www-data:www-data var/
RUN chmod -R 775 var/

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]