#!/bin/bash

echo "🪚 Executing Deep Pruning of Sharpishly..."

# 1. Prune redundant storage/uploads branches
rm -rf php/storage
rm -rf android/storage
rm -rf android/uploads
rm -rf php/uploads
rm -rf uploads

# 2. Prune stray logs
rm -rf php/logs
rm -rf python/logs
rm -f website/app.log

# 3. Remove old Test configs
rm -f phpunit.xml

# 4. Remove conflicting view fragments in website/
# (Assuming your MVC engine uses /php/src/views)
rm -rf website/view

# 5. Clean up the 'mish-mash' of shells
# Move the ones we keep to a safe place, then purge the rest if you desire.
# For now, let's just remove the obviously old 'start.sh' and 'setup.sh'
rm -f shells/start.sh
rm -f shells/setup.sh

echo "✅ Pruning complete. Your tree is now aligned with the MVC architecture."
