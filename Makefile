# Free Watermarks Plugin Makefile

# Variables
PLUGIN_NAME = free-watermarks
BUILD_DIR = build
ZIP_FILE = $(PLUGIN_NAME).zip

.PHONY: help install update build clean

help:
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@echo "  install    Install all Composer dependencies (for development)."
	@echo "  update     Update all Composer dependencies."
	@echo "  build      Create a production-ready zip file for distribution."
	@echo "  clean      Remove all build artifacts."

install:
	composer install

update:
	composer update

build: clean
	@echo "--> Preparing build directory..."
	mkdir -p $(BUILD_DIR)
	@echo "--> Installing production dependencies in build directory..."
	cp -r assets src config composer.json free-watermarks.php $(BUILD_DIR)/
	cd $(BUILD_DIR) && composer install --no-dev --optimize-autoloader
	@echo "--> Creating production zip file: $(ZIP_FILE)..."
	cd $(BUILD_DIR) && zip -r ../$(ZIP_FILE) .
	@echo ""
	@echo "--> Build complete: $(ZIP_FILE) created."

clean:
	@echo "--> Cleaning build artifacts..."
	rm -rf $(BUILD_DIR)
	rm -f *.zip
