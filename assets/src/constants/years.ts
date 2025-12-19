interface SelectOption {
  id: number
  name: string
}

const START_YEAR = 2024

export function yearsOptions(): SelectOption[] {
  const currentYear = new Date().getFullYear()
  const years: SelectOption[] = []

  for (let year = currentYear; year >= START_YEAR; year--) {
    years.push({ id: year, name: String(year) })
  }

  return years
}
