# Automatizacion Envato Elements

## 1) Instalar dependencias

```bash
npm install
npx playwright install chromium
```

## 2) Guardar sesion de Envato (una sola vez)

```bash
npm run envato:login
```

Se abre un navegador visible, inicias sesion, vuelves a la terminal y presionas Enter.

## 3) Flujo normal

1. En Filament > Envato Elements pegas el link y haces clic en "Ejecutar".
2. Se crea un registro en `descargas` con estado `pendiente`.
3. Un Job de Laravel lo procesa con Playwright:
   - `procesando`
   - `completado` o `error`

## 4) Ejecutar worker de cola

```bash
php artisan queue:work
```
