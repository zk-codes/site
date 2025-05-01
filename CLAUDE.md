# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build and Test Commands
- No explicit build system found
- PHP validation: `php -l filename.php` to check syntax of individual PHP files
- HTML validation: Use W3C validator or `html-validate filename.html` if installed

## Code Style Guidelines
- HTML: Use 4-space indentation, semantic elements, lowercase tags
- Copy & Share Links In HTML: Leave index files in the root directory as just the root link without the 'index.html' on the end
- CSS: Follow modular structure in assets/styles/ directory
- PHP: Use descriptive function names, proper error handling with try/catch
- Naming: Use descriptive names in camelCase for PHP variables/functions
- Documentation: Add comments for PHP functions using PHPDoc format
- Accessibility: Maintain ARIA attributes and a11y best practices
- Links: External links should include rel="noopener" and target="_blank"
- HTML Structure: Follow template.html pattern for consistency
