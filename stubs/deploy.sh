#!/bin/bash

git pull origin main

echo "🪄 Deploying Application..."

docker build -t application-name .

docker compose up -d --build

echo "🚀 Application Deployed!"
