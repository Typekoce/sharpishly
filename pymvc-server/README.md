# PyMVC – Minimal pure-Python MVC Server

**Features**
- Only Python standard library (no pip, no frameworks)
- DRY routing & base controller
- Responds in HTML or JSON (via Accept header or ?format=json)
- Bootstrap 5 responsive front-end (CDN only – no local files)
- URL style: `/pymvc/{controller}/{action}`

## Project Structure
```pymvc-server/
├── app/
│   ├── controllers/
│   │   └── HomeController.py
│   └── views/
│       ├── layouts/
│       │   └── default.html          ← full page skeleton with {{header}} {{content}} {{footer}}
│       ├── partials/
│       │   ├── header.html
│       │   └── footer.html
│       └── home/
│           └── index.html            ← only the main content block
└── server.py
    └── framework/
        └── base.py

```

**Run**
```bash
python3 server.py
```
