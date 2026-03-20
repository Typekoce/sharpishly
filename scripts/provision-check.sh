#!/bin/bash
# Compare .env against .env.example to find missing keys
MISSING=$(comm -23 <(cut -d= -f1 .env.example | sort) <(cut -d= -f1 .env | sort))

if [ -n "$MISSING" ]; then
  echo "❌ DEPLOYMENT BLOCKED: Missing keys in .env:"
  echo "$MISSING"
  exit 1
fi
echo "✅ Environment provisioned correctly."