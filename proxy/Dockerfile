# syntax=docker/dockerfile:1

FROM nginxinc/nginx-unprivileged:1.29-alpine

# Rendered at start-up: the image replaces ${VARIABLES} with values from the environment.
COPY default.conf.template /etc/nginx/templates/default.conf.template

EXPOSE 8080
