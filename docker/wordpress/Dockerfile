FROM wordpress:6.8.3-php8.2-apache

COPY docker/wordpress/security-hardening.conf /etc/apache2/conf-available/security-hardening.conf
COPY docker/wordpress/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/wordpress/php-security.ini /usr/local/etc/php/conf.d/php-security.ini
# Seed .htaccess with WordPress mod_rewrite rules so pretty permalinks work out of the box.
# Written to a staging path; a CMD/entrypoint copies it only if the volume file is absent.
COPY docker/wordpress/htaccess /docker-entrypoint.d/wordpress-htaccess

RUN a2enmod rewrite headers && \
	a2enconf security-hardening && \
	sed -ri -e 's/^ServerTokens\s+.*/ServerTokens Prod/g' /etc/apache2/conf-available/security.conf && \
	sed -ri -e 's/^ServerSignature\s+.*/ServerSignature Off/g' /etc/apache2/conf-available/security.conf && \
	# Ensure .htaccess is seeded at container start if the volume file is blank/missing
	printf '#!/bin/sh\n[ -s /var/www/html/.htaccess ] || cp /docker-entrypoint.d/wordpress-htaccess /var/www/html/.htaccess\n' \
		> /docker-entrypoint.d/50-seed-htaccess.sh && \
	chmod +x /docker-entrypoint.d/50-seed-htaccess.sh

