# DevOps Mini Project — Jenkins CI/CD Pipeline + Monitoring

> **Automated Static Website Deployment with Live Monitoring**
> `GitHub → Jenkins → Azure App Service → Prometheus → Grafana`

A beginner-friendly DevOps mini-project that automates website deployment using a Jenkins CI/CD pipeline on a **RHEL VM (VMware)**, pushing code directly to **Microsoft Azure App Service**, with real-time monitoring via **Prometheus** and **Grafana**.

---

## 📌 What is This Project?

Every time you update your website code, someone has to manually upload it to the server. That's slow, error-prone, and unprofessional.

This project eliminates that entirely.

Once set up, you only need to edit your code on GitHub and commit the change. Within 60 seconds, Jenkins automatically detects the change, pulls the latest code, and pushes it live to Azure — without you touching anything else.

On top of that, **Prometheus scrapes Jenkins metrics every 15 seconds** and **Grafana visualizes them in a live dashboard** — giving you full visibility into build health, failures, durations, and executor usage.

---

## 🏗️ System Architecture

```mermaid
flowchart TD
    DEV(["👨‍💻 Developer\nLocal Machine"])
    GH(["🐙 GitHub\nSource Code Repository"])
    JK(["⚙️ Jenkins\nCI/CD Engine — Port 8080"])
    PR(["📡 Prometheus\nMetrics Collector — Port 9090"])
    GF(["📊 Grafana\nLive Dashboard — Port 3000"])
    AZ(["☁️ Azure App Service\nyour-app.azurewebsites.net"])

    DEV -->|"git commit + push"| GH
    GH -->|"Poll SCM every 1 min"| JK
    JK -->|"git push HTTPS"| AZ
    JK -->|"Exposes /prometheus endpoint"| PR
    PR -->|"Scrape every 15s"| JK
    PR -->|"Data source"| GF

    subgraph VM ["🖥️ RHEL VM — VMware"]
        JK
        PR
        GF
    end

    subgraph CLOUD ["☁️ Microsoft Azure"]
        AZ
    end

    style DEV fill:#2d333b,stroke:#58a6ff,color:#cdd9e5
    style GH fill:#2d333b,stroke:#58a6ff,color:#cdd9e5
    style JK fill:#d24939,stroke:#ff6b57,color:#fff
    style PR fill:#e6522c,stroke:#ff7a57,color:#fff
    style GF fill:#f46800,stroke:#ff8c35,color:#fff
    style AZ fill:#0078d4,stroke:#3ea8ff,color:#fff
    style VM fill:#1c2128,stroke:#444c56,color:#adbac7
    style CLOUD fill:#1c2128,stroke:#0078d4,color:#adbac7
```

---

## 🧰 Tech Stack

| Tool | Role |
|------|------|
| GitHub | Source code storage and version control |
| RHEL VM (VMware) | Server OS where Jenkins, Prometheus & Grafana run |
| Jenkins | CI/CD automation engine |
| Microsoft Azure App Service | Cloud hosting for the live website |
| Git | Code transfer between all three systems |
| Azure CLI | Used to set deployment credentials via terminal |
| Prometheus | Metrics collection from Jenkins |
| Grafana | Real-time dashboard and visualization |

---

## ✅ Prerequisites

- A GitHub account with your `index.html` in a public repository
- A **RHEL Virtual Machine** (VMware) with internet access
- A Microsoft Azure account (free tier works perfectly)
- Basic comfort using a Linux terminal

---

## 📁 Phase 0 — Prepare Your Code on GitHub

