# Deploying Riverstone Consulting (PHP + RDS) on AWS

This walks through standing up the site on a single EC2 instance, backed by a
MySQL RDS database. It assumes a fresh AWS account/region and basic comfort
with the AWS console or CLI.

**Lab safety first:** the `team.php` upload handler is intentionally
insecure (see the comment block in that file) so you can practice exploiting
an unrestricted file upload. Keep this deployment inside a security group
that is not open to the world, or only reachable via your own IP / a VPN,
and tear it down when you're done practicing.

---

## 1. Create the RDS MySQL database

1. AWS Console → **RDS** → **Create database**.
2. Engine: **MySQL** (8.x is fine).
3. Templates: **Free tier** (or Dev/Test) is enough for a lab.
4. Settings:
   - DB instance identifier: `riverstone-db`
   - Master username: `admin`
   - Master password: set something strong, save it.
5. Instance size: `db.t3.micro` / `db.t4g.micro`.
6. Connectivity:
   - VPC: use your default VPC (or a dedicated lab VPC).
   - Public access: **No** (recommended — EC2 will reach it privately).
   - VPC security group: create a new one, e.g. `riverstone-db-sg`.
7. Initial database name: `riverstone`.
8. Create the database and wait for status **Available**.
9. Note the **endpoint** (hostname) shown on the DB's detail page.

### Open the DB security group to your EC2 instance only
Once you've launched the EC2 instance (step 2), edit `riverstone-db-sg`:
- Inbound rule: Type **MySQL/Aurora (3306)**, Source = the **EC2 instance's
  security group** (not `0.0.0.0/0`).

---

## 2. Launch the EC2 instance

1. AWS Console → **EC2** → **Launch instance**.
2. Name: `riverstone-web`.
3. AMI: **Amazon Linux 2023**.
4. Instance type: `t2.micro` / `t3.micro`.
5. Key pair: create or select one (you'll need it for SSH).
6. Network settings:
   - Use the same VPC as your RDS instance.
   - Create a new security group `riverstone-web-sg` with:
     - SSH (22) from **your IP only**
     - HTTP (80) from **your IP only** (or a small trusted range — avoid
       `0.0.0.0/0` while you're practicing an intentional vuln on it)
7. Launch the instance.

---

## 3. Install the LAMP stack

SSH in:
```bash
ssh -i your-key.pem ec2-user@<EC2_PUBLIC_IP>
```

Install Apache, PHP, and the MySQL client:
```bash
sudo dnf update -y
sudo dnf install -y httpd php php-mysqlnd mariadb105

sudo systemctl enable --now httpd
```

Check PHP is wired into Apache:
```bash
sudo systemctl restart httpd
php -v
```

---

## 4. Load the database schema

From the EC2 instance (it can reach RDS privately), run the schema file
against your RDS endpoint:
```bash
mysql -h <RDS_ENDPOINT> -u admin -p < db.sql
```
Enter the master password when prompted. This creates the `riverstone`
database and the `contacts` table.

(Upload `db.sql` to the instance first with `scp`, e.g.
`scp -i your-key.pem db.sql ec2-user@<EC2_PUBLIC_IP>:~`.)

---

## 5. Deploy the site files

From your local machine, copy the whole site folder up:
```bash
scp -i your-key.pem -r site ec2-user@<EC2_PUBLIC_IP>:~
```

On the EC2 instance, move it into the web root and fix permissions:
```bash
sudo cp -r ~/site/* /var/www/html/
sudo chown -R apache:apache /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/uploads   # Apache needs to write here
```

---

## 6. Point the app at RDS

Set the DB connection as environment variables for Apache so `config.php`
can read them. Easiest way on Amazon Linux: add an `/etc/httpd/conf.d/env.conf`:

```bash
sudo tee /etc/httpd/conf.d/env.conf > /dev/null <<'EOF'
SetEnv DB_HOST "your-rds-endpoint.rds.amazonaws.com"
SetEnv DB_NAME "riverstone"
SetEnv DB_USER "admin"
SetEnv DB_PASS "your-master-password"
SetEnv DB_PORT "3306"
EOF

sudo systemctl restart httpd
```

---

## 7. Test it

Visit `http://<EC2_PUBLIC_IP>/index.php` in your browser. You should see the
home page. Try:
- `/about.php`
- `/contact.php` — submits into the `contacts` table in RDS and lists recent entries.
- `/team.php` — the deliberately weak upload form.

---

## 8. Practicing on the upload vulnerability

`team.php` only blocks `.php`, `.exe`, and `.sh` extensions and trusts the
browser's claimed MIME type — it doesn't inspect the actual file content or
store uploads outside the web root. That's enough surface to practice the
classic bypass techniques:

- Alternate extensions Apache may still execute as PHP depending on config
  (`.phtml`, `.pht`, `.php5`, `.phar`), or an extension not in the blacklist.
- Double extensions (`profile.jpg.php`) if any later processing re-derives
  type from the first extension.
- Spoofing the `Content-Type` sent with the upload, which the code trusts
  outright.

A good first PoC is uploading a tiny file that just calls `phpinfo();` —
if you can get it to execute from `/uploads/`, you've proven code execution
without needing anything more destructive. From there you can explore what
Apache's config does and doesn't block by extension, and how you'd fix it
(content-sniffing the upload, re-encoding images, serving `/uploads` from a
location with PHP execution disabled, randomizing filenames, etc.).

---

## 9. Tear-down

When you're done:
```bash
# In the console: terminate the EC2 instance, delete the RDS instance
# (skip final snapshot if you don't need it), and remove the two
# security groups.
```
