# Engineering & Coding Standards

This document defines the strict architecture rules and API contracts for the Encuestas Electorales MVP.

## 1. Clean Architecture (MVC-Lite in PHP)
- **Views (Presentation):** Files like `distrito.php` only contain HTML and `echo` statements. No direct database queries here.
- **Controllers/API:** Files in `/api/` (e.g., `votar.php`) handle incoming requests, validate input, and return JSON.
- **Services:** Logic like GPS Triangulation must be decoupled into `/includes/` or `/services/`.
- **Data Access:** All MySQL `PDO` queries must use Prepared Statements.

## 2. API Contracts (Backend ↔ Frontend)

Frontend Vanilla JS and Backend PHP communicate strictly via JSON.

### Contract for `/api/votar.php`

**Request:**

```json
{
  "candidato_id": "uuid-1234",
  "distrito_id": "miraflores",
  "gps_lat": -12.12110000,
  "gps_lng": -77.02980000,
  "interaction_time_ms": 4500
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Voto registrado y encriptado correctamente."
}
```

## 3. Testing Strategy (QA)
- **Backend QA:** Every API endpoint must strictly validate inputs using PHP's `filter_var()`. Throw 400 Bad Request immediately if inputs are malformed.
- **Frontend QA:** All JS DOM interactions must fail gracefully (`if (!document.getElementById('map')) return;`) to prevent console errors.

## 4. Naming Conventions
- **MySQL:** `snake_case` (`trust_score`, `gps_lng`).
- **PHP:** `camelCase` for vars/functions, `PascalCase` for classes.
- **CSS/DOM IDs:** `kebab-case`.
