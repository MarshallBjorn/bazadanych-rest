@echo off
REM Define the directory to delete
set TARGET_DIR=containers-data

REM Check if the directory exists
if exist "%TARGET_DIR%" (
    echo Deleting directory: %TARGET_DIR%
    rmdir /s /q "%TARGET_DIR%"
) else (
    echo Directory not found: %TARGET_DIR%
)

REM Start docker-compose up
echo Starting docker-compose up...
docker compose up

REM Exit script immediately aftero docker compose stops
exit /b