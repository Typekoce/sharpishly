import pyudev
import time
import requests # We'll use this to talk to your PHP API later

class USBWatcher:
    def __init__(self):
        self.context = pyudev.Context()
        self.monitor = pyudev.Monitor.from_netlink(self.context)
        self.monitor.filter_by(subsystem='block') # Looking for storage devices

    def start(self):
        print("--- Sharpishly USB Scanner Active ---")
        for device in iter(self.monitor.poll, None):
            if device.action == 'add':
                self.handle_arrival(device)
            elif device.action == 'remove':
                self.handle_removal(device)

    def handle_arrival(self, device):
        vendor = device.get('ID_VENDOR', 'Unknown')
        model = device.get('ID_MODEL', 'Unknown')
        serial = device.get('ID_SERIAL_SHORT', 'N/A')
        
        print(f"[!] Target Acquired: {vendor} {model} (SN: {serial})")
        # TODO: Trigger PHP API call here

    def handle_removal(self, device):
        print("[?] Target Disconnected")

if __name__ == "__main__":
    watcher = USBWatcher()
    watcher.start()