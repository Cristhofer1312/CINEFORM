---
name: security-and-profile-manager
description: Activar SIEMPRE que se creen, modifiquen o auditen sistemas de perfiles de usuario, autenticación (AuthN), autorización/roles (RBAC/ABAC), manejo de sesiones, políticas de contraseñas, tokens (JWT/OAuth) o datos personales sensibles (PII).
allowed-tools: Read, Grep, Glob, Edit
---

# Skill: Arquitectura de Seguridad y Manejo de Perfiles (IAM)

## 1. Reglas Estrictas de Entorno (Zero-Trust del Agente)
- **Prohibición de lectura de secretos:** NUNCA leas ni imprimas el contenido de `.env`, `~/.ssh`, `~/.aws` ni archivos de certificados privados. Si necesitas variables de entorno, lee únicamente `.env.example`.
- **No hardcodear credenciales:** Rechaza cualquier intento de escribir API keys, secrets o tokens directamente en el código.

## 2. Seguridad en el Manejo de Perfiles (Anti-IDOR y Mass Assignment)
- **Prevención de IDOR / BOLA (Broken Object Level Authorization):**
  - En cada endpoint o controlador que consulte, edite o elimine un perfil (`GET/PUT/DELETE /users/{id}` o `/profile`), verifica explícitamente que el `user_id` de la sesión activa coincida con el recurso solicitado o que posea un rol administrativo validado en el servidor.
  - En frameworks con ORM (ej. Eloquent en Laravel, Prisma, TypeORM), usa Policies, Guards o Scopes ligados al usuario autenticado (`auth()->user()`), nunca confíes en IDs enviados por el cliente en el body o query string.
- **Protección contra Mass Assignment:**
  - Al actualizar un perfil, define estrictamente los campos permitidos (ej. `name`, `avatar`, `bio`).
  - Bloquea explícitamente la inyección de campos de privilegio o estado en el mismo payload (`role`, `is_admin`, `permissions`, `email_verified_at`, `balance`).

## 3. Autenticación, Sesiones y Credenciales
- **Hashing:** Usa exclusivamente `Argon2id` o `bcrypt` (cost >= 12) para contraseñas.
- **Ciclo de vida de sesión:**
  - Regenera el ID de sesión o rota el Refresh Token inmediatamente después de un login exitoso, cambio de contraseña o elevación de privilegios.
  - Al cambiar el correo electrónico o contraseña de un perfil, exige re-autenticación (contraseña actual o MFA) e invalida las demás sesiones activas.
- **Tokens y Cookies:** Configura cookies de sesión con las banderas `HttpOnly`, `Secure` y `SameSite=Lax` (o `Strict`).

## 4. Privacidad y Sanitización de Salida (PII)
- **Filtrado en respuestas API (DTOs / Resources):** Nunca devuelvas el objeto de base de datos crudo del usuario. Oculta siempre `password_hash`, `remember_token`, `two_factor_secret` y metadatos internos.
- **Logs limpios:** Asegúrate de que los middlewares de logging enmascaren contraseñas, tokens, números de documento y tarjetas antes de escribir en disco o servicios externos.

## 5. Checklist de Auditoría antes de confirmar cambios
Antes de dar por terminada cualquier tarea relacionada con usuarios o perfiles, verifica:
1. ¿Puede el Usuario A ver o modificar el perfil del Usuario B cambiando el ID en la URL/JSON? (Test BOLA/IDOR).
2. ¿Puede un usuario normal enviarse `"role": "admin"` en el endpoint de `updateProfile` y escalar privilegios?
3. ¿Se están exponiendo datos sensibles del perfil en la respuesta JSON o en los logs de error?
