# IoT Temperature Monitoring System

Laravel REST API for IoT temperature sensors.

## Setup

**Configure `.env`:**
add mysql credentials to .env file example:
```env
DB_DATABASE=lab
DB_USERNAME=lab
DB_PASSWORD=lab123
```

**Start:** need to be run from root directory
```bash
docker-compose up -d
```

**Stop:**
```bash
docker-compose down
```

## API Documentation

Import `postman_collection.json` into Postman.

**Notes:**
- After creating a user, the token is automatically saved to the `pat` variable
- To send measurements: use `Authorization: Bearer {{pat}}` (user) or `X-Device-Token` header (IoT device)

## Quick Examples

**Create User:**
```bash
curl -X POST http://localhost:8080/api/v1/users \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com"}'
```

**Create Device:**
```bash
curl -X POST http://localhost:8080/api/v1/devices \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name": "Sensor 1", "serial": "SN12345"}'
```

**Send Measurement (with Device Token):**
```bash
curl -X POST http://localhost:8080/api/v1/devices/1/measurements \
  -H "X-Device-Token: DEVICE_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"temperature_c": 25.5, "recorded_at": "2025-10-05T12:00:00Z"}'
```

**Send Measurement (with User Token):**
```bash
curl -X POST http://localhost:8080/api/v1/devices/1/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"temperature_c": -5.0, "recorded_at": "2025-10-05T12:00:00Z"}'
```

**Get My Measurements:**
```bash
curl -X GET http://localhost:8080/api/v1/me/measurements \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Get My Alerts:**
```bash
curl -X GET http://localhost:8080/api/v1/me/alerts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```
