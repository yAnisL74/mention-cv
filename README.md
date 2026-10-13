# Mention · analyse de CV par IA

Tu déposes ton CV (PDF ou texte), l'IA lui donne une note sur 20, détaille 5 critères, propose des corrections
ligne par ligne et réécrit le CV dans une mise en page propre, exportable en PDF.
Pensé pour les étudiants et les jeunes qui cherchent un stage, une alternance ou un premier emploi.

## Fonctionnalités

- Lecture du PDF dans le navigateur (pdf.js), ou texte collé
- Analyse optionnelle par rapport à une offre d'emploi
- Note globale, 5 critères notés, points forts, corrections prioritaires, exemples avant / après
- CV réécrit, 3 modèles et 5 couleurs, export PDF (html2pdf.js)
- API REST en PHP qui appelle l'API Claude d'Anthropic et enregistre des statistiques anonymes en MySQL

## Architecture

```
public/index.html      Front-end (HTML, CSS, JavaScript vanilla)
api/analyze.php        POST  : analyse un CV, renvoie le résultat en JSON
api/stats.php          GET   : statistiques anonymes (moyennes globales et par critère)
api/prompt.php         Construction du prompt côté serveur
api/anthropic.php      Client HTTP (cURL) vers l'API Messages d'Anthropic
api/bootstrap.php      Config, PDO, réponses JSON, helpers
schema.sql             Schéma MySQL
```

## API

`POST /api/analyze.php`

```json
{ "cv": "Texte du CV…", "offer": "Texte de l'offre (optionnel)" }
```

Réponse `200` : `{ "id": 42, "result": { "note": 11, "note_apres": 16, "criteres": [...], "cv": {...} } }`

Erreurs : `422 cv_too_short`, `429 rate_limited_api`, `502 ai_unavailable`, `405 method_not_allowed`.

`GET /api/stats.php` → `{ "total": 128, "note_moyenne": 10.9, "note_apres_moyenne": 15.2, "gain_moyen": 4.3, "criteres": [...] }`

## Choix techniques

- **Prompt construit côté serveur** : le client n'envoie que le CV et l'offre, il ne peut pas détourner le modèle.
- **Aucune donnée personnelle stockée** : seules les notes sont enregistrées, jamais le contenu du CV.
- **Limitation de débit** par empreinte d'IP hachée (SHA-256, renouvelée chaque jour).
- **Requêtes préparées PDO** partout, clé API hors du dépôt (`config.php` ignoré par Git).

## Installation

Prérequis : PHP 8.1+ (extensions curl, mbstring, pdo_mysql), MySQL 8.

```bash
mysql -u root -p < schema.sql
cp api/config.example.php api/config.php   # puis renseigner la clé API et la base
php -S localhost:8000                       # ouvrir http://localhost:8000/public/
```
