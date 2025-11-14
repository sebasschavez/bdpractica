FROM php:8.1-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar UTF-8 en Apache
RUN echo "AddDefaultCharset UTF-8" >> /etc/apache2/apache2.conf && \
    echo "php_value default_charset \"UTF-8\"" >> /etc/apache2/apache2.conf

# Configurar locale UTF-8
RUN apt-get update && apt-get install -y locales && \
    echo "es_MX.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen && \
    update-locale LANG=es_MX.UTF-8

ENV LANG es_MX.UTF-8
ENV LANGUAGE es_MX:es
ENV LC_ALL es_MX.UTF-8

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Copiar archivos del proyecto
COPY . /var/www/html/

# Exponer puerto 80
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
