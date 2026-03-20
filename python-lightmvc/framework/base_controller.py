"""DRY Base Controller - links Model + View"""
class BaseController:
    def __init__(self):
        self.model = None
        self.view = None

    def run(self):
        raise NotImplementedError("Subclasses must implement run()")
