# SkillUp 🚀

**SkillUp** is a modern, full-stack **Internship & Mentor Management Platform** built with **Laravel 12**, **Vue 3**, **Inertia.js**, and **Tailwind CSS**. It features real-time messaging powered by **Laravel Reverb**, AI-assisted task management powered by **Gemini AI**, and **Google OAuth** authentication via Laravel Socialite.

---

## 🌟 Key Features

### 👥 Role-Based Portals & Workflows
- **Admin Dashboard**:
  - Add, manage, and remove mentors.
  - Oversee platform access and system administration.
- **Mentor Workspace**:
  - Create and assign tasks to specific interns.
  - **AI Task Generation**: Automatically draft structured task descriptions and titles based on prompt topics.
  - Review submitted work with status updates (Approved / Needs Revision) and actionable feedback.
  - View task history and intern progress logs.
- **Intern Portal**:
  - View assigned active tasks with status indicators and due dates.
  - **AI Task Guidance**: Simplify complex task instructions into step-by-step actionable roadmaps.
  - Submit task deliverables with links and submission notes.
  - **AI Text Polisher**: Refine chat or submission notes into professional and polite communication.

---

### 💬 Real-Time Messaging
- Integrated **1-on-1 Chat** system between mentors and interns.
- Built using **Laravel Reverb** and **Laravel Echo** for instant, low-latency WebSocket communication.
- Instant delivery notifications and message history.

---

### 🤖 AI Assistant Capabilities
- **Task Auto-Generator**: Empowers mentors to generate complete task assignments from simple prompt topics.
- **Task Simplifier & Step-by-Step Breakdown**: Helps interns decompose complex instructions into actionable steps.
- **Tone & Text Polisher**: Elevates communication tone (chat messages & task submissions) to professional standards.

---

### 🔐 Authentication & Security
- Built-in authentication powered by **Laravel Breeze**.
- **Google OAuth 2.0 Integration** via Laravel Socialite for seamless single sign-on.
- Role-based authorization middleware ensuring strict access control across Admin, Mentor, and Intern routes.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.2+, [Laravel 12](https://laravel.com), Laravel Breeze, Laravel Socialite, Laravel Reverb, Laravel Horizon, Laravel Sanctum
- **Frontend**: [Vue 3](https://vuejs.org/), [Inertia.js v2](https://inertiajs.com/), [Vite](https://vitejs.dev/), [Tailwind CSS](https://tailwindcss.com/)
- **Real-Time / WebSockets**: Laravel Reverb, Laravel Echo, Pusher JS
- **Database**: SQLite / MySQL / PostgreSQL

---

## 🚀 Getting Started

Follow these steps to set up and run the project locally.

### Prerequisites

Ensure you have the following installed on your machine:
- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x & **npm**
- **SQLite** (or MySQL/PostgreSQL)

---

### 📥 Installation & Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Radha6673/intern-mentor-app.git
   cd intern-mentor-app
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**:
   ```bash
   npm install
   ```

4. **Configure Environment Variables**:
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Configure Environment Settings in `.env`**:
   - **Database**:
     ```env
     DB_CONNECTION=sqlite
     ```
   - **Google OAuth** (from Google Cloud Console):
     ```env
     GOOGLE_CLIENT_ID=your-google-client-id
     GOOGLE_CLIENT_SECRET=your-google-client-secret
     GOOGLE_REDIRECT_URL="${APP_URL}/auth/google/callback"
     ```
   - **Gemini AI API**:
     ```env
     GEMINI_API_KEY=your-gemini-api-key
     ```
   - **Laravel Reverb (WebSockets)**:
     ```env
     BROADCAST_CONNECTION=reverb
     REVERB_APP_ID=your-app-id
     REVERB_APP_KEY=your-app-key
     REVERB_APP_SECRET=your-app-secret
     REVERB_HOST="localhost"
     REVERB_PORT=8080
     REVERB_SCHEME=http
     ```

7. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

---

## 💻 Running the Application

You can start all required services (Laravel Server, Vite Dev Server, Queue Worker, and Reverb WebSocket Server) concurrently with a single command:

```bash
composer run dev
```

Alternatively, you can run each service individually in separate terminal sessions:

- **Laravel Web Server**:
  ```bash
  php artisan serve
  ```
- **Vite Development Server**:
  ```bash
  npm run dev
  ```
- **Queue Listener**:
  ```bash
  php artisan queue:listen
  ```
- **Reverb WebSocket Server**:
  ```bash
  php artisan reverb:start
  ```

Once running, access the application at `http://localhost:8000`.

---

## 🧪 Running Tests

Execute the PHPUnit test suite:

```bash
composer test
```
or
```bash
php artisan test
```

---

## 📁 Project Structure

```
intern-mentor-app/
├── app/
│   ├── Http/Controllers/    # Controllers (Admin, Mentor, Intern, Chat, AI)
│   ├── Models/              # Eloquent Models (User, Task, TaskSubmission, Conversation, Message)
│   └── Services/            # AiService (Gemini API integration & text processing)
├── database/
│   ├── migrations/          # Schema migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/      # Shared Vue components
│   │   ├── Layouts/         # Inertia application layouts
│   │   └── Pages/           # Vue page views (Admin, Mentor, Intern, Chat, Auth)
│   └── css/                 # Tailwind CSS styles
├── routes/
│   ├── web.php              # Web application routes & middleware
│   └── auth.php             # Breeze & OAuth authentication routes
└── config/                  # Framework configurations (services, broadcast, etc.)
```

---

## 📄 License

This project is open-sourced software licensed under the [MIT License](LICENSE).
