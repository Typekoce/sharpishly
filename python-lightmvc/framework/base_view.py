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
