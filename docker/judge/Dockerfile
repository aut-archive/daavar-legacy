FROM daavar/core

COPY conf/supervisord.conf /etc/supervisord.conf

RUN useradd -d /nonexistent -g nogroup -s /bin/false domjudge-run

RUN cd /tmp && \
    curl -#L https://github.com/DOMjudge/domjudge/archive/befaa62459530b62f4d2b947eac95299d1de5010.tar.gz | tar -xzf- && \
    mv domjudge* domjudge

#RUN cd /tmp/domjudge && \
#    ./configure --disable-submitclient  --with-domjudge-user=root && \
#    make install-domserver && \
#    ln -sfv /opt/domjudge/domserver/bin/* /usr/local/bin && \
#    make install-judgehost && \
#    ln -sfv /opt/domjudge/judgehost/bin/* /usr/local/bin
#
##RUN mkdir -p /opt/domjudge/etc && \ 
##    cp -nv /opt/domjudge/domserver/etc/* /opt/domjudge/etc/ && \
##    rm -r /opt/domjudge/domserver/etc && \
#    ln -vs /opt/domjudge/etc /opt/domjudge/domserver/etc  && \
#    cp -nv /opt/domjudge/judgehost/etc/* /opt/domjudge/etc/ && \
#    rm -r /opt/domjudge/judgehost/etc && \
#    ln -vs /opt/domjudge/etc /opt/domjudge/judgehost/etc 
#
#RUN ln -fs /dbpasswords.secret /opt/domjudge/etc
#RUN ln -fs /restapi.secret /opt/domjudge/etc
#
#COPY entrypoint /
