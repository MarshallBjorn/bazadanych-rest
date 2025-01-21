@echo off
REM Define the directory to delete
set TARGET_DIR=containers-data

REM Start docker-compose up
echo Starting docker-compose up...
docker compose up

REM Exit script immediately aftero docker compose stops
exit /b