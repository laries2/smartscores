# SmartScores - Event Scoring System

SmartScores is a LAMP stack application for managing event scoring. It allows judges to assign points to participants and displays a real-time scoreboard.

## Features

- **Admin Panel**: Manage judges in the system
- **Judge Portal**: Judges can assign scores to participants
- **Public Scoreboard**: Real-time display of participant rankings with auto-refresh

## Setup Instructions

### Prerequisites

- XAMPP (or any LAMP stack with PHP 7.0+ and MySQL 5.6+)
- Web browser

### Installation

1. Clone or download this repository to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\smartscores\
   ```

2. Start Apache and MySQL services in XAMPP Control Panel

3. Import the database schema:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `smartscores`
   - Import the SQL file from `database/schema.sql`

4. Access the application:
   - Admin Panel: http://localhost/smartscores/admin/dashboard.php
   - Judge Portal: http://localhost/smartscores/judges/login.php
   - Public Scoreboard: http://localhost/smartscores/index.php

## Database Schema

The application uses three main tables:

### judges
```sql
CREATE TABLE judges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### participants
```sql
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### scores
```sql
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    judge_id INT NOT NULL,
    points INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id),
    FOREIGN KEY (judge_id) REFERENCES judges(id)
);
```

## Design Choices

### LAMP Stack Implementation
- **Linux/Windows**: The application runs on any OS that supports XAMPP
- **Apache**: Used for serving PHP files and handling HTTP requests
- **MySQL**: Relational database for storing judges, participants, and scores
- **PHP**: Server-side scripting language for business logic and database interaction

### Security Considerations
- PDO with prepared statements for database queries to prevent SQL injection
- Input validation and sanitization for all form submissions
- HTML escaping to prevent XSS attacks

### Frontend Design
- Bootstrap 4 for responsive layout and UI components
- Simple and intuitive interface for all user types
- Color-coded scoreboard for easy visualization of rankings

### Database Design
- Normalized structure with proper relationships between tables
- Foreign key constraints to maintain data integrity
- Efficient queries for calculating total scores

## Future Enhancements

With more time, the following features could be added:

1. **User Authentication**: Proper login system with password hashing for judges and admins
2. **Real-time Updates**: WebSockets for instant scoreboard updates without page refresh
3. **Participant Management**: Admin interface for adding/editing participants
4. **Responsive Design Improvements**: Better mobile experience
5. **API**: RESTful API for integration with other systems

