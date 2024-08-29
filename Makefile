PHP_VERSION ?= 8.3.10
DISTRO ?= debian

.DEFAULT_GOAL : help

help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
all: build-image build test ## Build image, build extension, run tests
build-image: ## Build docker image
	PHP_VERSION=$(PHP_VERSION) docker compose build $(DISTRO)
shell: ## Shell
	docker compose run $(DISTRO) bash
format: ## Run clang-format (debian-only)
	docker compose run debian ./format.sh
clean: ## Clean
	docker compose run $(DISTRO) make clean
git-clean: ## git-clean
	git clean -Xf
build: ## Build extension
	docker compose run $(DISTRO) ./build.sh
test: ## Run tests
	docker compose run $(DISTRO) make test
remove-orphans: ## Remove orphaned containers
	docker compose down --remove-orphans
.PHONY: clean build test git-clean
