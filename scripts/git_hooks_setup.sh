#!/bin/sh

green='\033[0;32m'
yellow='\033[0;33m'
no_color='\033[0m'

echo "${yellow}Copying git hooks to local '.git/hooks' folder...${no_color}"
cp hooks/post-checkout .git/hooks/post-checkout
cp hooks/post-merge .git/hooks/post-merge
cp hooks/pre-commit .git/hooks/pre-commit
cp hooks/pre-push .git/hooks/pre-push

echo "${yellow}Setting execute permissions on git hooks...${no_color}"
chmod +x .git/hooks/post-checkout
chmod +x .git/hooks/post-merge
chmod +x .git/hooks/pre-commit
chmod +x .git/hooks/pre-push

echo "${green}Git hooks are now all set, enjoy!${no_color}"
