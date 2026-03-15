"""DRY Base Model - shared timestamp and data storage"""
import time

class BaseModel:
    def __init__(self):
        self.created = time.time()
        self.data = {}

    def get_created(self):
        return time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(self.created))
