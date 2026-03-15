import subprocess
import platform
import os
import socket
from framework.base_controller import BaseController
from app.models.device_model import DeviceModel
from app.views.device_view import DeviceView

class DevicesController(BaseController):
    """
    Detects and attempts to connect to devices via:
    USB, Ethernet, Bluetooth, RF (limited by stdlib)
    """

    def __init__(self):
        super().__init__()
        self.model = DeviceModel()
        self.view = DeviceView()

    def detect_usb(self):
        try:
            if platform.system() == "Linux":
                output = subprocess.check_output(["lsusb"], stderr=subprocess.STDOUT).decode()
                self.model.usb_devices = output.strip().split("\n")

                # Attempt basic connection info (serial devices)
                serials = [d for d in os.listdir("/dev") if any(x in d for x in ["ttyUSB", "ttyACM", "ttyS"])]
                if serials:
                    self.model.usb_devices.append(f"→ Connectable serial ports: {', '.join(serials)}")
            else:
                self.model.usb_devices = ["Platform not supported for lsusb (use Windows/macOS native tools)"]
        except Exception as e:
            self.model.usb_devices = [f"USB detection failed: {str(e)[:80]}"]

    def detect_ethernet(self):
        try:
            if platform.system() == "Linux":
                output = subprocess.check_output(["ip", "link", "show"], stderr=subprocess.STDOUT).decode()
                self.model.network_interfaces = [line for line in output.split("\n") if line.strip().startswith(" ")]
            else:
                # Fallback for macOS/Windows
                output = subprocess.check_output(["ifconfig"] if platform.system() == "Darwin" else ["ipconfig"], 
                                                 stderr=subprocess.STDOUT).decode(errors="ignore")
                self.model.network_interfaces = output.split("\n")[:10]

            # Attempt connection test (localhost ping simulation)
            try:
                s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                s.settimeout(1)
                s.connect(("8.8.8.8", 53))  # Google DNS test (safe read-only)
                self.model.network_interfaces.append("→ Internet connectivity test: SUCCESS")
                s.close()
            except:
                self.model.network_interfaces.append("→ Internet connectivity test: FAILED (no route)")
        except Exception as e:
            self.model.network_interfaces = [f"Network detection failed: {str(e)[:80]}"]

    def detect_bluetooth(self):
        try:
            # Try modern bluetoothctl (Linux)
            output = subprocess.check_output(["bluetoothctl", "devices"], stderr=subprocess.STDOUT, timeout=3).decode()
            self.model.bluetooth_devices = output.strip().split("\n")
        except:
            try:
                # Fallback hcitool (older Linux)
                output = subprocess.check_output(["hcitool", "dev"], stderr=subprocess.STDOUT).decode()
                self.model.bluetooth_devices = output.strip().split("\n")
            except Exception as e:
                self.model.bluetooth_devices = [f"Bluetooth tools not available ({str(e)[:60]})"]

    def attempt_connections(self):
        """Safe 'attempt' messages - never opens devices destructively"""
        print("\nConnection attempts (safe simulation):")
        print("   USB      → Use: open('/dev/ttyUSB0', 'r+b') in your own code")
        print("   Ethernet → Use: socket.connect((ip, port))")
        print("   Bluetooth→ Run: bluetoothctl connect <MAC>")
        print("   RF       → Not supported with pure stdlib")

    def run(self):
        self.detect_usb()
        self.detect_ethernet()
        self.detect_bluetooth()
        self.view.show_devices(self.model)
        self.attempt_connections()


if __name__ == "__main__":
    # Quick test when running file directly
    DevicesController().run()
