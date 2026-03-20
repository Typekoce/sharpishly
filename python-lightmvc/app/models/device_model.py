from framework.base_model import BaseModel

class DeviceModel(BaseModel):
    def __init__(self):
        super().__init__()
        self.usb_devices = []
        self.network_interfaces = []
        self.bluetooth_devices = []
        self.rf_note = "RF detection requires hardware-specific drivers (not possible with stdlib)"
