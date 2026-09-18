FROM alpine:latest

RUN apk add --no-cache wget unzip curl bash python3

RUN wget -q https://github.com/XTLS/Xray-core/releases/latest/download/Xray-linux-64.zip -O /tmp/xray.zip && \
    unzip /tmp/xray.zip -d /usr/local/bin && \
    rm /tmp/xray.zip && \
    chmod +x /usr/local/bin/xray

COPY entrypoint.sh /entrypoint.sh
COPY agent.py /agent.py
RUN chmod +x /entrypoint.sh /agent.py

EXPOSE 443 10085 8080

ENTRYPOINT ["/entrypoint.sh"]