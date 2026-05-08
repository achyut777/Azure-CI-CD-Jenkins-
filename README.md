# DevOps Mini Project — Jenkins CI/CD Pipeline

🚀 **Automated Static Website Deployment**
`GitHub → Jenkins → Azure App Service`

![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?style=for-the-badge&logo=ubuntu&logoColor=white)
![Jenkins](https://img.shields.io/badge/Jenkins-D24939?style=for-the-badge&logo=jenkins&logoColor=white)
![Azure](https://img.shields.io/badge/Azure-0078D4?style=for-the-badge&logo=microsoftazure&logoColor=white)
![Git](https://img.shields.io/badge/Git-F05032?style=for-the-badge&logo=git&logoColor=white)

A beginner-friendly DevOps mini-project that automates website deployment using a Jenkins CI/CD pipeline on an **Ubuntu VM on Azure**, pushing code directly to **Microsoft Azure App Service**.

---

## 📌 What is This Project?

Every time you update your website code, someone has to manually upload it to the server. That's slow, error-prone, and unprofessional.

This project eliminates that entirely.

Once set up, you only need to edit your code on GitHub and commit the change. Within 60 seconds, Jenkins automatically detects the change, pulls the latest code, and pushes it live to Azure — without you touching anything else.

This is called a **CI/CD Pipeline** (Continuous Integration / Continuous Deployment), and it is a core skill used by DevOps engineers in the real world.

---

## 🏗️ System Architecture

```
Developer (You)
     |
     | git commit + push
     ▼
┌─────────────┐
│   GitHub    │  ← Stores your source code (index.html)
└──────┬──────┘
       │ Jenkins polls every 1 min
       ▼
┌──────────────────────────────┐
│  Jenkins on Ubuntu Azure VM  │  ← CI/CD Engine (your Linux server)
│  - Pulls latest code         │
│  - Runs deploy shell script  │
└──────────────┬───────────────┘
               │ git push via HTTPS
               ▼
┌──────────────────────────────┐
│  Microsoft Azure App Service │  ← Live website on the internet
│  (your-app.azurewebsites.net)│
└──────────────────────────────┘
```

---

## 🧰 Tech Stack

| Tool | Role |
|------|------|
| GitHub | Source code storage (version control) |
| Ubuntu (Azure VM) | Server OS where Jenkins runs |
| Jenkins | CI/CD automation engine |
| Microsoft Azure App Service | Cloud hosting for the live website |
| Git | Code transfer between all three systems |
| Azure CLI | Used to set Azure deployment credentials via terminal |

---

## ✅ Prerequisites

Before you begin, make sure you have the following ready:

- A GitHub account with your `index.html` file in a public repository
- An **Ubuntu Virtual Machine** on Azure with internet access
- A Microsoft Azure account (free tier works perfectly for this project)
- Basic comfort using a Linux terminal

> 💡 You can get a free Azure account with $200 credits at [azure.microsoft.com/free](https://azure.microsoft.com/free). This project costs nothing on the free tier.

---

## 📁 Phase 0 — Prepare Your Code on GitHub

1. Log in to [github.com](https://github.com) and create a new repository.
2. Upload your `index.html` file into the repository.
3. Make sure your default branch is named `main`.

> 💡 Keep your GitHub repository **Public**. This way Jenkins can access it without needing any login credentials for GitHub.

---

## ☁️ Phase 1 — Set Up Azure App Service

### 1.1 Create the Web App

1. Log in to the [Azure Portal](https://portal.azure.com).
2. Click **Create a resource** → search for **Web App** → click **Create**.
3. Fill in the details:
   - **Resource Group:** Create a new one (e.g., `devops-project-rg`)
   - **Name:** Give it a unique name — this becomes your URL
   - **Publish:** Select `Code`
   - **Runtime Stack:** Select `PHP 8.2` or `Node.js`
   - **Operating System:** Linux
   - **Region:** Choose the one closest to you
4. Click **Review + Create** → **Create**.

> ⚠️ Choose your app name carefully. Azure uses it as your public URL (`yourname.azurewebsites.net`) and it cannot be changed later.

### 1.2 Configure Deployment Source

1. Once your Web App is created, open it and go to **Deployment Center** on the left menu.
2. Under the **Settings** tab, set **Source** to `Local Git`.
3. Click **Save** at the top.
4. After saving, the page will show a **Git Clone URI**. Copy and save it. It looks like:
   ```
   https://your-app-name.scm.azurewebsites.net/your-app-name.git
   ```

### 1.3 Enable SCM Authentication

1. On the left menu, click **Configuration** → then click the **General settings** tab.
2. Find **SCM Basic Auth Publishing Credentials** and switch it to **On**.
3. Click **Save** and wait for the confirmation message.

> 💡 This step is frequently missed and causes most `Authentication failed` errors. Always enable this before testing your pipeline.

### 1.4 Create Deployment Credentials via Azure Cloud Shell

Open the Azure Cloud Shell (`>_` icon at the top of the portal) and run:

```bash
az webapp deployment user set --user-name YOUR_UNIQUE_NAME --password YourPassword123
```

**Password rules:**
- Minimum 8 characters
- Must contain uppercase, lowercase, and a number
- **Do NOT use special characters like `@` or `#`** — they break the Git URL format

> ⚠️ Using a password with an `@` symbol (e.g., `Admin@1234`) will cause a `Could not resolve host` error in Jenkins because Git misreads the `@` in the URL. Use a plain alphanumeric password like `Admin1234` instead.

---

## 🔧 Phase 2 — Prepare Your Ubuntu VM

Connect to your Ubuntu VM via terminal and run the following commands one by one.

### 2.1 Install Java 17

```bash
sudo apt update
sudo apt install fontconfig openjdk-17-jre -y
```

Verify:
```bash
java -version
```

### 2.2 Install Git and curl

```bash
sudo apt install git curl -y
```

---

## ⚙️ Phase 3 — Install Jenkins on Ubuntu

### 3.1 Add Jenkins GPG Key

```bash
sudo wget -O /usr/share/keyrings/jenkins-keyring.asc \
  https://pkg.jenkins.io/debian-stable/jenkins.io-2023.key
```

> ⚠️ Common Mistake: Using `wget` without `gpg --dearmor` causes a `NO_PUBKEY` GPG error. The command above handles this correctly.

### 3.2 Add Jenkins Repository

```bash
echo "deb [signed-by=/usr/share/keyrings/jenkins-keyring.asc] \
  https://pkg.jenkins.io/debian-stable binary/" \
  | sudo tee /etc/apt/sources.list.d/jenkins.list > /dev/null
```

### 3.3 Install Jenkins

```bash
sudo apt update && sudo apt install jenkins -y
```

> ⚠️ Common Mistake: Running `sudo apt install jenkins` **before** adding the Jenkins repository will fail with `Package 'jenkins' has no installation candidate`. Always add the repo first.

### 3.4 Start Jenkins

```bash
sudo systemctl enable --now jenkins
```

Verify it is running:
```bash
sudo systemctl status jenkins
# Look for: Active: active (running)
```

### 3.5 Open the Firewall Port

Ubuntu uses **UFW** instead of `firewall-cmd`:

```bash
sudo ufw allow 8080/tcp
sudo ufw enable
sudo ufw status
```

> ⚠️ Also open port `8080` in **Azure Portal → VM → Networking → Add inbound port rule** or Jenkins won't be accessible from your browser even if UFW allows it.

---

## 🌐 Phase 4 — Unlock and Configure Jenkins in the Browser

### 4.1 Find Your VM's Public IP

```bash
curl ifconfig.me
```

### 4.2 Get the Admin Password

```bash
sudo cat /var/lib/jenkins/secrets/initialAdminPassword
```

### 4.3 First-Time Setup

1. Open a browser and go to: `http://<your-vm-ip>:8080`
2. Paste the admin password and click **Continue**.
3. Click **Install suggested plugins** and wait (takes 3–5 minutes).
4. Create your Jenkins admin username and password.
5. Click **Save and Finish** → **Start using Jenkins**.

---

## 🔗 Phase 5 — Create the CI/CD Pipeline in Jenkins

### 5.1 Create a New Job

1. On the Jenkins dashboard, click **New Item**.
2. Type a project name (e.g., `Azure-Deploy-Pipeline`).
3. Select **Freestyle project** → click **OK**.

### 5.2 Connect to GitHub

1. Scroll to **Source Code Management** → select **Git**.
2. In the **Repository URL** box, paste your GitHub repository URL:
   ```
   https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
   ```
3. Leave **Credentials** as `None` (public repo).
4. Under **Branches to build**, change `*/master` to `*/main`.

> ⚠️ GitHub's default branch is now called `main`, not `master`. Leaving it as `*/master` will cause Jenkins to fail with: `Couldn't find any revision to build`.

### 5.3 Set the Automation Trigger

1. Scroll to **Build Triggers**.
2. Check **Poll SCM**.
3. In the **Schedule** box, type:
   ```
   * * * * *
   ```
   (Five asterisks — checks GitHub every 1 minute.)

### 5.4 Add the Deployment Script

1. Scroll to **Build Steps** → **Add build step** → **Execute shell**.
2. Paste the following command, replacing placeholders with your values:

```bash
git push https://YOUR_AZURE_USERNAME:YOUR_AZURE_PASSWORD@YOUR_SCM_URI:443/YOUR_APP_NAME.git HEAD:refs/heads/main --force
```

**Example with real values:**

```bash
git push https://achyut777:Admin1234@cicd-jenkins.scm.azurewebsites.net:443/cicd-jenkins.git HEAD:refs/heads/main --force
```

> 💡 **Why `refs/heads/main`?** When pushing to a brand new, empty Azure Git repository for the first time, you must use the full Git refname (`HEAD:refs/heads/main`). Using just `HEAD:main` will fail with: `The destination you provided is not a full refname`.

### 5.5 Save the Configuration

Click the **Save** button at the bottom of the page.

---

## 🧪 Phase 6 — Test and Verify

### 6.1 Run the First Manual Build

1. Click **Build Now** on the left side menu.
2. Click on the build number → click **Console Output**.
3. Scroll to the bottom. You should see:
   ```
   remote: Deployment successful.
   Finished: SUCCESS
   ```

### 6.2 View Your Live Website

Open your browser and go to your Azure URL:
```
https://your-app-name.azurewebsites.net
```

You should see your `index.html` live on the internet. 🎉

### 6.3 Test the Full Automation

1. Go to your `index.html` file on GitHub and click the **Edit** (pencil) icon.
2. Change any visible text (e.g., change a heading).
3. Click **Commit changes**.
4. Go back to your Jenkins dashboard and **do not click anything**.
5. Within 60 seconds, a new build will automatically appear and run.
6. Once it finishes with `SUCCESS`, refresh your Azure website — your change will be live.

---

## 🛠️ Troubleshooting Guide

| Error | Cause | Fix |
|-------|-------|-----|
| `Package 'jenkins' has no installation candidate` | Jenkins repo not added before install | Add GPG key and repo first, then `apt update` |
| `NO_PUBKEY 7198F4B714ABFC68` | GPG key not correctly dearmored | Use `curl + gpg --dearmor` method instead of wget |
| `Unable to locate package java-21-openjdk` | Wrong package name for Ubuntu | Use `openjdk-17-jre` — Ubuntu naming is different from RHEL |
| `ERR_CONNECTION_TIMED_OUT` on port 8080 | Azure NSG blocking the port | Add inbound rule for port 8080 in Azure Portal Networking |
| `Authentication failed` | Wrong Azure credentials or SCM auth disabled | Enable SCM Basic Auth in Azure → Configuration → General Settings |
| `Could not resolve host: password@...` | Password contains `@` symbol breaking the URL | Use alphanumeric-only password (no `@`, `#`, `!`) |
| `not a full refname` | New Azure repo doesn't recognize shorthand | Use `HEAD:refs/heads/main` instead of `HEAD:main` |
| `Couldn't find any revision to build` | Branch set to `*/master` but repo uses `main` | Change branch to `*/main` in Source Code Management |
| `firewall-cmd: command not found` | Ubuntu doesn't use firewall-cmd | Use `sudo ufw allow 8080/tcp` instead |

---

## 💡 Key Learnings

- **Ubuntu vs RHEL:** Package names and firewall tools differ between distributions. `java-21-openjdk` is RHEL syntax; Ubuntu uses `openjdk-17-jre`. `firewall-cmd` is RHEL; Ubuntu uses `ufw`.
- **Azure's web portal can be unreliable** for credential management. The Azure CLI (`az` commands) is always more reliable and is the preferred method for automation.
- **CI/CD removes human error** from deployments. Every push is done exactly the same way, every time.
- **Special characters in passwords** (`@`, `#`, `!`) break URL-embedded credentials. Always use alphanumeric passwords in automation scripts.
- **NSG + UFW:** On an Azure VM, both Azure's Network Security Group AND the VM's firewall (UFW) must allow a port for it to be accessible.

---

## 📂 Project Structure

```
your-github-repo/
│
└── index.html        ← Your entire website (single file)
```

---

## 📝 A Note for Anyone Who Tries This

Every error in the troubleshooting table above was a real wall that had to be broken through — the wrong package names on Ubuntu, the GPG key that wouldn't import, the Azure portal fields that stayed grayed out.

If you are going through this guide and something breaks in a way that isn't listed here, that's not a failure — that's just how DevOps actually works. Debug it, fix it, and that fix becomes yours to keep.

The troubleshooting table in this README grew one error at a time. Maybe yours will too. 🙂

---

*Built with patience, a lot of terminal output, and one very stubborn Ubuntu firewall. 🐧*

**GitHub → Jenkins → Azure — One push at a time.**
