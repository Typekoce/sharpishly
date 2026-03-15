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
