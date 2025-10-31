// assets/utils/assetHelper.js

// Définir le mappage des imports une seule fois pour tout le dossier 'uploads'
// Le chemin doit être adapté à la racine de votre projet si ce helper est importé de différents endroits.
const uploadsImports = import.meta.glob('../../images/uploads/*', { eager: true });

export function getImageUrl(filename) {
  if (!filename) return;

  // Chemin relatif à partir de l'endroit où Vite résout l'asset (pour correspondre à la clé du glob)
  const path = `../../images/uploads/${filename}`;

  return uploadsImports[path]?.default;
}

