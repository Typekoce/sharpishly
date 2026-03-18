/**
 * @mainpage Sharpishly: The Micro-Stack Orchestrator
 * * @section intro_sec Introduction
 * Sharpishly is a stateful web-organism designed for hardware orchestration 
 * and local AI integration. It utilizes a PHP-based "Brain" to coordinate 
 * Python "Senses" (PyMVC) and Dockerized "Neural" nodes (Ollama).
 *
 * @section arch_sec System Architecture
 * - **Front Controller:** Centralized request handling and dynamic routing.
 * - **Nervous System:** Real-time feedback loop via Server-Sent Events (SSE).
 * - **Worker Agents:** Asynchronous daemons for heavy-lift data processing (CSV/Jobs).
 * - **Db Abstraction:** Automated migrations and sanitized data persistence.
 *
 * @section directory_sec Directory Structure
 * - /php/src/Controllers : UI Request Logic
 * - /php/src/Agents      : Background Daemons
 * - /php/src/Models      : Data Persistence & Schemas
 * - /storage/queue       : The SSE Event Buffer (The "Synapses")
 * * @version 1.1.0 (Phase 3.1: Nervous System Online)
 */
