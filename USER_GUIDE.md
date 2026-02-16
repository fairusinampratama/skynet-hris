# Skynet HRIS - User Guide

This guide explains how to use the Face Verification features in the Skynet HRIS application.

## 1. Getting Started

-   **Login**: Access the system with your credentials.
-   **Dashboard**: Your central hub for navigation.

---

## 2. Face Registration (First Time Setup)

Before you can check in, you must register your face data. **This is a one-time process.**

1.  **Navigate**: Go to your **Profile** or click the **"Register Face"** link on the dashboard if prompted.
2.  **Camera Access**: Allow the browser to access your camera.
3.  **Position Yourself**:
    -   Center your face in the **Viewfinder**.
    -   Ensure you are in a well-lit area.
    -   Look straight at the camera.
4.  **Wait for "High Quality"**: The system analyzes your face quality.
    -   **Red**: No face found.
    -   **Yellow**: Face found but needs adjustment (too far, dark, or angled).
    -   **Green**: Face detected and good quality.
5.  **Capture**: Once the status is **Green**, click **"Capture & Register"**.
6.  **Success**: You will see a success message. Your face ID is now locked.

> **Note**: If you see a "Face ID Registered" message, you are already set up. Contact HR if you need to change it.

---

## 3. Daily Attendance

### Check In
1.  **Navigate**: Go to the **Attendance** page.
2.  **Permissions**: Allow **Camera** and **Location** access.
3.  **Verification**:
    -   The system will automatically scan your face.
    -   The Viewfinder will turn **Green** when you are recognized.
    -   Status will say **"Ready to Check In"**.
4.  **Confirm**: Click **"Authenticate & Check In"**.

**Troubleshooting Failures:**
-   **"Face verification failed"**: The system sees a face but it doesn't match your registered profle. Move closer or improve lighting.
-   **"No face data"**: Ensure the camera isn't blocked.
-   **"Out of office bounds"**: You are too far from the office location (for office staff).

### Check Out
1.  **Navigate**: Return to the **Attendance** page.
2.  **Click**: Press **"Check Out"**.
3.  **Summary (Technicians)**: You may be required to enter a work summary before checking out.

---

## 4. Administrator Guide

### Managing Face IDs
Administrators can reset a user's face data if they need to re-register (e.g., mistaken registration or new appearance).

1.  **Navigate**: Go to the **Admin Panel** -> **Employees**.
2.  **Identify User**:
    -   Look at the **"Face Registered"** column.
    -   <span style="color:green">✔</span> = Registered
    -   <span style="color:red">✘</span> = Not Registered
3.  **Reset**:
    -   Click the **"Reset Face ID"** button (orange refresh icon) on the user's row.
    -   Confirm the action.
    -   The user can now register again.
