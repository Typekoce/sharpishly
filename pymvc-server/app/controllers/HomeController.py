# app/controllers/HomeController.py

import os
from datetime import datetime

from framework.base import BaseController   # ← this import is required

class HomeController(BaseController):
    def index(self) -> None:
        data = {
            "title": "Welcome to PyMVC",
            "message": "This is a minimal, fast, pure-Python MVC server",
            "url": self.path,
            "query": dict(self.query),
            "server_time": datetime.now().isoformat(),
            "example_items": ["item1", "item2", "item3"]
        }

        if self.wants_json():
            self.json({"status": "ok", "data": data})
        else:
            self.render("home/index.html", data)