FROM mysql:latest
MAINTAINER Ian Edington "IanEdington@gmail.com"

# this need to stay the same for script to work
ENV MYSQL_USER mysql

COPY ./localdb-run.sh /
RUN chmod 755 /localdb-run.sh

ENTRYPOINT ["/localdb-run.sh"]