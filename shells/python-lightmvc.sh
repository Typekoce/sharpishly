#!/usr/bin/env bash

set -e

PROJECT_NAME="python-lightmvc"
echo "Creating lightweight Python MVC project: $PROJECT_NAME"
echo "→ Pure stdlib only • MVC + DRY pattern • Device scanner included"
echo "→ No pip, no external libraries, no updates"

mkdir -p "$PROJECT_NAME"
cd "$PROJECT_NAME"

#################################################
# DIRECTORY STRUCTURE (DRY-friendly)
#################################################

mkdir -p framework
mkdir -p app/{models,views,controllers}
mkdir -p docs

#################################################
# FRAMEWORK BASE CLASSES (DRY core)
#################################################

cat > framework/base_model.py << 'EOF'
"""DRY Base Model - shared timestamp and data storage"""
import time

class BaseModel:
    def __init__(self):
        self.created = time.time()
        self.data = {}

    def get_created(self):
        return time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(self.created))
EOF

cat > framework/base_view.py << 'EOF'
"""DRY Base View - console output helpers"""
class BaseView:
    def show_header(self, title):
        print("=" * 60)
        print(f"  {title.center(56)}  ")
        print("=" * 60)

    def show_section(self, title):
        print(f"\n→ {title}")

    def show_item(self, key, value):
        print(f"   • {key:<20} : {value}")

    def show_error(self, msg):
        print(f"   ❌ {msg}")
EOF

cat > framework/base_controller.py << 'EOF'
"""DRY Base Controller - links Model + View"""
class BaseController:
    def __init__(self):
        self.model = None
        self.view = None

    def run(self):
        raise NotImplementedError("Subclasses must implement run()")
EOF

#################################################
# APP - DEVICE MODEL
#################################################

cat > app/models/device_model.py << 'EOF'
from framework.base_model import BaseModel

class DeviceModel(BaseModel):
    def __init__(self):
        super().__init__()
        self.usb_devices = []
        self.network_interfaces = []
        self.bluetooth_devices = []
        self.rf_note = "RF detection requires hardware-specific drivers (not possible with stdlib)"
EOF

#################################################
# APP - DEVICE VIEW
#################################################

cat > app/views/device_view.py << 'EOF'
from framework.base_view import BaseView
import platform

class DeviceView(BaseView):
    def show_devices(self, model):
        self.show_header("LIGHTMVC DEVICE SCANNER")

        self.show_section("USB Devices")
        if model.usb_devices:
            for dev in model.usb_devices:
                self.show_item("Detected", dev.strip())
        else:
            self.show_item("Status", "None found")

        self.show_section("Ethernet / Network Interfaces")
        if model.network_interfaces:
            for iface in model.network_interfaces:
                self.show_item("Interface", iface.strip())
        else:
            self.show_item("Status", "None found")

        self.show_section("Bluetooth Devices")
        if model.bluetooth_devices:
            for dev in model.bluetooth_devices:
                self.show_item("Paired/Visible", dev.strip())
        else:
            self.show_item("Status", "None found or bluetoothctl not available")

        self.show_section("RF (Radio Frequency)")
        self.show_item("Note", model.rf_note)

        print("\n" + "-" * 60)
        print("Detection complete. Connection attempts shown below.")
EOF

#################################################
# DEVICES CONTROLLER (as requested)
#################################################

cat > app/controllers/devices_controller.py << 'EOF'
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
EOF

#################################################
# MAIN ENTRY POINT (MVC orchestration)
#################################################

cat > main.py << 'EOF'
#!/usr/bin/env python3
"""
LightMVC - Pure Python MVC starter
Run with: python3 main.py
"""
from app.controllers.devices_controller import DevicesController

if __name__ == "__main__":
    print("🚀 Starting LightMVC Device Scanner...")
    controller = DevicesController()
    controller.run()
    print("\n✅ Scan finished. Extend this controller for your own devices!")
EOF

#################################################
# DOCUMENTATION FILES
#################################################

cat > README.md << 'EOF'
# python-lightmvc

Ultra-lightweight **pure-Python MVC** framework (stdlib only).

**Features**
- True MVC separation
- DRY base classes (BaseModel, BaseView, BaseController)
- No external dependencies whatsoever
- Built-in `DevicesController` that scans USB, Ethernet, Bluetooth, RF

## How to run
```bash
cd python-lightmvc
python3 main.py
