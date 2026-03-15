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
