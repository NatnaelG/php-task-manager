# Dockerfile to build the PHP environment
FROM php:8.2-fpm-alpine

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    sqlite-dev \
    libxml2-dev

RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    opcache

# Copy the application code
COPY . .

# Expose port 80 (for Nginx)
EXPOSE 80