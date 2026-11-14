-- ========================================================
-- Prime Care Health Solutions — Database Schema (SQL)
-- Compatible with SQLite, MySQL / MariaDB, and PostgreSQL
-- ========================================================

-- 1. Appointments Table (Patient Visit Bookings)
CREATE TABLE IF NOT EXISTS appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ref VARCHAR(40) UNIQUE NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    location VARCHAR(60) NOT NULL,
    service VARCHAR(60) NOT NULL,
    patient_type VARCHAR(30) DEFAULT 'new',
    preferred_date VARCHAR(30),
    preferred_time VARCHAR(30),
    contact_method VARCHAR(30) DEFAULT 'phone',
    notes TEXT,
    status VARCHAR(30) DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Patients & Digital Health ID Cards Table
CREATE TABLE IF NOT EXISTS patients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    card_number VARCHAR(40) UNIQUE NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    dob VARCHAR(30),
    gender VARCHAR(20),
    blood_group VARCHAR(10),
    phone VARCHAR(40),
    email VARCHAR(120),
    emergency_contact VARCHAR(120),
    address VARCHAR(255),
    issue_date VARCHAR(30),
    expiry_date VARCHAR(30),
    status VARCHAR(30) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Inquiries & Contact Messages Table
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    phone VARCHAR(40),
    location VARCHAR(60),
    subject VARCHAR(150),
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS subscribers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(120) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Administrators Table (Control Panel Access)
CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(60) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for Fast Query Performance
CREATE INDEX IF NOT EXISTS idx_appointments_ref ON appointments(ref);
CREATE INDEX IF NOT EXISTS idx_appointments_status ON appointments(status);
CREATE INDEX IF NOT EXISTS idx_patients_card ON patients(card_number);
CREATE INDEX IF NOT EXISTS idx_messages_read ON messages(is_read);

-- ========================================================
-- Seed Data / Demonstration Records
-- ========================================================

-- Sample Patient Card
INSERT INTO patients (card_number, full_name, dob, gender, blood_group, phone, email, emergency_contact, address, issue_date, expiry_date, status)
VALUES ('PC-2026-1042', 'Evelyn Montgomery', '1962-04-18', 'Female', 'O+', '+1 (404) 555-0193', 'evelyn.montgomery@example.com', 'David Montgomery (Son) +1 (404) 555-0194', '1420 Peachtree Rd NE, Atlanta, GA', '2026-01-15', '2027-01-15', 'active');
