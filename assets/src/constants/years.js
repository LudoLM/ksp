

const START_YEAR = 2024;

export const yearsOptions = () => {
  const currentYear = new Date().getFullYear();
  const years = [];

  for (let year = currentYear; year >= START_YEAR; year--) {
    years.push({ id: year, name: String(year) });
  }

  return years;
};
