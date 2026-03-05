#!/bin/bash

# 1. Get the current branch name
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

# 2. Add and Commit local changes
echo "Step 1: Committing changes on $CURRENT_BRANCH..."
git add .
read -p "Enter commit message: " msg
git commit -m "$msg"

# 3. Push feature branch to origin
echo "Step 2: Pushing $CURRENT_BRANCH to remote..."
git push origin $CURRENT_BRANCH

# 4. Switch to main and sync with remote
echo "Step 3: Updating local main..."
git checkout main
git pull origin main

# 5. Merge the feature branch into main
echo "Step 4: Merging $CURRENT_BRANCH into main..."
git merge $CURRENT_BRANCH --no-edit

# 6. Push the updated main to remote
echo "Step 5: Pushing main to remote..."
git push origin main

# 7. Return to the feature branch
echo "Step 6: Returning to $CURRENT_BRANCH..."
git checkout $CURRENT_BRANCH

echo "Done! Main and $CURRENT_BRANCH are now synchronized."