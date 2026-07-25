/**
 * Forum Numérique Congo — extraction du jeu de données canonique vers dataset.json.
 *
 * Lit les sources de vérité (module d'agenda typé + fiches Markdown) et produit
 * un unique `dataset.json` consommé par tools/seed-dataset.php. Séparer
 * l'extraction (dev) du semis (WordPress) garde le semis autonome et rejouable.
 *
 * Exécution :  npx tsx tools/build-dataset.mjs
 *   Variable FNC_SRC : racine des données canoniques
 *   (défaut : C:/projets_dev/forum-numerique-congo).
 *
 * @author    Vanel NGOYO ADOUMA, Lead développeur — Grinso & Associés
 * @copyright © 2026 Grinso & Associés (https://www.grinso.io) — Tous droits réservés.
 */
import fs from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const SRC = process.env.FNC_SRC || 'C:/projets_dev/forum-numerique-congo';
const OUT = path.join(path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1')), 'dataset.json');

// Ordre protocolaire (repris de src/data/agenda-2027.ts, non exporté).
const PRIORITY_ORDER = ['makosso', 'bouya-jj', 'nze', 'ngouonimba', 'yoka', 'ngatse', 'djombo', 'bahamboula', 'bouya-er'];

/** Parseur minimal de frontmatter YAML (clés scalaires « key: "value" »). */
function frontmatter(file) {
  const txt = fs.readFileSync(file, 'utf8');
  const m = txt.match(/^---\r?\n([\s\S]*?)\r?\n---/);
  const out = {};
  if (!m) return out;
  for (const line of m[1].split(/\r?\n/)) {
    const mm = line.match(/^([a-zA-Z0-9_]+):\s*(.*)$/);
    if (!mm) continue;
    let v = mm[2].trim();
    if (v === '' || v === '[]') { out[mm[1]] = ''; continue; }
    v = v.replace(/^"(.*)"$/, '$1').replace(/^'(.*)'$/, '$1');
    out[mm[1]] = v;
  }
  return out;
}

function mdFiles(dir) {
  const d = path.join(SRC, dir);
  return fs.existsSync(d) ? fs.readdirSync(d).filter((f) => f.endsWith('.md')) : [];
}

const statusMap = { passee: 'past', a_venir: 'upcoming', en_cours: 'current' };

async function main() {
  const agenda = await import(pathToFileURL(path.join(SRC, 'src/data/agenda-2027.ts')).href);

  // --- Éditions (frontmatter des 5 fiches) ---
  const editions = [];
  for (const f of mdFiles('docs/sources/content-migration/02-editions').filter((f) => /^\d{4}-/.test(f))) {
    const fm = frontmatter(path.join(SRC, 'docs/sources/content-migration/02-editions', f));
    const slug = f.replace(/\.md$/, '');
    const year = fm.year || slug.slice(0, 4);
    const is2027 = year === '2027';
    editions.push({
      legacyId: slug,
      slug,
      year,
      title: `Édition ${year}`,
      theme: fm.theme || '',
      themeEn: is2027 ? agenda.edition.themeEn : '',
      // L'édition active pilote l'accueil : statut « current » (le résolveur la
      // choisit en priorité), même si l'évènement est à venir (compte à rebours).
      status: is2027 ? 'current' : ( statusMap[fm.status] || 'past' ),
      active: is2027 ? 1 : 0,
      startDate: fm.start_date || '',
      endDate: fm.end_date || '',
      location: is2027 ? agenda.edition.venue : '',
    });
  }

  // --- Intervenants (agenda-2027.ts) ---
  const speakers = agenda.speakers.map((s, i) => {
    const p = PRIORITY_ORDER.indexOf(s.id);
    return {
      legacyId: s.id,
      slug: s.slug || s.id,
      title: s.title || '',
      name: s.name,
      roleFr: s.roleFr || '',
      roleEn: s.roleEn || '',
      org: s.org || '',
      country: s.country || '',
      kind: s.kind,
      photo: s.photo || '',
      protocolOrder: p >= 0 ? p + 1 : 50 + i,
    };
  });

  // --- Sessions (agenda-2027.ts) ---
  const edition2027 = editions.find((e) => e.year === '2027');
  const sessions = agenda.sessions.map((s) => ({
    legacyId: s.id,
    slug: s.slug || s.id,
    editionLegacy: edition2027 ? edition2027.legacyId : '',
    day: s.day,
    jour: `Jour ${s.day}`,
    start: s.start || '',
    end: s.end || '',
    time: s.end ? `${s.start} – ${s.end}` : (s.start || ''),
    type: s.type,
    titleFr: s.titleFr || '',
    titleEn: s.titleEn || '',
    moderatorLegacy: s.moderatorId || '',
    speakerLegacyIds: Array.isArray(s.speakerIds) ? s.speakerIds : [],
    note: s.note || '',
  }));

  // --- Partenaires (frontmatter) ---
  const partners = [];
  for (const f of mdFiles('docs/sources/content-migration/05-partenaires')) {
    const fm = frontmatter(path.join(SRC, 'docs/sources/content-migration/05-partenaires', f));
    if (!fm.name) continue;
    partners.push({
      legacyId: fm.slug || f.replace(/\.md$/, ''),
      name: fm.name,
      type: fm.partner_type || 'soutien',
      website: fm.website_url || '',
      description: fm.description || '',
    });
  }

  // --- Publications (frontmatter) ---
  const publications = [];
  for (const f of mdFiles('docs/sources/content-migration/06-ressources')) {
    const fm = frontmatter(path.join(SRC, 'docs/sources/content-migration/06-ressources', f));
    if (!fm.title) continue;
    publications.push({
      legacyId: fm.slug || f.replace(/\.md$/, ''),
      title: fm.title,
      type: fm.resource_type || 'document',
      editionLegacy: fm.edition_slug || '',
      date: fm.publication_date || '',
      url: fm.external_url || fm.file_source_url || '',
      description: fm.description || '',
    });
  }

  const data = { generatedAt: new Date().toISOString(), editions, speakers, sessions, partners, publications };
  fs.writeFileSync(OUT, JSON.stringify(data, null, 2), 'utf8');
  console.log(`dataset.json écrit : ${OUT}`);
  console.log(`  éditions=${editions.length} intervenants=${speakers.length} sessions=${sessions.length} partenaires=${partners.length} publications=${publications.length}`);
}

main().catch((e) => { console.error(e); process.exit(1); });
