# 🚀 Wholesale Education Platform - Dev V0.1

> **Professional wholesale education platform with modern design and comprehensive features**

[![Version](https://img.shields.io/badge/version-0.1.0-blue.svg)](https://github.com/CodyZ30/Wholesale-Education)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-development-orange.svg)](https://github.com/CodyZ30/Wholesale-Education)

## 📋 Overview

Wholesale Education Platform is a comprehensive learning management system designed specifically for wholesale business education. Built with modern web technologies, it provides an intuitive interface for students, instructors, and administrators.

## ✨ Features

### 🎓 Core Learning Features
- **Interactive Courses** - Comprehensive wholesale business courses
- **Progress Tracking** - Real-time learning progress monitoring
- **Certificates** - Automated certificate generation upon completion
- **Quizzes & Assessments** - Interactive knowledge testing

### 👥 User Management
- **Student Portal** - Personalized learning dashboard
- **Instructor Tools** - Course creation and management
- **Admin Panel** - Complete system administration
- **Role-based Access** - Secure permission system

### 💼 Business Features
- **Supplier Directory** - Comprehensive supplier database
- **Marketplace Integration** - Connect with wholesale marketplaces
- **Analytics Dashboard** - Business intelligence and reporting
- **API Integration** - Third-party service connections

### 🎨 Modern Design
- **Responsive Design** - Mobile-first approach
- **Dark/Light Mode** - User preference support
- **Accessibility** - WCAG 2.1 compliant
- **Performance Optimized** - Fast loading times

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with custom properties
- **JavaScript (ES6+)** - Interactive functionality
- **Tailwind CSS** - Utility-first CSS framework

### Backend
- **PHP 8.2+** - Server-side logic
- **Node.js** - Real-time features
- **SQLite** - Lightweight database
- **RESTful API** - Clean API architecture

### Development Tools
- **Git** - Version control
- **Composer** - PHP dependency management
- **NPM** - JavaScript package management
- **Docker** - Containerization support

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Node.js 18 or higher
- SQLite 3
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/CodyZ30/Wholesale-Education.git
   cd Wholesale-Education
   ```

2. **Install dependencies**
   ```bash
   # PHP dependencies
   composer install
   
   # Node.js dependencies
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Initialize database**
   ```bash
   php setup-database.js
   ```

5. **Start development server**
   ```bash
   # PHP server
   php -S localhost:8000
   
   # Node.js server (in another terminal)
   node server.js
   ```

6. **Access the application**
   - Main site: http://localhost:8000
   - Admin panel: http://localhost:8000/admin
   - API: http://localhost:3000/api

## 📁 Project Structure

```
Wholesale-Education/
├── 📁 admin/                 # Admin panel
│   ├── 📁 assets/           # Admin assets
│   ├── 📁 includes/         # Admin includes
│   └── 📄 *.php            # Admin pages
├── 📁 api/                  # API endpoints
│   ├── 📁 chat/            # Chat API
│   └── 📁 support/         # Support API
├── 📁 assets/               # Frontend assets
│   ├── 📁 css/             # Stylesheets
│   ├── 📁 js/              # JavaScript files
│   └── 📁 images/          # Images
├── 📁 config/               # Configuration files
├── 📁 data/                 # Data files
├── 📁 includes/             # Core includes
├── 📁 uploads/              # User uploads
├── 📁 views/                # Template views
├── 📄 index.php            # Main entry point
├── 📄 server.js            # Node.js server
├── 📄 composer.json        # PHP dependencies
├── 📄 package.json         # Node.js dependencies
└── 📄 README.md            # This file
```

## 🔧 Configuration

### Environment Variables
```env
# Database
DB_PATH=./wholesale_education.db

# Application
APP_NAME="Wholesale Education"
APP_URL=http://localhost:8000
APP_ENV=development

# Security
SECRET_KEY=your-secret-key-here
SESSION_LIFETIME=86400

# Email
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
```

## 📚 API Documentation

### Authentication
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

### Courses
```http
GET /api/courses
Authorization: Bearer {token}

GET /api/courses/{id}
Authorization: Bearer {token}
```

### Users
```http
GET /api/users/profile
Authorization: Bearer {token}

PUT /api/users/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com"
}
```

## 🧪 Testing

```bash
# Run PHP tests
composer test

# Run JavaScript tests
npm test

# Run all tests
npm run test:all
```

## 🚀 Deployment

### Google Cloud Run
```bash
# Build and deploy
gcloud run deploy wholesale-education \
  --source . \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated
```

### Docker
```bash
# Build image
docker build -t wholesale-education .

# Run container
docker run -p 8000:8000 wholesale-education
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **Cody Z** - Lead Developer & Project Manager
- **Contributors** - See [CONTRIBUTORS.md](CONTRIBUTORS.md)

## 📞 Support

- **Email**: support@wholesale-education.com
- **Documentation**: [docs.wholesale-education.com](https://docs.wholesale-education.com)
- **Issues**: [GitHub Issues](https://github.com/CodyZ30/Wholesale-Education/issues)

## 🗺️ Roadmap

### Version 0.2 (Q2 2025)
- [ ] Advanced analytics dashboard
- [ ] Mobile app (React Native)
- [ ] Payment integration
- [ ] Multi-language support

### Version 0.3 (Q3 2025)
- [ ] AI-powered recommendations
- [ ] Advanced reporting
- [ ] Third-party integrations
- [ ] Enterprise features

---

**Built with ❤️ for the wholesale education community**
