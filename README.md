# MUIT Student Portal (PHP + MySQL)
Apply online -> Admin approves -> student receives username/password -> role dashboards (Student, Lecturer, Admin).

## Default admin
Username `admin`, password from env `ADMIN_PASSWORD` (default `admin123`). Change it under Admin > Security.

## Deploy to Railway
1. Push this folder to GitHub.
2. Railway > New Project > Deploy from GitHub repo.
3. Add a **MySQL** service, then in the web service add variable references: MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD (and ADMIN_PASSWORD).
4. Settings > Networking > Generate Domain. Tables are created automatically on first visit.
5. Uploaded documents live in `public/uploads`; attach a Railway Volume mounted at `/app/public/uploads` so files survive redeploys.

## Local run
`php -S localhost:8000 -t public` (with a local MySQL and the variables in `.env.example` exported).
