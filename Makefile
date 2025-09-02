# AquaMark Plugin Makefile

# Variables
PLUGIN_NAME = aquamark
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
	mkdir -p $(BUILD_DIR)/$(PLUGIN_NAME)
	@echo "--> Copying plugin files..."
	cp -r assets src config aquamark.php readme.txt license.txt DEVELOPER.md CHANGELOG.md $(BUILD_DIR)/$(PLUGIN_NAME)/
	# Copy composer.json to the build directory to allow composer install
	cp composer.json $(BUILD_DIR)/$(PLUGIN_NAME)/
	@echo "--> Allowing Jetpack Autoloader plugin..."
	cd $(BUILD_DIR)/$(PLUGIN_NAME) && composer config --no-plugins allow-plugins.automattic/jetpack-autoloader true
	@echo "--> Adding Jetpack Autoloader for production..."
		cd $(BUILD_DIR)/$(PLUGIN_NAME) && composer require automattic/jetpack-autoloader:"^5.0" --no-interaction
	@echo "--> Ensuring no dev dependencies are present after adding Jetpack Autoloader..."
	cd $(BUILD_DIR)/$(PLUGIN_NAME) && composer update --no-dev --no-interaction --optimize-autoloader
	@echo "--> Creating production zip file: $(ZIP_FILE)..."
	rm $(BUILD_DIR)/$(PLUGIN_NAME)/composer.lock $(BUILD_DIR)/$(PLUGIN_NAME)/assets/*.jpg
	cd $(BUILD_DIR) && zip -r ../$(ZIP_FILE) $(PLUGIN_NAME)
	@echo ""
	@echo "--> Build complete: $(ZIP_FILE) created."

clean:
	@echo "--> Cleaning build artifacts..."
	rm -rf $(BUILD_DIR)
	rm -f *.zip
