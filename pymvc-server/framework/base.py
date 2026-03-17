import json

class BaseController:
    """DRY base with common response helpers"""

    def __init__(self, handler):
        self.handler = handler
        self.request = handler.request
        self.path = handler.path
        self.query = handler.query
        self.headers = handler.headers

    def respond(self, content, status=200, content_type="text/html; charset=utf-8"):
        self.handler.send_response(status)
        self.handler.send_header("Content-Type", content_type)
        self.handler.send_header("Content-Length", str(len(content)))
        self.handler.end_headers()
        self.handler.wfile.write(content)

    def html(self, html_str, status=200):
        self.respond(html_str.encode("utf-8"), status, "text/html; charset=utf-8")

    def json(self, data, status=200):
        content = json.dumps(data, indent=2 if self.pretty() else None).encode("utf-8")
        self.respond(content, status, "application/json; charset=utf-8")

    def text(self, text, status=200):
        self.respond(text.encode("utf-8"), status, "text/plain; charset=utf-8")

    def pretty(self):
        """Helper: pretty JSON if ?pretty=1 or Accept contains */*pretty*"""
        return ("pretty" in self.query) or ("pretty" in self.headers.get("Accept", "").lower())

    def wants_json(self):
        accept = self.headers.get("Accept", "").lower()
        return (
            "application/json" in accept or
            self.query.get("format") == "json" or
            self.query.get("fmt") == "json"
        )
