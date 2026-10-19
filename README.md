# 🎓 Dual Authentication Attendance Checker

A secure and modern attendance management system that uses **Dual Authentication** through:

- 📱 Personal QR Code
- 👤 Face Recognition

The system is designed to reduce attendance fraud by requiring both authentication factors before a student's attendance can be recorded.

---

## ✨ Overview

The **Dual Authentication Attendance Checker** is a web-based attendance management system developed using **Laravel 13**, with a separate **Python Face Recognition Service**.

Instead of relying on only a QR code or manual attendance, the system verifies the student's identity using two independent authentication factors.

### 🔐 Dual Authentication Flow

```text
┌──────────────────────────────┐
│ Student Presents Personal QR │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│       Teacher Scans QR       │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│      Validate QR Token       │
└──────────────┬───────────────┘
               │
          ┌────┴────┐
          │         │
       Invalid     Valid
          │         │
          ▼         ▼
   ┌──────────┐  ┌──────────────────┐
   │    QR    │  │ Face Recognition │
   │ Rejected │  └────────┬─────────┘
   └──────────┘           │
                          ▼
                 ┌──────────────────┐
                 │  Match Face to   │
                 │ Student Record   │
                 └────────┬─────────┘
                          │
                     ┌────┴────┐
                     │         │
                   Match    Mismatch
                     │         │
                     ▼         ▼
             ┌────────────┐  ┌──────────┐
             │   Both     │  │   Face   │
             │  Factors   │  │ Mismatch │
             │ Confirmed  │  └──────────┘
             └─────┬──────┘
                   │
                   ▼
          ┌──────────────────┐
          │ Student Marked   │
          │     Present      │
          └──────────────────┘
