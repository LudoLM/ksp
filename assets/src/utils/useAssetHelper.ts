const uploadsImports = import.meta.glob('../../images/uploads/*', { eager: true }) as Record<string, any>

export function getImageUrl(filename: string | null | undefined): string | undefined {
  if (!filename) return undefined

  const path = `../../images/uploads/${filename}`

  return uploadsImports[path]?.default as string | undefined
}
