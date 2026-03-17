FROM python:3.12-slim
WORKDIR /app
RUN apt-get update && apt-get install -y --no-install-recommends \
    usbutils iproute2 && rm -rf /var/lib/apt/lists/*
COPY . /app
EXPOSE 8083