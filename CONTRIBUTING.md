# Contributing

Thank you for your interest in contributing to the Ollama PHP 5.6 Client!

## How to Contribute

### Reporting Bugs

1. Check if the bug has not already been reported in existing issues
2. Create a new issue with:
   - Clear description of the problem
   - Steps to reproduce
   - PHP version
   - Ollama version
   - Example code that demonstrates the problem

### Requesting Features

1. Open an issue describing:
   - The desired functionality
   - Use cases
   - Examples of how it should work

### Submitting Pull Requests

1. Fork the project
2. Create a branch for your feature (`git checkout -b feature/new-feature`)
3. Make your changes
4. Add tests if applicable
5. Run existing tests
6. Commit your changes (`git commit -am 'Add new feature'`)
7. Push to the branch (`git push origin feature/new-feature`)
8. Open a Pull Request

## Code Standards

- Follow PSR-1 and PSR-2 for PHP code style
- Use PHPDoc comments to document methods and classes
- Maintain compatibility with PHP 5.6+
- Write tests for new functionalities

## Setting up Development Environment

```bash
# Clone the project
git clone https://github.com/vluzr/ollama-php56.git
cd ollama-php56

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit
```

## Testing

Make sure that:
1. All tests pass
2. Your contribution includes adequate tests
3. The code works with PHP 5.6, 7.x and 8.x

## License

By contributing, you agree that your contributions will be licensed under the same MIT license as the project.
