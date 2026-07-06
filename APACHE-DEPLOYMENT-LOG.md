# Apache Virtual Host Deployment & Troubleshooting Log

This log documents how the project was deployed to the custom Apache server, how network access was enabled for teammates, and how the Vite assets loading issue (`net::ERR_CONNECTION_REFUSED`) was resolved.

---

## 1. The UI Loading Issue (`ERR_CONNECTION_REFUSED`)

### 🔍 The Symptom:
When accessing the site from a teammate's machine, the Login page loaded correctly, but the Dashboard showed a black page with a raw navbar. The browser console displayed:
```
client:1  Failed to load resource: net::ERR_CONNECTION_REFUSED
app.css:1  Failed to load resource: net::ERR_CONNECTION_REFUSED
app.js:1  Failed to load resource: net::ERR_CONNECTION_REFUSED
```

### 💡 Why did it happen?
1. **Vite Hot Module Replacement (HMR) Mode:** During local development, running `npm run dev` creates a temporary file named `public/hot` containing `http://127.0.0.1:5173`.
2. **Laravel Asset Resolution:** When the `@vite(['resources/css/app.css', 'resources/js/app.js'])` directive runs in Blade templates, Laravel checks for the existence of `public/hot`. If it exists, it generates HMR asset links pointing to `http://127.0.0.1:5173/build/assets/...`.
3. **The Teammate's Perspective:** When your teammate visited the site, their browser loaded the HTML and read these asset links. Since `127.0.0.1` (localhost) points to **their own machine**, their browser attempted to download `app.js` and `app.css` from their own local port `5173`, where no Vite development server was running. This triggered `ERR_CONNECTION_REFUSED`.
4. **Why Login Worked:** The Login page does not use the `@vite` directive; it only loads Bootstrap from a public CDN, which is why it rendered correctly.

### 🛠️ The Resolution:
We deleted the `public/hot` file from the repository:
```bash
rm -f public/hot
```
Without `public/hot`, the Laravel template fallback automatically reads `/public/build/manifest.json` and loads the pre-compiled production assets (`public/build/assets/app-*.js` and `app-*.css`), which are served directly by Apache on port `8110`.

---

## 2. Apache Virtual Host Configuration

We created a custom virtual host configuration on port `8110` (a non-privileged port suited for this custom user-space Apache instance).

* **Configuration Path:** `/home/abhi.andani@simform.dom/apache/sites-available/laravel-practical-2026.conf`
* **Configuration Code:**
```apache
# laravel-practical-2026.conf

# Virtual Host
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

## 3. Directory Syncing via Symbolic Link (Symlink)

Instead of statically copying files (which would require manual syncing whenever you edited code), we linked the Apache hosting directory directly to your active Desktop development directory.

* **Symlink Command:**
```bash
ln -sf /home/abhi.andani@simform.dom/Desktop/laravel-practical-2026 /home/abhi.andani@simform.dom/apache/laravel-practical-2026
```
* **Why it helps:** Any changes you make in your IDE in the Desktop folder are instantly served by Apache without copying!

---

## 4. Environment Config (`.env`) & Asset Compilation

To allow WebSocket real-time connections from external machines, the compiled Javascript assets need to know the correct host IP.

1. **Configured `.env`:**
   ```env
   APP_URL=http://172.16.5.183:8110
   VITE_REVERB_HOST=172.16.5.183
   ```
2. **Compiled Assets:**
   Ran the compiler to inject the network IP into the client-side JavaScript bundle:
   ```bash
   npm run build
   ```

---

## 5. File Permissions & Control Script Checklist

To prevent Apache write permission errors and load the new configurations:

1. **Set permissions on logs and storage folders:**
   ```bash
   chmod -R 777 storage bootstrap/cache logs
   ```
2. **Enable Virtual Host:**
   ```bash
   ln -sf /home/abhi.andani@simform.dom/apache/sites-available/laravel-practical-2026.conf /home/abhi.andani@simform.dom/apache/sites-enabled/laravel-practical-2026.conf
   ```
3. **Restart Apache:**
   ```bash
   apache2 -f /home/abhi.andani@simform.dom/apache/conf/httpd.conf -k restart
   ```
