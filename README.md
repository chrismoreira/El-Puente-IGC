# El Puente — Iglesia Gran Comisión

Herramienta evangelística digital basada en la presentación del Evangelio "El Puente". Diseñada para ser usada en conversaciones uno a uno, mostrando el diagrama de manera progresiva.

---

## Instalación como app (PWA)

La aplicación funciona directamente desde el navegador y puede instalarse en cualquier dispositivo sin necesidad de una tienda de apps.

### iPhone / iPad (Safari)

1. Abre **Safari** y visita `https://puente.igcsansalvador.com`
2. Toca el botón de compartir (el ícono de caja con flecha hacia arriba) en la barra inferior
3. Selecciona **"Añadir a pantalla de inicio"**
4. Toca **Añadir** en la esquina superior derecha
5. La app aparecerá en tu pantalla de inicio como cualquier otra aplicación

### Android (Chrome)

1. Abre **Chrome** y visita `https://puente.igcsansalvador.com`
2. Toca el menú (los tres puntos en la esquina superior derecha)
3. Selecciona **"Añadir a pantalla de inicio"** o **"Instalar app"**
4. Toca **Instalar**
5. La app aparecerá en tu pantalla de inicio

### Computadora (Chrome / Edge)

1. Visita `https://puente.igcsansalvador.com`
2. En la barra de direcciones aparecerá un ícono de instalación (⊕) a la derecha
3. Haz clic en él y selecciona **"Instalar"**

---

## Cómo usar la aplicación

- **Desliza hacia la izquierda** para avanzar al siguiente paso del diagrama
- **Toca el símbolo `+`** (círculo negro) para ver el versículo bíblico correspondiente
- El diagrama se va construyendo progresivamente en una sola pantalla
- Al final hay una oración de salvación y un versículo de confirmación

---

## Archivos del proyecto

| Archivo | Descripción |
|---------|-------------|
| `index.html` | Aplicación principal (archivo único) |
| `manifest.json` | Configuración PWA para instalación |
| `sw.js` | Service worker para funcionamiento sin conexión |
| `icon.svg` | Ícono vectorial de la app |
| `icon-192.png` | Ícono 192×192 para Android |
| `icon-512.png` | Ícono 512×512 para pantallas de alta resolución |

---

## Despliegue en servidor propio

Solo se necesita un servidor web estático. Sube todos los archivos a la raíz del dominio:

```
index.html
manifest.json
sw.js
icon.svg
icon-192.png
icon-512.png
```

Requisitos:
- Servidor con **HTTPS** (requerido para PWA e instalación)
- No se necesita base de datos ni backend

---

## Tecnología

- HTML, CSS y JavaScript puro — sin frameworks ni dependencias externas
- PWA con soporte offline mediante Service Worker
- Responsive: funciona en móvil, tablet y computadora
- Fuente: [Raleway](https://fonts.google.com/specimen/Raleway) (Google Fonts)

---

Iglesia Gran Comisión · El Salvador
