# Armadores de Mundos 🦡

Sitio web multimedia y comunitario sobre el **armadillo gigante** (*Priodontes maximus*),
el "Armador de Mundos". Incluye un relato, audiolibros, video y un **muro colaborativo abierto**
donde cualquier visitante puede aportar su propia historia e ilustración.

Pensado para alojarse en el **host propio (Namecheap / cPanel)** de la Fundación Kurupira,
en la subcarpeta: **https://fundkurupira.org/armadores/**

---

## ¿Cómo funciona? (arquitectura)

El sitio corre en tu propio host con **PHP** (no necesita base de datos ni configuración especial):

```
[Visitante en la web]
      │  rellena el formulario y pulsa "Publicar historia"
      ▼
[guardar.php]  ← guarda la imagen en /imagenes y añade la historia a data/stories.json
      │
      ▼
[El muro se actualiza al INSTANTE]  ← muro abierto, sin moderación
```

Para mostrar el muro, el sitio pide las historias a **listar.php**, que entrega `data/stories.json`.

**Ventajas de esta solución:**
- ✅ El video y todos los archivos pesados viven en tu host, sin límites de GitHub/YouTube
- ✅ Las historias se guardan **al instante**
- ✅ Los visitantes **NO necesitan ninguna cuenta** para publicar
- ✅ Todo bajo tu control, sin depender de terceros
- ✅ Sin tokens ni secretos expuestos

---

## 📂 Archivos del proyecto

| Archivo / carpeta | Propósito |
|---|---|
| `index.html` | La página web completa. |
| `guardar.php` | Recibe el formulario y guarda la historia + imagen. |
| `listar.php` | Entrega las historias al muro. |
| `data/stories.json` | Base de datos de historias (se crea/actualiza solo). |
| `data/.htaccess` | Impide el acceso directo al JSON desde el navegador. |
| `imagenes/` | Imágenes subidas por la comunidad (se guardan aquí). |
| `imagenes/.htaccess` | Seguridad: impide ejecutar código dentro de esta carpeta. |

### Archivos multimedia (súbelos tú a la misma carpeta)

El `index.html` referencia estos archivos por nombre. Súbelos junto al resto:

- `Armadora de mundos.png` (logo / póster del video)
- `Armadores-de-mundo-Diana.mp3` (voz femenina)
- `Armadores-de-mundo-Arón.mp3` (voz masculina)
- `Armadora_de_mundos.mp4` (video) ← **ahora sí puedes subirlo, sin el límite de GitHub**

Si no subes alguno, el sitio usa los enlaces de respaldo de Google Drive ya incluidos.

---

## 🚀 Instalación en cPanel (Namecheap) — paso a paso

1. **Entra a cPanel** de tu cuenta de Namecheap y abre el **Administrador de archivos** (File Manager).
2. Ve a la carpeta **`public_html`** (es la raíz de tu sitio web).
3. **Crea una carpeta nueva** llamada **`armadores`** y entra en ella.
4. **Sube estos archivos y carpetas** dentro de `armadores/`:
   - `index.html`
   - `guardar.php`
   - `listar.php`
   - la carpeta `data/` (con su `.htaccess` dentro)
   - la carpeta `imagenes/` (con su `.htaccess` dentro)
   - tus archivos multimedia (`.mp4`, `.mp3`, `.png`)

   > 💡 Truco: puedes subir todo comprimido en un `.zip` y luego usar "Extraer" en cPanel. Es más rápido.

5. **Permisos de carpetas** (para que PHP pueda escribir):
   - Clic derecho sobre `data/` → **Permissions** → ponle **755** (o 775 si 755 no funciona).
   - Haz lo mismo con `imagenes/`.
   - *(En la mayoría de hostings de Namecheap, 755 es suficiente.)*

6. **Verifica la versión de PHP** (opcional):
   - En cPanel → **Select PHP Version** → asegúrate de que sea **7.4 u 8.x**.
   - La extensión **`fileinfo`** debe estar activada (viene activada por defecto).

7. **¡Listo!** Visita **https://fundkurupira.org/armadores/** y prueba a publicar una historia.

---

## ✅ Cómo comprobar que funciona

- Abre `https://fundkurupira.org/armadores/listar.php` en el navegador:
  deberías ver las historias en formato JSON (texto). Si lo ves, el backend funciona.
- Publica una historia de prueba desde el formulario: debe aparecer en el muro al instante.
- Revisa que en `imagenes/` aparezca la foto que subiste.

### Si algo falla
- **"No se pudo publicar"** → revisa los permisos de `data/` e `imagenes/` (ponlos en 775).
- **El muro sale vacío** → abre `listar.php` directamente; si da error 500, revisa la versión de PHP.
- **La imagen no se guarda** → confirma que `imagenes/` tiene permisos de escritura.

---

Copyleft 2026 · Conocimiento abierto para la conservación de la vida silvestre.
Inspirado en la labor de la [Fundación Kurupira](https://fundkurupira.org/).
