# Apache Virtual Host Deployment & Troubleshooting Log

This log documents how the project was deployed to the custom Apache server, how network access was enabled for teammates, and how the Vite assets and Reverb WebSocket loading issues (`net::ERR_CONNECTION_REFUSED`) are resolved.

---

## 1. Directory Structure Context
For all commands below, please note the directory you should be in:

* **Project Root Directory:** `/home/abhi.andani@simform.dom/Desktop/laravel-practical-2026` (your Laravel project workspace).
* **Apache User Directory:** `/home/abhi.andani@simform.dom/apache` (where your custom Apache configs and logs reside).
* **Any Directory:** Can be executed from any path on the system.

---

## 2. Vite HMR Asset Loading Issue (`ERR_CONNECTION_REFUSED`)

### 🔍 The Symptom:
When accessing the site from a teammate's machine on the same WiFi, the Login page loaded correctly (since it loads Bootstrap from a CDN), but the Dashboard showed a blank page with a raw navbar. The browser console displayed:
```
client:1  Failed to load resource: net::ERR_CONNECTION_REFUSED
app.css:1  Failed to load resource: net::ERR_CONNECTION_REFUSED
app.js:1  Failed to load resource: net::ERR_CONNECTION_REFUSED
```

### 💡 Why did it happen?
1. **Vite Hot Module Replacement (HMR) Mode:** During local development, running `npm run dev` creates a temporary file named `public/hot` containing `http://127.0.0.1:5173`.
2. **Laravel Asset Resolution:** When the `@vite` directive runs, Laravel checks for the presence of `public/hot`. If it exists, it generates HMR asset links pointing to `http://127.0.0.1:5173/build/assets/...`.
3. **The Teammate's Perspective:** When your teammate visits the site, their browser tries to download these assets from `127.0.0.1` (localhost), which maps to **their own machine** where no Vite server is running. This triggers `ERR_CONNECTION_REFUSED`.

### 🛠️ The Resolution:
We must delete the `public/hot` file so Laravel falls back to loading compiled production assets directly from Apache:

> **Run this in:** `Project Root Directory`
> ```bash
> rm -f public/hot
> ```

---

## 3. Real-time WebSocket (Reverb) Connection Issue (`ERR_CONNECTION_REFUSED`)

### 🔍 The Symptom:
The page layout renders correctly, but the real-time visitor approval notifications, modals, and Echo event listeners do not fire. The browser console displays:
```
WebSocket connection to 'ws://172.16.5.183:9000/app/...' failed: net::ERR_CONNECTION_REFUSED
```

### 💡 Why did it happen?
The frontend assets are configured to open WebSocket channels on port `9000` (`VITE_REVERB_HOST=172.16.5.183`, `VITE_REVERB_PORT=9000`), but the Reverb server is either not running, or running on `localhost` (binding only to `127.0.0.1` instead of `0.0.0.0`).

### 🛠️ The Resolution:
1. Make sure your `.env` has Reverb host binding on `0.0.0.0` or matches the configuration:
   ```env
   BROADCAST_CONNECTION=reverb
   REVERB_PORT=9000
   REVERB_SERVER_PORT=9000
   VITE_REVERB_HOST=172.16.5.183
   VITE_REVERB_PORT=9000
   ```
2. Start the Reverb WebSocket server:

> **Run this in:** `Project Root Directory`
> ```bash
> php artisan reverb:start --host=0.0.0.0 --port=9000
> ```
*Note: Keep this terminal window open so the WebSocket server remains active.*

---

## 4. Apache Virtual Host Configuration

We created a custom virtual host configuration on port `8110` (suited for this custom user-space Apache instance).

* **Configuration Path:** `/home/abhi.andani@simform.dom/apache/sites-available/laravel-practical-2026.conf`
* **Configuration Code:**
```apache
<VirtualHost *:8110>
  DocumentRoot "/home/abhi.andani@simform.dom/apache/laravel-practical-2026/public/"

  <Directory "/home/abhi.andani@simform.dom/apache/laravel-practical-2026/public/">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>

  ErrorLog  "/home/abhi.andani@simform.dom/apache/laravel-practical-2026/logs/error.log"
  CustomLog "/home/abhi.andani@simform.dom/apache/laravel-practical-2026/logs/access.log" combined
</VirtualHost>
```

---

## 5. Deployment Step-by-Step Command Guide

Follow this sequence to sync configurations and launch the server.

### Step 1: Link Desktop Folder to Apache Directory
This links the hosting directory directly to your active Desktop development workspace.

> **Run this in:** `Any Directory`
> ```bash
> ln -sf /home/abhi.andani@simform.dom/Desktop/laravel-practical-2026 /home/abhi.andani@simform.dom/apache/laravel-practical-2026
> ```

### Step 2: Compile CSS & Javascript Bundles
Injects the network IP configurations into the client-side JavaScript bundle for WebSocket Echo connections.

> **Run this in:** `Project Root Directory`
> ```bash
> npm run build
> ```

### Step 3: Remove Hot Reload Files
Forces Laravel to serve the compiled production assets instead of referencing the dev server.

> **Run this in:** `Project Root Directory`
> ```bash
> rm -f public/hot
> ```

### Step 4: Configure Storage & Cache Directory Permissions
Enables Apache (`abhi.andani@simform.dom`) to write sessions, application logs, and boot cache.

> **Run this in:** `Project Root Directory`
> ```bash
> chmod -R 777 storage bootstrap/cache logs
> ```

### Step 5: Enable Virtual Host Configuration
Symlinks the configuration file into Apache's active list.

> **Run this in:** `Any Directory`
> ```bash
> ln -sf /home/abhi.andani@simform.dom/apache/sites-available/laravel-practical-2026.conf /home/abhi.andani@simform.dom/apache/sites-enabled/laravel-practical-2026.conf
> ```

### Step 6: Restart Apache HTTP Server
Applies configurations and binds to port `8110`.

> **Run this in:** `Any Directory`
> ```bash
> apache2 -f /home/abhi.andani@simform.dom/apache/conf/httpd.conf -k restart
> ```

### Step 7: Launch Reverb WebSocket Server
Allows teammates' browsers to broadcast realtime approval workflows.

> **Run this in:** `Project Root Directory`
> ```bash
> php artisan reverb:start --host=0.0.0.0 --port=9000
> ```
