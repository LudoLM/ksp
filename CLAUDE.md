# CLAUDE.md

Ce fichier donne à Claude Code le contexte nécessaire pour travailler efficacement sur ce projet. Il est lu au début de chaque session.

## Vue d'ensemble du projet

**KSS** — Plateforme de réservation et de paiement en ligne pour un centre de cours de bien-être (Pilates, stretching, gym douce).

- Espace utilisateur : inscription/désinscription aux cours, paiement Stripe (packs de cours), liste d'attente avec notification email, historique des paiements (PDF)
- Espace admin : gestion des cours/horaires, des utilisateurs et inscriptions, dashboard/stats, gestion des types de cours

**Projet en production.** Des vrais utilisateurs, des vrais paiements Stripe, des vraies données personnelles transitent dessus — être particulièrement prudent sur tout ce qui touche paiement, données perso et emails transactionnels.

## Règles absolues (non négociables)

- **NE JAMAIS committer, push, ou créer de branche sans mon accord explicite.** Proposer le commit (message inclus) et attendre validation avant `git commit` ou `git push`.
- **NE JAMAIS lire, afficher ou modifier les fichiers `.env`, `.env.local`, `.env.*.local`**, ni aucun fichier contenant des secrets (clés Stripe, clés JWT, credentials Mailjet/Sentry/MySQL/Redis).
- **NE JAMAIS exécuter de commandes destructrices** (`git reset --hard`, `rm -rf`, migrations qui suppriment des données, purge Redis/MySQL) sans confirmation explicite.
- **Zone sensible : paiements Stripe et webhooks.** Ne pas modifier la logique de paiement, de remboursement ou les handlers de webhook Stripe sans validation explicite et sans tests. Un bug ici a un impact financier réel.
- **Zone sensible : liste d'attente / notifications async (Messenger + Redis).** Toute modification doit être testée, y compris les cas d'échec/retry, avant d'être proposée.
- Ne pas modifier `composer.json` / `package.json` (ajout ou changement de dépendances) sans validation.
- Ne pas pousser directement sur une branche protégée déclenchant le déploiement (CI/CD GitHub Actions) sans accord.

## Stack technique

### Backend
- PHP 8.4 / Symfony 7.3
- Doctrine ORM
- Authentification JWT (rôles utilisateur / admin)
- Symfony Messenger (traitement asynchrone : emails, liste d'attente) + Redis
- Qualité : PHPStan, PHP-CS-Fixer, Rector

### Frontend
- Vue 3 (Composition API) + Vuetify 3
- TypeScript (migration en cours — du code peut encore être en `.js`, ne pas s'étonner)
- Pinia (state management)
- Tailwind CSS, ApexCharts (graphiques dashboard admin)

### Infrastructure & services tiers
- MySQL, Docker
- GitHub Actions — CI/CD (tests, analyse statique, lint) à chaque push
- Stripe (paiements)
- Mailjet (emails transactionnels)
- Sentry (monitoring d'erreurs) — si une erreur semble déjà trackée sur Sentry, le signaler plutôt que de "deviner" la cause

## Architecture

```
src/
├── Controller/       # 13 controllers REST fins, délèguent aux services (pas de logique métier ici)
├── Service/          # 33 services métier (réservation, paiement, liste d'attente...)
├── Entity/           # 11 entités Doctrine
├── DTO/              # Objets de transfert de données
├── MessageHandler/   # Handlers Messenger (traitement async)
└── EventSubscriber/  # Abonnés aux événements Symfony
```

Convention à respecter : les controllers restent fins, la logique métier vit dans `Service/`. Ne pas réintroduire de logique métier dans un controller lors d'une correction rapide.

## Commandes utiles

```bash
make test      # Tests PHPUnit
make phpstan   # Analyse statique PHPStan
make cs        # Formatage PHP-CS-Fixer
make rector    # Refactoring automatique Rector
make analyse   # cs + rector + phpstan + test en une commande

# Frontend (à compléter si différent)
npm install
npm run dev
npm run build
npm run lint
```

Avant de proposer qu'une tâche est terminée sur du code backend, lancer au minimum `make analyse` et rapporter le résultat réel (pas d'affirmation sans exécution).

## Conventions générales

- Style PSR-12 / conventions Symfony, respecter le style existant plutôt qu'imposer ses préférences
- Ne pas ajouter de commentaires/docstrings sur du code non modifié
- Privilégier la simplicité : pas de sur-ingénierer une fonctionnalité simple
- Pour tout nouveau code TypeScript côté frontend, écrire en TS (pas en JS) vu la migration en cours

## Workflow attendu

1. Avant toute modification non triviale, présenter un plan rapide et attendre validation — en particulier pour tout ce qui touche paiement, auth, ou liste d'attente.
2. Après une modification, lancer les tests/analyses concernés (`make test`, `make phpstan`...) et rapporter le résultat réel.
3. Ne jamais dire "les tests passent" ou "c'est corrigé" sans avoir exécuté la commande dans cette session.
4. Une fois le travail validé, proposer un message de commit clair — **et attendre mon accord avant de committer**.

## Ce qu'il ne faut PAS faire

- Ne pas committer ou push sans accord
- Ne pas lire les fichiers `.env*` ni aucun secret (Stripe, JWT, Mailjet, Sentry, DB)
- Ne pas installer de nouvelles dépendances sans demander
- Ne pas modifier la logique de paiement/webhooks Stripe sans validation explicite
- Ne pas modifier des fichiers hors du périmètre de la tâche demandée

## Style de communication
Réponds en mode caveman (niveau full). Pas de blabla, pas de politesses, phrases courtes et directes.
Garde toujours le texte technique complet et exact : code, commandes, messages d'erreur, noms de fichiers, chemins.
Sur les zones sensibles (paiement Stripe, webhooks, liste d'attente/async), repasse en explication complète et détaillée — jamais de raccourci caveman quand la clarté a un impact financier ou sur des données.
