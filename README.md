# 📚 Academic Abstract Submission System

A comprehensive web-based platform for managing academic abstract submissions, built with PHP, MySQL, and modern web technologies.

## 🎯 Project Overview

This system provides a complete solution for academic institutions to collect, manage, and review academic abstracts from students and researchers. It features a user-friendly submission interface, robust admin dashboard, and automated PDF generation capabilities.

## ✨ Key Features

### 📝 Submission Portal
- **Responsive Design**: Clean, mobile-friendly interface for abstract submissions
- **Rich Form Fields**: Support for author details, co-authors, titles, and abstracts
- **Real-time Validation**: Client-side validation with immediate feedback
- **Word Count**: Built-in word counter for abstract text

### 🔐 Admin Dashboard
- **Secure Login**: Role-based authentication system
- **User Management**: Complete CRUD operations for user accounts
- **Abstract Management**: View, edit, delete, and search submissions
- **Advanced Search**: Filter by name, college, branch, title, or date
- **Bulk Operations**: Export multiple abstracts to PDF

### 📄 PDF Generation
- **Automated PDFs**: Generate professional PDFs for individual abstracts
- **Bulk Export**: Export all abstracts or filtered results
- **Custom Templates**: Professional formatting with college branding
- **Download Options**: Direct download or email delivery

### 📊 Data Management
- **MySQL Database**: Robust data storage with proper indexing
- **Data Validation**: Server-side validation and sanitization
- **Backup Ready**: SQL dump files for easy backup/restore
- **Scalable Architecture**: Designed for growth and expansion

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **PDF Library**: TCPDF
- **Server**: Apache/Nginx (XAMPP compatible)

## 📋 System Requirements

- **PHP**: Version 8.2 or higher
- **MySQL**: Version 5.7 or higher
- **Web Server**: Apache/Nginx
- **Browser**: Modern browsers (Chrome, Firefox, Safari, Edge)

## 🚀 Installation Guide

### 1. Clone/Download the Project
```bash
# Extract to your web server directory
# Example: c:/xampp/htdocs/magazine/
```

### 2. Database Setup
```sql
-- Create database
CREATE DATABASE magazine;

-- Import the provided SQL file
mysql -u root -p magazine < magazine.sql
```

### 3. Configuration
Update database connection in these files:
- `dashboard.php`
- `login.php`
- `insert.php`

### 4. Web Server Setup
- **XAMPP**: Place in `htdocs/magazine/`
- **WAMP**: Place in `www/magazine/`
- **LAMP**: Place in `/var/www/html/magazine/`

### 5. Access the System
- **User Portal**: `http://localhost/magazine/index.html`
- **Admin Login**: `http://localhost/magazine/login.php`

## 🔧 Configuration Files

### Database Connection
```php
// Update in dashboard.php, login.php, insert.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magazine";
```

### Email Settings (Optional)
Update SMTP settings in `tcpdf/config/tcpdf_config.php` for email notifications.

## 📊 Database Schema

### Main Tables
- **mag**: Stores abstract submissions
- **users**: Stores admin user accounts

### mag Table Structure
```sql
CREATE TABLE `mag` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `college` varchar(50) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `abstract` text NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

## 🎯 Usage Guide

### For Submitters
1. Navigate to `index.html`
2. Fill in all required fields
3. Submit the abstract
4. Receive confirmation and ID for tracking

### For Administrators
1. Login at `login.php`
2. Access dashboard at `dashboard.php`
3. View all submissions in table format
4. Use search/filter options
5. Generate PDFs for individual or bulk exports

### PDF Generation Features
- **Individual PDF**: Click PDF button on any submission
- **Bulk Export**: Use "Export to PDF" button
- **Filtered Export**: Search first, then export results

## 🔐 Security Features

- **Input Sanitization**: All user inputs are sanitized
- **SQL Injection Prevention**: Prepared statements used
- **Password Security**: Encrypted passwords (production ready)
- **Session Management**: Secure session handling
- **Role-Based Access**: Different permissions for different roles

## 🔄 Maintenance

### Regular Tasks
- **Database Backups**: Use `magazine.sql` for regular dumps
- **Log Monitoring**: Check PHP error logs
- **Security Updates**: Keep PHP and MySQL updated
- **User Management**: Regular review of admin accounts

### Backup Strategy
```bash
# Database backup
mysqldump -u root -p magazine > backup_$(date +%Y%m%d).sql

# Files backup
tar -czf magazine_backup_$(date +%Y%m%d).tar.gz /path/to/magazine/
```

## 🐛 Troubleshooting

### Common Issues
1. **Database Connection**: Check credentials in config files
2. **PDF Generation**: Ensure TCPDF library is properly installed
3. **File Uploads**: Check PHP upload limits in `php.ini`
4. **Session Issues**: Clear browser cookies and cache

### Error Logs
- **PHP Errors**: Check `php_error.log`
- **MySQL Errors**: Check MySQL error log
- **Apache Errors**: Check `error.log`

## 📞 Support

For technical support or feature requests:
1. Check the troubleshooting section
2. Review error logs
3. Ensure all requirements are met
4. Contact system administrator

## 📄 License

This project is open-source and available under the MIT License. See LICENSE.TXT for details.

## 🤝 Contributing

We welcome contributions! Please:
1. Fork the repository
2. Create a feature branch
3. Submit a pull request with clear description

## 🔄 Version History

- **v1.0.0**: Initial release with core features
- **v1.1.0**: Added PDF generation and admin dashboard
- **v1.2.0**: Enhanced search and export capabilities

---

**Built with ❤️ for Academic Excellence**
**PREVIEW**
<img width="1606" height="936" alt="image" src="https://github.com/user-attachments/assets/cc3fb586-202d-4ee5-a2a7-fd29b2e46880" />

<img width="1646" height="898" alt="image" src="https://github.com/user-attachments/assets/20cf10c8-da91-4482-a626-3c1443773892" />
<img width="1665" height="978" alt="image" src="https://github.com/user-attachments/assets/ac77cfc5-9deb-4d4f-bd6b-160364e1ebda" />
**IF WE CLICK ON VIEW DETAILS**
<img width="1583" height="818" alt="image" src="https://github.com/user-attachments/assets/4a4e8d68-59ff-4a0f-a190-33fe5f6a175b" />
**IF WE CLICK ON DOWNLOAD PDF PDF WILL BE DOWNLOADED**

