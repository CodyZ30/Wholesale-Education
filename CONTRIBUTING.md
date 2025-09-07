# Contributing to Wholesale Education Platform

Thank you for your interest in contributing to the Wholesale Education Platform! We welcome contributions from the community and appreciate your help in making this project better.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contributing Guidelines](#contributing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Documentation](#documentation)

## 🤝 Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to [cody@wholesale-education.com](mailto:cody@wholesale-education.com).

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

## 🚀 Getting Started

### Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2+** with extensions: PDO, SQLite, JSON, MBString, cURL, OpenSSL
- **Node.js 18+** and npm
- **Git** for version control
- **Composer** for PHP dependencies
- **SQLite 3** for database

### Development Setup

1. **Fork the repository**
   ```bash
   # Fork on GitHub, then clone your fork
   git clone https://github.com/YOUR_USERNAME/Wholesale-Education.git
   cd Wholesale-Education
   ```

2. **Set up the development environment**
   ```bash
   # Install PHP dependencies
   composer install
   
   # Install Node.js dependencies
   npm install
   
   # Copy environment file
   cp env.example .env
   
   # Initialize database
   npm run setup
   ```

3. **Start development servers**
   ```bash
   # Start PHP server (terminal 1)
   php -S localhost:8000
   
   # Start Node.js server (terminal 2)
   npm run dev
   ```

4. **Verify installation**
   - Main site: http://localhost:8000
   - Admin panel: http://localhost:8000/admin
   - API: http://localhost:3000/api

## 📝 Contributing Guidelines

### Types of Contributions

We welcome several types of contributions:

- 🐛 **Bug Reports** - Help us identify and fix issues
- ✨ **Feature Requests** - Suggest new functionality
- 💻 **Code Contributions** - Submit bug fixes or new features
- 📖 **Documentation** - Improve our documentation
- 🧪 **Testing** - Add or improve tests
- 🎨 **Design** - Improve UI/UX

### Before You Start

1. **Check existing issues** - Look for similar issues or feature requests
2. **Create an issue** - For significant changes, create an issue first
3. **Discuss** - Engage with maintainers and community
4. **Plan** - Break down large changes into smaller, manageable tasks

## 🔄 Pull Request Process

### 1. Create a Branch

```bash
# Create and switch to a new branch
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

### 2. Make Your Changes

- Write clean, readable code
- Follow our coding standards
- Add tests for new functionality
- Update documentation as needed
- Ensure all tests pass

### 3. Commit Your Changes

```bash
# Stage your changes
git add .

# Commit with a descriptive message
git commit -m "feat: add user authentication system

- Implement JWT-based authentication
- Add login/logout functionality
- Include password reset feature
- Add comprehensive tests

Closes #123"
```

### 4. Push and Create PR

```bash
# Push your branch
git push origin feature/your-feature-name

# Create a Pull Request on GitHub
```

### 5. Pull Request Template

When creating a PR, please include:

- **Description** - What changes were made and why
- **Type** - Bug fix, feature, documentation, etc.
- **Testing** - How you tested the changes
- **Screenshots** - For UI changes
- **Breaking Changes** - Any breaking changes
- **Related Issues** - Link to related issues

## 🐛 Issue Reporting

### Bug Reports

When reporting bugs, please include:

- **Environment** - OS, PHP version, Node.js version
- **Steps to Reproduce** - Clear, numbered steps
- **Expected Behavior** - What should happen
- **Actual Behavior** - What actually happens
- **Screenshots** - If applicable
- **Error Messages** - Full error messages and stack traces

### Feature Requests

For feature requests, please include:

- **Use Case** - Why is this feature needed?
- **Proposed Solution** - How should it work?
- **Alternatives** - Other solutions you've considered
- **Additional Context** - Any other relevant information

## 📏 Coding Standards

### PHP Standards

- Follow **PSR-12** coding standard
- Use **type declarations** for all functions
- Write **PHPDoc** comments for all public methods
- Use **meaningful variable and function names**
- Keep functions **small and focused**

```php
<?php

declare(strict_types=1);

namespace WholesaleEducation\Services;

use WholesaleEducation\Models\User;

/**
 * User authentication service
 */
class AuthService
{
    /**
     * Authenticate user with email and password
     *
     * @param string $email User email address
     * @param string $password User password
     * @return User|null Authenticated user or null
     */
    public function authenticate(string $email, string $password): ?User
    {
        // Implementation here
    }
}
```

### JavaScript Standards

- Use **ES6+** features
- Follow **Standard JS** style guide
- Use **meaningful variable names**
- Write **JSDoc** comments for functions
- Use **async/await** instead of callbacks

```javascript
/**
 * Authenticate user with credentials
 * @param {string} email - User email address
 * @param {string} password - User password
 * @returns {Promise<User|null>} Authenticated user or null
 */
async function authenticateUser (email, password) {
  try {
    const response = await fetch('/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, password })
    })
    
    return response.ok ? await response.json() : null
  } catch (error) {
    console.error('Authentication failed:', error)
    return null
  }
}
```

### CSS Standards

- Use **Tailwind CSS** utility classes
- Follow **BEM methodology** for custom CSS
- Use **CSS custom properties** for theming
- Keep **specificity low**
- Use **semantic class names**

```css
/* Custom component styles */
.user-profile {
  @apply bg-white rounded-lg shadow-md p-6;
}

