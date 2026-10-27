#!/usr/bin/env node
/**
 * process-story.js
 * -----------------------------------------------------------------------------
 * Procesa un GitHub Issue etiquetado como "nueva-historia" y:
 *   1. Extrae autor, narración e imagen (dataURL incrustada) del cuerpo.
 *   2. Si hay imagen, la decodifica y la guarda en /images.
 *   3. Añade la historia al inicio de data/stories.json.
 *
 * Se ejecuta dentro de un GitHub Action. Recibe el cuerpo y número del Issue
 * por variables de entorno:
 *   ISSUE_BODY    -> cuerpo markdown del Issue
 *   ISSUE_NUMBER  -> número del Issue (para id único y nombre de imagen)
 *   ISSUE_AUTHOR  -> login de quien abrió el Issue (metadato opcional)
 *
 * No usa dependencias externas: solo Node.js estándar.
 */

const fs = require("fs");
const path = require("path");

const ROOT = process.cwd();
const STORIES_PATH = path.join(ROOT, "data", "stories.json");
const IMAGES_DIR = path.join(ROOT, "images");

const ISSUE_BODY = process.env.ISSUE_BODY || "";
const ISSUE_NUMBER = process.env.ISSUE_NUMBER || String(Date.now());

/**
 * Extrae el contenido que sigue a un encabezado "### <nombre>" hasta el
 * siguiente "### " o el final del texto.
 */
function extractSection(body, heading) {
  const re = new RegExp(
    "###\\s*" + heading + "\\s*\\n([\\s\\S]*?)(?=\\n###\\s|$)",
    "i"
  );
  const m = body.match(re);
  return m ? m[1].trim() : "";
}

/** Extrae el bloque ```image\n<nombre>\n<dataURL>\n``` si existe. */
function extractImageBlock(body) {
  const re = /```image\s*\n([^\n]*)\n([\s\S]*?)```/;
  const m = body.match(re);
  if (!m) return null;
  return { name: m[1].trim(), dataUrl: m[2].trim() };
}

/** Convierte un dataURL (data:image/png;base64,....) en {buffer, ext}. */
function decodeDataUrl(dataUrl) {
  const m = dataUrl.match(/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.*)$/s);
  if (!m) return null;
  const mime = m[1];
  const base64 = m[2].replace(/\s+/g, "");
  const extMap = {
    "image/png": "png",
    "image/jpeg": "jpg",
    "image/jpg": "jpg",
    "image/gif": "gif",
    "image/webp": "webp",
    "image/svg+xml": "svg"
  };
  const ext = extMap[mime] || "png";
  return { buffer: Buffer.from(base64, "base64"), ext };
}

function slugify(str) {
  return String(str)
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .substring(0, 40) || "anonimo";
}

function spanishDate() {
  const now = new Date();
  const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
  ];
  return `${now.getDate()} de ${meses[now.getMonth()]}, ${now.getFullYear()}`;
}

function main() {
  const author = extractSection(ISSUE_BODY, "Autor") || "Anónimo";
  const text = extractSection(ISSUE_BODY, "Narración");

  if (!text) {
    console.error("El Issue no contiene una narración válida. Abortando.");
    process.exit(1);
  }

  // Procesar imagen si viene incrustada.
  let imageField = "";
  const imgBlock = extractImageBlock(ISSUE_BODY);
  if (imgBlock && imgBlock.dataUrl) {
    const decoded = decodeDataUrl(imgBlock.dataUrl);
    if (decoded) {
      if (!fs.existsSync(IMAGES_DIR)) fs.mkdirSync(IMAGES_DIR, { recursive: true });
      const fileName = `historia-${ISSUE_NUMBER}-${slugify(author)}.${decoded.ext}`;
      fs.writeFileSync(path.join(IMAGES_DIR, fileName), decoded.buffer);
      imageField = `images/${fileName}`;
      console.log("Imagen guardada:", imageField, `(${decoded.buffer.length} bytes)`);
    } else {
      console.warn("No se pudo decodificar la imagen; se publicará sin ella.");
    }
  }

  // Cargar stories.json existente.
  let stories = [];
  if (fs.existsSync(STORIES_PATH)) {
    try {
      stories = JSON.parse(fs.readFileSync(STORIES_PATH, "utf8"));
      if (!Array.isArray(stories)) stories = [];
    } catch (e) {
      console.warn("stories.json inválido, se reinicia:", e.message);
      stories = [];
    }
  }

  const newStory = {
    id: "story-" + ISSUE_NUMBER,
    author,
    date: spanishDate(),
    image: imageField,
    text
  };

  // Evitar duplicados por id (si el Action se reejecuta).
  stories = stories.filter((s) => s.id !== newStory.id);
  stories.unshift(newStory);

  fs.writeFileSync(STORIES_PATH, JSON.stringify(stories, null, 2) + "\n", "utf8");
  console.log(`Historia añadida de "${author}". Total: ${stories.length}.`);
}

main();
