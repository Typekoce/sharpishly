Here is the complete README.md content in one single, clean block — ready for you to copy and paste directly into your `README.md` file:

```markdown
# Sharpishly Research & Development

> **The Sovereign Multimodal Ecosystem**  
> High-performance Agentic MVC, Neural Ingestion, and Hardware Orchestration.  
> © 2026 Sharpishly Research and Development

---

## 🚀 The Evolution: God Mode

Sharpishly has evolved from a static landing page into a **Sovereign Agentic System**. It is designed to bridge the gap between human language, physical hardware, and digital knowledge.  

The architecture follows a **"Brain-Body" model**:

- **PHP** handles reasoning, routing, and orchestration  
- **Dockerized nodes** manage heavy-duty neural computation, vector storage, and hardware interrogation

## ✨ Core Features

- **3-Tier Intelligent Routing** — Subdomain-aware routing (`docs.*`, `shop.*`, `blog.*`) managed by a unified Front Controller
- **The Nervous System (SSE)** — Real-time, non-buffered task streaming from background workers directly to the Cyberdeck HUD
- **Neural Long-Term Memory (RAG)** — Full integration with **Ollama** and **Qdrant** for semantic search and document "understanding"
- **The Surveyor’s Suite** — Multimodal ingestion of property contracts (PDF/CSV) and meeting audio (Automatic Transcription → Vectorization)
- **The Vault** — AES-256-CBC encryption for secure handling of API keys, project credentials, and sensitive surveyor data
- **Hardware Senses** — Python-based PyMVC node for low-level USB scanning and device interrogation via the `HardwareController`

## 🛠️ Technologies

- **PHP 8.2 (FPM)** — Core Orchestrator & Multi-threaded Background Workers
- **Python 3.10** — Hardware Logic (PyMVC) & Vision Agents
- **Nginx** — High-fidelity "Thalamus" for proxying and Server-Sent Events (SSE) optimization
- **Ollama** — Neural Inference Engine (Llama 3.1 & Nomic-Embed-Text)
- **Qdrant** — High-speed Vector Database for semantic memory
- **MariaDB** — Relational data persistence for structured records

## 📂 Project Structure

```
.
├── website/                # The "Skin": Cyberdeck Dashboard & UI assets
├── php/                    # The "Brain": MVC Core, Agents & Neural Services
│   ├── src/                # Controllers, Models, and Services (Vector, CSV, Vault)
│   ├── Agents/             # Background Daemons (worker.php, scout.php)
│   └── index.php           # Front Controller & 3-Tier Subdomain Router
├── pymvc-server/           # The "Senses": Python-based Hardware Interface
├── android/                # Mobile Node: LightMVC implementation for Android
├── shells/                 # The "Reflexes": Automated DevOps, Purge, & Sync scripts
├── storage/                # The "Memory": Vault keys, Uploads, Vectors, and Logs
├── nginx.conf              # The "Thalamus": Traffic Orchestration & SSE Rules
└── docker-compose.yml      # The "Body": Containerized Infrastructure Orchestration
```

## 🛠️ Getting Started

### 1. Environmental Cleanse

If you previously ran Ollama manually on the host, purge it to prevent port conflicts with the Dockerized instance:

```bash
chmod +x shells/purge-local-ollama.sh
./shells/purge-local-ollama.sh
```

### 2. Ignite the Body

Launch the multi-container ecosystem from the project root:

```bash
docker compose up -d
```

### 3. Initialize Neural Memory

Pull the model weights inside the running Ollama container:

```bash
docker exec -it sharpishly-ollama ollama pull llama3.1
docker exec -it sharpishly-ollama ollama pull nomic-embed-text
```

### 4. Create the Vector Collection

Create the Qdrant collection for property documents (768 dimensions for Nomic embeddings):

```bash
curl -X PUT "http://localhost:6333/collections/property_docs" \
     -H "Content-Type: application/json" \
     --data '{
       "vectors": {
         "size": 768,
         "distance": "Cosine"
       }
     }'
```

## 🔐 Environment (.env)

Create a `.env` file in the project root. Sensitive values are automatically encrypted and managed via `Vault.php`:

```env
DB_PASSWORD=your_secure_pass
VAULT_KEY=your_aes_master_key
LOCAL_IP=192.168.0.11
# Add any other required variables here
```

## 📊 Monitoring & Access

- **Main App** → http://sharpishly.vm  
- **Documentation** → http://docs.sharpishly.vm (served via DocsController)  
- **Logs** → `storage/logs/` (Nginx, PHP, Worker logs)  
- **Qdrant Dashboard** → http://192.168.0.11:6333/dashboard (or replace with your actual IP)

> "Research and development accessible to everyone."

---

Built with 🔥 by Sharpishly R&D — 2026
