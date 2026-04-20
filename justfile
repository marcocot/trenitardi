set shell := ["bash", "-e", "-u", "-o", "pipefail", "-c"]

registry  := env("REGISTRY", "registry.homelab.devncode.it")
image     := env("IMAGE", "trenitardi-web")
tag       := env("TAG", "latest")
platforms := env("PLATFORMS", "linux/amd64,linux/arm64")
builder   := "trenitardi-builder"

help:
	@echo "# Dev"
	@echo "just install            # composer install + npm ci"
	@echo "just dev                # composer run dev (server + vite + logs)"
	@echo ""
	@echo "# CI"
	@echo "just lint               # pint --test"
	@echo "just lint-fix           # pint (fix)"
	@echo "just test               # artisan test --compact"
	@echo "just ci                 # lint + test"
	@echo ""
	@echo "# Docker (registry={{registry}}, image={{image}})"
	@echo "just login              # docker login (REGISTRY_USERNAME / REGISTRY_PASSWORD)"
	@echo "just build              # build locale single-arch"
	@echo "just push TAG=x.y.z     # buildx multi-arch push"
	@echo ""
	@echo "# Release"
	@echo "just release v1.2.3     # clean-check -> pull -> ci -> tag -> push -> gh release"

# --- Dev ---

install:
	composer install
	npm ci

dev:
	composer run dev

# --- Lint / Test / CI ---

lint:
	composer lint:check

lint-fix:
	composer lint

assets:
	npm run build

test: assets
	php artisan test --compact

ci: lint test

# --- Docker ---

login:
	@echo "$REGISTRY_PASSWORD" | docker login {{registry}} -u "${REGISTRY_USERNAME:?set REGISTRY_USERNAME}" --password-stdin

buildx-setup:
	docker buildx inspect {{builder}} >/dev/null 2>&1 || docker buildx create --name {{builder}} --driver docker-container --use
	docker buildx use {{builder}}
	docker buildx inspect --bootstrap

build:
	docker build -t {{registry}}/{{image}}:{{tag}} .

push: buildx-setup
	docker buildx build --platform {{platforms}} -t {{registry}}/{{image}}:{{tag}} --push .

# --- Release ---

release version:
	@if [ -n "$(git status --porcelain)" ]; then echo "Working tree non pulito. Commit o stash prima di procedere."; exit 1; fi
	git checkout master
	git pull --ff-only origin master
	just ci
	@if git rev-parse "{{version}}" >/dev/null 2>&1; then echo "Il tag {{version}} esiste già."; exit 1; fi
	git tag -a "{{version}}" -m "Release {{version}}"
	git push origin master
	git push origin "{{version}}"
	gh release create "{{version}}" --generate-notes --title "{{version}}"
