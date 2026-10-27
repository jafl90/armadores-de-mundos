# Armadores de Mundos 🦡

Sitio web multimedia y comunitario sobre el **armadillo gigante** (*Priodontes maximus*),
el "Armador de Mundos". Incluye un relato, audiolibros, video y un **muro colaborativo**
donde cualquier persona puede aportar su propia historia e ilustración.

Las historias se guardan de forma permanente en este repositorio de GitHub.

## ¿Cómo se guardan las historias? (arquitectura)

El sitio es estático (solo `index.html`) y **no contiene ningún token secreto**.
El flujo de publicación es seguro:

```
[Visitante en la web]
      │  rellena el formulario y pulsa "Publicar"
      ▼
[Se abre un GitHub Issue con la historia + imagen incrustada]
      │
      ▼
[GitHub Action: .github/workflows/process-story.yml]
      │  • extrae autor, texto e imagen
      │  • guarda la imagen en /images
      │  • añade la historia a data/stories.json
      │  • hace commit y cierra el Issue
      ▼
[El sitio lee data/stories.json y muestra la nueva historia]
```

Como el HTML nunca guarda credenciales, nadie puede abusar del repositorio.
La contrapartida es que una historia nueva tarda **1–2 minutos** en aparecer
(lo que tarda el Action en procesarla).

## Estructura del proyecto

| Archivo / carpeta | Propósito |
|---|---|
| `index.html` | La página web completa (lee y publica historias). |
| `data/stories.json` | Base de datos de historias. El Action lo actualiza. |
| `images/` | Imágenes subidas por la comunidad. |
| `.github/workflows/process-story.yml` | Action que procesa cada Issue nuevo. |
| `.github/scripts/process-story.js` | Lógica que actualiza el JSON y guarda la imagen. |
| `.github/ISSUE_TEMPLATE/nueva-historia.yml` | Plantilla del Issue. |

## Puesta en marcha

1. Sube estos archivos al repositorio `jafl90/armadores-de-mundos`.
2. En **Settings → Pages**, elige la rama `main` y carpeta `/ (root)`.
   El sitio quedará en `https://jafl90.github.io/armadores-de-mundos/`.
3. ¡Listo! El muro ya funciona.

## Archivos multimedia

El `index.html` referencia estos archivos locales (súbelos a la raíz del repo si los tienes):

- `Armadora de mundos.png` (logo / póster)
- `Armadores-de-mundo-Diana.mp3` (voz femenina)
- `Armadores-de-mundo-Arón.mp3` (voz masculina)
- `Armadora_de_mundos.mp4` (video)

Si no los subes, el sitio usa los enlaces de respaldo de Google Drive ya incluidos.

---

Copyleft 2026 · Conocimiento abierto para la conservación de la vida silvestre.
Inspirado en la labor de la [Fundación Kurupira](https://fundkurupira.org/).
