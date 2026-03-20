import http.server
import socketserver
import urllib.parse
import importlib.util
import os
import sys
from urllib.parse import urlparse, parse_qs

PORT = 8083
BIND = "0.0.0.0"   # change to "0.0.0.0" if you want to listen on all interfaces

# ── Simple DRY router ────────────────────────────────────────
ROUTES = {
    "/pymvc/home/index": ("app.controllers.HomeController", "index"),
    # Add more like this:
    # "/pymvc/users/list":   ("app.controllers.UsersController", "list"),
}

class PyMVCRequestHandler(http.server.BaseHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)

    def dispatch(self):
        parsed = urlparse(self.path)
        clean_path = parsed.path.rstrip("/")
        query = parse_qs(parsed.query)

        self.query = {k: v[0] if len(v) == 1 else v for k,v in query.items()}
        self.path_clean = clean_path

        if clean_path in ROUTES:
            ctrl_module_path, action = ROUTES[clean_path]
            try:
                spec = importlib.util.spec_from_file_location(
                    ctrl_module_path.replace(".", "_"),
                    os.path.join(os.path.dirname(__file__), ctrl_module_path.replace(".", os.sep) + ".py")
                )
                module = importlib.util.module_from_spec(spec)
                spec.loader.exec_module(module)
                ctrl_class = getattr(module, ctrl_module_path.split(".")[-1])
                controller = ctrl_class(self)
                method = getattr(controller, action, None)
                if method:
                    method()
                    return
                else:
                    self.send_error(500, f"Action '{action}' not found in {ctrl_module_path}")
            except Exception as e:
                self.send_error(500, f"Controller error: {str(e)}")
        else:
            self.send_error(404, "Route not found")

    def do_GET(self):
        self.dispatch()

    def do_HEAD(self):
        self.send_response(200)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.end_headers()

    def do_POST(self):
        # If you need POST later → extend here
        self.send_error(405, "Method Not Allowed")

    def log_message(self, format, *args):
        # Quiet logging or customize
        sys.stderr.write(f"{self.address_string()} - {format%args}\n")

if __name__ == "__main__":
    print(f"Starting PyMVC server on http://{BIND}:{PORT}/pymvc/home/index")
    print("Ctrl+C to stop")
    with socketserver.TCPServer((BIND, PORT), PyMVCRequestHandler) as httpd:
        httpd.serve_forever()
