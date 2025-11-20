# 1. Usa la imagen base de PHP 8.2 con el servidor web Apache
FROM php:8.2-apache

# 2. Instala la extensión MySQL (mysqli). ¡Esencial para conectarse a Clever Cloud!
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 3. Copia el archivo de configuración de Apache que crearemos a continuación
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# 4. Copia todos los archivos de tu proyecto (todo el código) al directorio del servidor web de Apache
COPY . /var/www/html/

# 5. Exponer el puerto por defecto de Apache
EXPOSE 80