# Usamos la imagen oficial de PHP con Apache (Coincidiendo con tu versión 8.2.12)
FROM php:8.2-apache

# 1. Instalamos las extensiones necesarias para MySQL (Visto en tu Database.php)
RUN docker-php-ext-install pdo pdo_mysql

# 2. Habilitamos mod_rewrite de Apache para que tu .htaccess funcione
RUN a2enmod rewrite

# 3. Configuramos el directorio de trabajo
WORKDIR /var/www/html

# 4. Copiamos todos los archivos de tu proyecto al contenedor
COPY . .

# 5. Ajustamos los permisos para que Apache pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html

# 6. Exponemos el puerto 80
EXPOSE 80

# 7. Comando para arrancar Apache
CMD ["apache2-foreground"]