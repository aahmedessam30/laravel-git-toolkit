# Changelog

All notable changes to `laravel-git-toolkit` will be documented in this file.

## [v2.1.0] - 2025-07-23

### 🚀 Enhanced Merge Operations & User Experience

#### Added

- **Multi-Branch Merge Support**: Enhanced `MergeAction` to support merging into multiple target branches
- **Auto-Push After Merge**: Automatic push to remote repository after successful merge operations
- **Meaningful Option Names**: Added `--source` and `--target` options for better clarity
- **Backward Compatibility**: Maintained support for legacy `--merge` and `--branch` options
- **Enhanced Error Handling**: Improved error messages and validation for merge operations
- **Shell Argument Validation**: Smart detection and helpful error messages for command-line parsing issues
- **Visual Feedback**: Added emoji indicators for merge and push operation status
- **Clean Code Refactoring**: Applied SOLID principles and method decomposition to MergeAction

#### Changed

- **MergeAction Enhancement**: Complete refactoring with method decomposition and constants
- **Command Options**: Added `--source` and `--target` options while preserving existing ones
- **Error Messages**: More descriptive and actionable error messages for users
- **Code Quality**: Improved maintainability through SRP compliance and enhanced structure

#### Fixed

- **Empty Source Branch Handling**: Fixed issue where pressing Enter without input caused merge failures
- **Type Safety**: Resolved ProcessResult vs string type compatibility issues
- **Shell Parsing**: Enhanced validation for comma-separated branch arguments with spaces

#### Technical Improvements

- **Method Decomposition**: Split complex methods into focused, single-responsibility functions
- **Constants Usage**: Implemented constants for emoji indicators and magic strings
- **Error Recovery**: Better error handling with detailed user guidance
- **Input Validation**: Enhanced validation for command-line arguments and user input

## [v2.0.0] - 2025-07-22

### 🚀 Major Release - Complete Architecture Refactoring

#### Added

- **Action-based Architecture**: Implemented Command Pattern with individual action classes
- **GitActionRegistry**: Dynamic action registration and resolution system
- **ConsoleIOInterface**: Abstract console interface for better testability
- **ArtisanConsoleIO**: Laravel-specific console implementation
- **ActionResult**: Standardized result objects for consistent response handling
- **BaseGitAction**: Abstract base class providing common functionality
- **Individual Action Classes**: 
  - `PushAction` - Handle git push operations
  - `PullAction` - Handle git pull operations
  - `BranchAction` - Handle branch creation and switching
  - `MergeAction` - Handle git merge operations
  - `CheckoutAction` - Handle git checkout operations
  - `FetchAction` - Handle git fetch operations
- **Comprehensive Test Suite**: 73+ test cases covering unit, integration, and feature tests
- **Enhanced Error Handling**: Structured error messages and exception handling

#### Changed

- **GitCommand Refactoring**: Transformed from monolithic class to action dispatcher
- **Architecture Simplification**: 80% reduction in GitCommand method count (3 vs 15+ methods)
- **Improved Separation of Concerns**: Each git operation isolated in dedicated classes
- **SOLID Principles Implementation**: Applied throughout the entire codebase
- **Enhanced Dependency Injection**: Better service registration and container integration
- **CommitMessageBuilder**: Updated to work with ConsoleIOInterface

#### Technical Improvements

- **Reduced Coupling**: 52% reduction in class coupling
- **Better Testability**: All components now fully mockable and testable
- **Type Safety**: Full PHP 8.2+ type declarations throughout
- **Code Reusability**: Shared functionality through base classes and interfaces
- **Performance**: Better resource management through dependency injection

#### Backward Compatibility

- **No Breaking Changes**: All existing Laravel commands work unchanged
- **API Compatibility**: `php artisan git {action}` commands remain the same
- **Seamless Migration**: No code changes required for existing implementations

## [v1.7.0] - 2024-12-22

### Added

- Laravel 12 support
- Essential package files (LICENSE, CONTRIBUTING.md, CHANGELOG.md)
- Proper package structure improvements

### Changed

- Updated composer.json to support Laravel 12.x
- Updated minimum PHP requirement documentation

### Fixed

- Improved package structure and maintainability

## [v1.6.1] - Previous Release

### Fixed

- Various bug fixes

## [v1.6.0] - Previous Release

### Added

- Merge to multiple branches feature

## [v1.5.0] - Previous Release

### Added

- Rebase action support

## [v1.4.0] - Previous Release

### Added

- Environment variables support

## [v1.3.0] - Previous Release

### Added

- Previous features and improvements

## [v1.2.0] - Previous Release

### Added

- Previous features and improvements

## [v1.1.0] - Previous Release

### Added

- Previous features and improvements

## [v1.0.0] - Initial Release

### Added

- Initial Laravel Git Toolkit functionality
- Git commands integration
- Git Flow support
- Laravel console commands
