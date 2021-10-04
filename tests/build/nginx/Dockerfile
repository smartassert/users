FROM nginx:1.19

RUN rm /etc/nginx/conf.d/default.conf
COPY site.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /app/public && touch /app/public/index.php
