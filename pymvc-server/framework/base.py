import json
import os
from datetime import datetime

class BaseController:
    """
    DRY base class with common response helpers and template rendering.
    All controllers should inherit from this.
    """

    def __init__(self, handler):
        self.handler = handler
        self.request = handler.request
        self.path = handler.path
        self.query = handler.query
        self.headers = handler.headers

    # ─────────────────────────────────────────────────────────────
    # Core Response Methods
    # ─────────────────────────────────────────────────────────────

    def respond(self, content: bytes, status: int = 200, content_type: str = "text/html; charset=utf-8"):
        """Send a full HTTP response."""
        self.handler.send_response(status)
        self.handler.send_header("Content-Type", content_type)
        self.handler.send_header("Content-Length", str(len(content)))
        self.handler.end_headers()
        self.handler.wfile.write(content)

    def html(self, html_str: str, status: int = 200):
        """Send HTML response."""
        self.respond(html_str.encode("utf-8"), status, "text/html; charset=utf-8")

    def json(self, data: dict, status: int = 200):
        """Send JSON response (pretty if requested)."""
        indent = 2 if self.pretty() else None
        content = json.dumps(data, indent=indent).encode("utf-8")
        self.respond(content, status, "application/json; charset=utf-8")

    def text(self, text: str, status: int = 200):
        """Send plain text response."""
        self.respond(text.encode("utf-8"), status, "text/plain; charset=utf-8")

    # ─────────────────────────────────────────────────────────────
    # Helpers for response type detection
    # ─────────────────────────────────────────────────────────────

    def pretty(self) -> bool:
        """Check if pretty-print JSON is requested."""
        return (
            "pretty" in self.query or
            "pretty" in self.headers.get("Accept", "").lower()
        )

    def wants_json(self) -> bool:
        """Detect if client prefers JSON output."""
        accept = self.headers.get("Accept", "").lower()
        return (
            "application/json" in accept or
            self.query.get("format") == "json" or
            self.query.get("fmt") == "json"
        )

    # ─────────────────────────────────────────────────────────────
    # Simple Template Rendering (DRY - reusable across controllers)
    # ─────────────────────────────────────────────────────────────

    def render(self,
               view_path: str,
               data: dict = None,
               layout: str = "layouts/default.html") -> None:
        if data is None:
            data = {}

        PROJECT_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

        def load(rel_path: str) -> str:
            full = os.path.join(PROJECT_ROOT, 'app', 'views', rel_path)
            if not os.path.isfile(full):
                raise FileNotFoundError(f"Template not found: {full}")
            with open(full, 'r', encoding='utf-8') as f:
                return f.read()

        try:
            content_raw = load(view_path)
            header_raw  = load("partials/header.html")
            footer_raw  = load("partials/footer.html")
            layout_raw  = load(layout)
        except FileNotFoundError as e:
            self.text(f"Template error: {str(e)}", status=500)
            return

        # Prepare safe data
        safe_data = {k: str(v).replace('{', '{{').replace('}', '}}') for k, v in data.items()}

        # Render content (including list handling)
        content = content_raw
        for key, value in safe_data.items():
            placeholder = '{{ ' + key + ' }}'
            if isinstance(value, (list, tuple)) and key == "example_items":
                items_html = "\n".join(
                    f'<li class="list-group-item bg-dark text-light">{item}</li>'
                    for item in value
                )
                content = content.replace('{{ example_items }}', items_html)
            else:
                content = content.replace(placeholder, value)

        # Assemble layout
        full_html = (
            layout_raw
            .replace('{{header}}', header_raw)
            .replace('{{content}}', content)
            .replace('{{footer}}', footer_raw)
        )

        # ── IMPORTANT: Apply replacements to the FULL page ──
        for key, value in safe_data.items():
            full_placeholder = '{{ ' + key + ' }}'
            full_html = full_html.replace(full_placeholder, value)

        # Optional global replacements (e.g. current time)
        if '{{ now }}' in full_html:
            full_html = full_html.replace('{{ now }}', datetime.now().isoformat())

        self.html(full_html)