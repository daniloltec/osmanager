FROM php:8.2-apache

# Extensão mysqli (usada pelo app)
RUN docker-php-ext-install mysqli

# Permite .htaccess / rewrites (não obrigatório, mas evita surpresas)
RUN a2enmod rewrite
