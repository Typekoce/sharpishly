from framework.base import BaseController

class HomeController(BaseController):
    def index(self):
        data = {
            "title": "Welcome to PyMVC",
            "message": "This is a minimal, fast, pure-Python MVC server",
            "url": self.path,
            "query": dict(self.query),
            "server_time": __import__("datetime").datetime.now().isoformat(),
            "example_items": ["item1", "item2", "item3"]
        }

        if self.wants_json():
            self.json({"status": "ok", "data": data})
        else:
            html = f"""
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ data['title'] }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-dark text-light">
  <div class="container my-5">
    <div class="text-center mb-5">
      <h1 class="display-4">{data['title']}</h1>
      <p class="lead text-muted">{data['message']}</p>
    </div>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="card bg-secondary text-light shadow">
          <div class="card-body">
            <h5 class="card-title">Request Info</h5>
            <ul class="list-group list-group-flush">
              <li class="list-group-item bg-dark text-light"><strong>Path:</strong> {data['url']}</li>
              <li class="list-group-item bg-dark text-light"><strong>Query:</strong> {data['query']}</li>
              <li class="list-group-item bg-dark text-light"><strong>Time:</strong> {data['server_time']}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card bg-secondary text-light shadow">
          <div class="card-body">
            <h5 class="card-title">Example Data</h5>
            <ul class="list-group list-group-flush">
              {% for item in data['example_items'] %}
              <li class="list-group-item bg-dark text-light">{item}</li>
              {% endfor %}
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-5">
      <a href="?format=json" class="btn btn-outline-light">View as JSON</a>
      <a href="?pretty=1&format=json" class="btn btn-outline-info">Pretty JSON</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
""".format(data=data)   # very simple string format (no real template engine)

            self.html(html)
