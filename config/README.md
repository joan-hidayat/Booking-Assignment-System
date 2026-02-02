# 🚀 Booking Assignment System

![Status](https://img.shields.io/badge/status-active-success)
![License](https://img.shields.io/badge/license-MIT-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-purple)
![Architecture](https://img.shields.io/badge/architecture-MVC-orange)

A lightweight booking assignment system that automatically assigns marketing ownership using repeat detection and round-robin distribution.

---

## 📸 Screenshot

![App Screenshot](docs/screenshots/preview.png)

> Replace with actual UI screenshot

---

## 🧠 Overview

This system automates booking assignment by checking whether a PIC already exists.

- Existing PIC → repeat booking
- New PIC → round-robin marketing assignment

Designed to ensure fair distribution and customer continuity.

---

## 🏗 Architecture

```mermaid
stateDiagram-v2
    [*] --> CekPIC

    state CekPIC <<choice>>

    CekPIC --> Repeat : PIC exists
    CekPIC --> New : PIC not found

    Repeat : Repeat booking\nReuse marketing
    New : New booking\nRound-robin assignment

    Repeat --> Save
    New --> Save

    Save : Save to database

    Save --> [*]
```

Architecture style: **MVC + Service Layer**

- Controller → request handling
- Service → business logic
- Model/Repository → database access

---

## ⚙️ Tech Stack

- PHP 8
- MySQL
- JavaScript
- HTML/CSS
- MVC Architecture
- Mermaid (documentation diagrams)

---

## 📂 Project Structure

```
project-root/
│
├── app/
│   ├── controllers/
│   ├── services/
│   ├── models/
│   └── views/
│
├── public/
│   └── assets/
│
├── docs/
│   ├── architecture/
│   └── screenshots/
│
├── config/
├── README.md
└── LICENSE
```

---

## 🎯 Features

- Automatic marketing assignment
- Repeat client detection
- Round-robin distribution
- Audit-friendly workflow
- Clean architecture
- Documentation-ready repo

---

## 🧪 Future Improvements

- REST API layer
- Role management
- Booking analytics dashboard
- Notification system
- Queue-based assignment

---

## 📄 License

This project is licensed under the MIT License.
