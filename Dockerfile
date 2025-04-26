# Use an official PHP image with Apache server
FROM php:8.2-apache

# Enable Apache mod_rewrite (useful if you later add URL rewrites)
RUN a2enmod rewrite

# Copy your app files into the container
COPY studentSuccessGrade/ /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80 (the web port)
EXPOSE 80
