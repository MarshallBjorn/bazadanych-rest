#!/bin/bash

# Define the directory whose contents to delete
TARGET_DIR="containers-data"

# Check if the directory exists
if [ -d "$TARGET_DIR" ]; then
    echo "Deleting contents of directory: $TARGET_DIR"
    rm -rf "$TARGET_DIR"/* # Delete all files and directories inside
else
    echo "Directory not found: $TARGET_DIR"
fi

# Start docker-compose up and exit immediately after stopping it
echo "Starting docker-compose up..."
docker compose up

# Exit script immediately after docker-compose stops
exit 0
