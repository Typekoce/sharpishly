FROM python:3.12-slim

WORKDIR /app

# Optional minimal tools if server uses lsusb/ip etc.
RUN apt-get update && apt-get install -y --no-install-recommends \
    usbutils iproute2 && rm -rf /var/lib/apt/lists/*

COPY . /app

EXPOSE 8083

CMD ["python", "-u", "server.py"]