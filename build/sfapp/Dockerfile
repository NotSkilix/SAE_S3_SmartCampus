FROM forge-registry.iut-larochelle.fr/php-fpm-composer/iutlr-info-php8.2-fpm-composer2

# Login to container as root user
USER root

# Symfony dev environement 
ENV APP_ENV=dev

# Copy php default configuration
COPY ./build/sfapp/conf/default.ini /usr/local/etc/php/conf.d/default.ini

# Set working directory
WORKDIR /app 

# Arguments defined in compose.yml
ARG USER_NAME
ARG USER_ID
ARG GROUP_NAME
ARG GROUP_ID

# Create system user to run Composer and PHP Commands
RUN if [ ! -z ${USER_NAME} ] && [ ! -z ${GROUP_NAME} ] && [ ${USER_ID:-0} -ne 0 ] && [ ${GROUP_ID:-0} -ne 0 ] ; then \
    useradd -G www-data,root -u $USER_ID -d /home/$USER_NAME $USER_NAME && \
    mkdir -p /home/$USER_NAME/.composer  && \
    chown -Rf ${USER_NAME}:${GROUP_NAME} /home/$USER_NAME  && \
    chown -R ${USER_NAME}:${GROUP_NAME} /app \
    ; fi

# Login to container as non-root user 
USER ${USER_ID:-0}:${GROUP_ID:-0}