.user-profile__header {
  @apply flex items-center justify-between mb-4;
}

.user-profile__avatar {
  @apply w-16 h-16 rounded-full object-cover;
}

.user-profile__name {
  @apply text-xl font-semibold text-gray-900;
}
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
npm test

# Run PHP tests
composer test

# Run JavaScript tests
npm run test:js

# Run with coverage
npm run test:coverage
```

### Writing Tests

- Write **unit tests** for all new functions
- Write **integration tests** for API endpoints
- Write **end-to-end tests** for critical user flows
- Aim for **80%+ code coverage**
- Use **descriptive test names**

```php
<?php

namespace WholesaleEducation\Tests\Services;

use PHPUnit\Framework\TestCase;
use WholesaleEducation\Services\AuthService;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->authService = new AuthService();
    }

    public function testAuthenticateWithValidCredentials(): void
    {
        $user = $this->authService->authenticate('test@example.com', 'password123');
        
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testAuthenticateWithInvalidCredentials(): void
    {
        $user = $this->authService->authenticate('test@example.com', 'wrongpassword');
        
        $this->assertNull($user);
    }
}
```

## 📖 Documentation

### Code Documentation

- Write **clear comments** for complex logic
- Use **PHPDoc/JSDoc** for all public APIs
- Keep **README files** up to date
- Document **API endpoints** thoroughly

### User Documentation

- Write **clear installation instructions**
- Provide **usage examples**
- Include **troubleshooting guides**
- Keep **changelog** updated

## 🏷️ Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/) format:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```
feat(auth): add JWT authentication system

- Implement JWT token generation
- Add middleware for token validation
- Include refresh token functionality

Closes #123
```

```
fix(api): resolve user creation validation error

The user creation endpoint was not properly validating
email addresses, causing duplicate entries.

Fixes #456
```

## 🎯 Development Workflow

1. **Create Issue** - Document the problem or feature
2. **Fork Repository** - Create your own copy
3. **Create Branch** - Use descriptive branch names
4. **Make Changes** - Follow coding standards
5. **Write Tests** - Ensure your code works
6. **Update Docs** - Keep documentation current
7. **Submit PR** - Create pull request with description
8. **Review Process** - Address feedback and suggestions
9. **Merge** - Once approved, your changes are merged!

## 🆘 Getting Help

- **GitHub Discussions** - For questions and general discussion
- **GitHub Issues** - For bug reports and feature requests
- **Email** - [cody@wholesale-education.com](mailto:cody@wholesale-education.com)
- **Documentation** - Check our comprehensive docs

## 🙏 Recognition

Contributors will be recognized in:

- **README.md** - Listed as contributors
- **CHANGELOG.md** - Mentioned in release notes
- **GitHub** - Listed in contributors section

Thank you for contributing to the Wholesale Education Platform! 🎉

---

**Happy Coding! 🚀**
