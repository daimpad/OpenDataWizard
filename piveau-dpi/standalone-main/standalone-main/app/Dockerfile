FROM nginx:alpine

EXPOSE 8080
ADD dist /usr/share/nginx/html/
COPY default.nginx.conf /etc/nginx/nginx.conf
COPY nginx.vh.default.conf /etc/nginx/conf.d/default.conf


# The following steps are needed because of the OpenShift security constraints
# Create some temp folders for later permission granting and
# support running as arbitrary user which belogs to the root group
RUN mkdir /var/cache/nginx/uwsgi_temp && \
    mkdir /var/cache/nginx/client_temp && \
    mkdir /var/cache/nginx/proxy_temp && \
    mkdir /var/cache/nginx/fastcgi_temp && \
    mkdir /var/cache/nginx/scgi_temp && \
    chmod g+rwx /var/cache/nginx /var/run /var/log/nginx /var/cache/nginx/client_temp

COPY runtimeconfig.sh /

RUN chmod +x /runtimeconfig.sh
RUN chmod a+rw /usr/share/nginx/html/
RUN chmod a+rw /usr/share/nginx/html/assets
RUN chmod a+rw /usr/share/nginx/html/assets/*
RUN chmod a+rw /usr/share/nginx/html/app.*.js

CMD [ "/runtimeconfig.sh" ]