1. Log in to [github.com](https://github.com) and create a new repository.
2. Upload your `index.html` file into the repository.
3. Make sure your default branch is named `main`.

> 💡 Keep your repository **Public** so Jenkins can access it without credentials.

---

## ☁️ Phase 1 — Set Up Azure App Service

### 1.1 Create the Web App

1. Log in to [portal.azure.com](https://portal.azure.com)
2. **Create a resource** → **Web App** → **Create**
3. Fill in:
   - **Resource Group:** `devops-project-rg`
   - **Name:** unique name (becomes your URL)
   - **Publish:** `Code`
   - **Runtime Stack:** `PHP 8.2` or `Node.js`
   - **Operating System:** Linux
4. **Review + Create** → **Create**

> ⚠️ App name cannot be changed later — it becomes `yourname.azurewebsites.net`.

### 1.2 Configure Deployment Source

1. Open Web App → **Deployment Center**
2. Set **Source** to `Local Git` → **Save**
3. Copy the **Git Clone URI** shown after saving

### 1.3 Enable SCM Authentication

1. **Configuration** → **General settings** tab
2. **SCM Basic Auth Publishing Credentials** → **On** → **Save**

> 💡 This step is frequently missed and causes most `Authentication failed` errors.

### 1.4 Create Deployment Credentials via Azure Cloud Shell

```bash
az webapp deployment user set --user-name YOUR_UNIQUE_NAME --password YourPassword123
```

> ⚠️ Do NOT use `@`, `#`, or `!` in passwords — they break the Git URL format.

---

## 🔧 Phase 2 — Prepare Your RHEL VM

### 2.1 Install Java 21

```bash
sudo dnf install java-21-openjdk -y
java -version
```

### 2.2 Install Git and wget

```bash
sudo dnf install git wget -y
```

---

## ⚙️ Phase 3 — Install Jenkins on RHEL

### 3.1 Add Jenkins Repository

```bash
sudo wget -O /etc/yum.repos.d/jenkins.repo \
  https://pkg.jenkins.io/redhat-stable/jenkins.repo
```

### 3.2 Import Security Key

```bash
sudo rpm --import https://pkg.jenkins.io/redhat-stable/jenkins.io-2023.key
```

### 3.3 Install Jenkins

```bash
sudo dnf install jenkins -y
```

### 3.4 Extend Startup Timeout

```bash
sudo mkdir -p /etc/systemd/system/jenkins.service.d
sudo bash -c 'echo "[Service]" > /etc/systemd/system/jenkins.service.d/override.conf'
sudo bash -c 'echo "TimeoutStartSec=600" >> /etc/systemd/system/jenkins.service.d/override.conf'
sudo systemctl daemon-reload
```

> 💡 Prevents Jenkins from being killed before it finishes unpacking on first boot (common on VMs with less than 2GB RAM).

### 3.5 Start Jenkins

```bash
sudo systemctl enable --now jenkins
sudo systemctl status jenkins
# Look for: Active: active (running)
```

### 3.6 Open Firewall

```bash
sudo firewall-cmd --permanent --add-port=8080/tcp
sudo firewall-cmd --reload
```

---

## 🌐 Phase 4 — Unlock and Configure Jenkins

### 4.1 Get Admin Password

```bash
sudo cat /var/lib/jenkins/secrets/initialAdminPassword
```

### 4.2 First-Time Setup

1. Open browser: `http://<your-vm-ip>:8080`
2. Paste admin password → **Continue**
3. **Install suggested plugins** (takes 3–5 minutes)
4. Create admin username and password
5. **Save and Finish** → **Start using Jenkins**

---

## 🔗 Phase 5 — Create the CI/CD Pipeline in Jenkins

### 5.1 Create a New Job

1. **New Item** → type project name → **Freestyle project** → **OK**

### 5.2 Connect to GitHub

1. **Source Code Management** → **Git**
2. Paste your GitHub repository URL
3. **Credentials:** `None` (public repo)
4. **Branches to build:** change `*/master` → `*/main`

> ⚠️ Leaving `*/master` causes: `Couldn't find any revision to build`.

### 5.3 Set Automation Trigger

1. Check **Poll SCM**
2. Schedule: `* * * * *` (polls every 1 minute)

### 5.4 Add Deployment Script

**Build Steps → Add build step → Execute shell:**

```bash
git push https://YOUR_AZURE_USERNAME:YOUR_AZURE_PASSWORD@YOUR_SCM_URI:443/YOUR_APP_NAME.git HEAD:refs/heads/main --force
```

> 💡 Use `refs/heads/main` not `main` — new Azure repos reject shorthand on first push.

### 5.5 Save

Click **Save** at the bottom.

---

## 🧪 Phase 6 — Test and Verify Deployment

1. Click **Build Now** → **Console Output**
2. You should see:
   ```
   remote: Deployment successful.
   Finished: SUCCESS
   ```
3. Visit `https://your-app-name.azurewebsites.net` 🎉
4. Edit `index.html` on GitHub → commit → Jenkins auto-deploys within 60 seconds ✅

---

## 📊 Phase 7 — Install Prometheus Plugin in Jenkins

### 7.1 Install Plugin

1. **Manage Jenkins** → **Plugins** → **Available plugins**
2. Search `Prometheus metrics` → **Install**

### 7.2 Enable Metrics Endpoint

1. **Manage Jenkins** → **System** → scroll to **Prometheus** section
2. Leave default path as `prometheus`
3. Enable all metric checkboxes:
   - ✅ Count duration of successful builds
   - ✅ Count duration of failed builds
   - ✅ Count duration of unstable builds
   - ✅ Count duration of aborted builds
4. **Save**

### 7.3 Verify

```bash
curl http://localhost:8080/prometheus
```
You should see a large list of Jenkins metrics ✅

---

## 📡 Phase 8 — Install Prometheus on RHEL VM

### 8.1 Download and Extract

```bash
cd /tmp
wget https://github.com/prometheus/prometheus/releases/download/v2.51.0/prometheus-2.51.0.linux-amd64.tar.gz
tar -xvf prometheus-2.51.0.linux-amd64.tar.gz
sudo mv prometheus-2.51.0.linux-amd64 /opt/prometheus
sudo mkdir -p /opt/prometheus/data
```

### 8.2 Fix Permissions (RHEL SELinux requirement)

```bash
sudo chmod +x /opt/prometheus/prometheus
sudo chown -R root:root /opt/prometheus
sudo chmod -R 755 /opt/prometheus/data
sudo chcon -t bin_t /opt/prometheus/prometheus
sudo restorecon -rv /opt/prometheus/
```

Verify binary works:
```bash
/opt/prometheus/prometheus --version
```

### 8.3 Configure to Scrape Jenkins

```bash
sudo nano /opt/prometheus/prometheus.yml
```

Replace entire content:

```yaml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'jenkins'
    metrics_path: '/prometheus'
    scheme: http
    static_configs:
      - targets: ['YOUR_VM_IP:8080']
```

> ⚠️ Replace `YOUR_VM_IP` with your actual IP from `hostname -I`.

### 8.4 Create Systemd Service

```bash
sudo nano /etc/systemd/system/prometheus.service
```

Paste:

```ini
[Unit]
Description=Prometheus
After=network.target

[Service]
ExecStart=/opt/prometheus/prometheus \
  --config.file=/opt/prometheus/prometheus.yml \
  --storage.tsdb.path=/opt/prometheus/data
Restart=always

[Install]
WantedBy=multi-user.target
```

### 8.5 Start Prometheus

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now prometheus
sudo systemctl status prometheus
```

### 8.6 Open Firewall

```bash
sudo firewall-cmd --permanent --add-port=9090/tcp
sudo firewall-cmd --reload
```

### 8.7 Verify

Open `http://<YOUR_VM_IP>:9090/targets` — Jenkins should show **UP** ✅

---

## 📈 Phase 9 — Install Grafana on RHEL VM

### 9.1 Add Repository

```bash
sudo nano /etc/yum.repos.d/grafana.repo
```

Paste:

```ini
[grafana]
name=grafana
baseurl=https://packages.grafana.com/oss/rpm
repo_gpgcheck=1
enabled=1
gpgcheck=1
gpgkey=https://packages.grafana.com/gpg.key
sslverify=1
sslcacert=/etc/pki/tls/certs/ca-bundle.crt
```

### 9.2 Install and Start

```bash
sudo dnf install grafana -y
sudo systemctl enable --now grafana-server
sudo systemctl status grafana-server
```

### 9.3 Open Firewall

```bash
sudo firewall-cmd --permanent --add-port=3000/tcp
sudo firewall-cmd --reload
```

### 9.4 Access Grafana

Open `http://<YOUR_VM_IP>:3000`
- Login: `admin` / `admin`
- Set a new password when prompted

---

## 🔌 Phase 10 — Connect Prometheus to Grafana

1. Grafana → **Connections** → **Add new connection**
2. Search **Prometheus** → **Add new data source**
3. URL: `http://localhost:9090`
4. **Save & Test** → ✅ `"Data source is working"`

---

## 🎛️ Phase 11 — Import Jenkins Dashboard

1. Grafana → **"+"** → **Import dashboard**
2. Dashboard ID: `9964` → **Load**
3. Select **Prometheus** as data source
4. **Import**

🎉 **Jenkins monitoring dashboard is live in Grafana!**

---

## 📊 What You See in Grafana

| Metric | Description |
|--------|-------------|
| Build Success Rate | % of successful Jenkins builds |
| Build Duration | How long each build takes |
| Failed Builds | Count of failed jobs |
| Job Queue | Jobs waiting to run |
| Executor Usage | Jenkins worker utilization |
| JVM Memory | Java heap usage of Jenkins |
| CPU Usage | Jenkins server CPU load |

---

## 🛠️ Troubleshooting Guide

| Error | Cause | Fix |
|-------|-------|-----|
| `Package 'jenkins' has no installation candidate` | Jenkins repo not added | Add repo first, then install |
| `java-21-openjdk not found` | Package name differs per distro | Use `openjdk-17-jre` on Ubuntu |
| `firewall-cmd: command not found` | Ubuntu uses different firewall | Use `sudo ufw allow 8080/tcp` |
| `Authentication failed` | Wrong credentials or SCM auth off | Enable SCM Basic Auth in Azure |
| `Could not resolve host: password@...` | `@` in password breaks Git URL | Use alphanumeric-only password |
| `not a full refname` | New Azure repo rejects shorthand | Use `HEAD:refs/heads/main` |
| `Couldn't find any revision to build` | Branch still set to `*/master` | Change to `*/main` |
| Prometheus `status=203/EXEC` | SELinux blocking binary | `sudo chcon -t bin_t /opt/prometheus/prometheus` |
| Grafana shows `N/A` or `No data` | Prometheus not scraping Jenkins | Update `prometheus.yml` with correct VM IP |
| Prometheus target shows `DOWN` | Wrong IP in config | Run `hostname -I` and update config |

---

## 🔒 Port Summary

| Port | Service | Open With |
|------|---------|-----------|
| 8080 | Jenkins | `firewall-cmd --permanent --add-port=8080/tcp` |
| 9090 | Prometheus | `firewall-cmd --permanent --add-port=9090/tcp` |
| 3000 | Grafana | `firewall-cmd --permanent --add-port=3000/tcp` |

```bash
sudo firewall-cmd --reload
sudo firewall-cmd --list-ports
```

---

## 💡 Key Learnings

- **CI/CD removes human error** — every deployment is identical, automated, and auditable.
- **Prometheus + Grafana** adds observability — you can see build failures before they become a problem.
- **SELinux on RHEL** requires context labeling for custom binaries — `chcon -t bin_t` is the fix.
- **Special characters in passwords** (`@`, `#`, `!`) break URL-embedded Git credentials.
- **`prometheus.yml` must use the actual VM IP** — not `localhost` — when systemd resolves services differently.
- **firewall-cmd vs ufw** — RHEL uses `firewall-cmd`; Ubuntu uses `ufw`. Know your distro.

---

## 📂 Project Structure

```
GitHub Repo/
└── index.html          ← Your website source

RHEL VM (VMware)/
├── Jenkins  :8080      ← CI/CD engine + /prometheus metrics endpoint
├── Prometheus :9090    ← Scrapes Jenkins every 15 seconds
└── Grafana  :3000      ← Dashboard — import ID 9964

Azure Cloud/
└── App Service :443    ← Live website (auto-deployed by Jenkins)
```

---

## 📝 A Note for Anyone Who Tries This

Every error in the troubleshooting table above was a real wall — wrong Java version, locked Azure portal, SELinux blocking Prometheus from starting, GPG keys that wouldn't import, and a Grafana dashboard showing `N/A` until the VM IP was corrected in `prometheus.yml`.

If something breaks that isn't listed here, that's not a failure — that's DevOps. Debug it, fix it, own it.

The troubleshooting table grew one error at a time. Maybe yours will too. 🙂

---

*Built with patience, a lot of terminal output, one stubborn RHEL firewall, and a Grafana dashboard that finally showed real data. 🐧*

**GitHub → Jenkins → Azure → Prometheus → Grafana — One push at a time.**
