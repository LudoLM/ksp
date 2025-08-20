
export function useConvertFormatDate(date){

  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Mois est 0-indexé
  const day = date.getDate().toString().padStart(2, '0');
  const hours = date.getHours().toString().padStart(2, '0');
  const minutes = date.getMinutes().toString().padStart(2, '0');
  const seconds = date.getSeconds().toString().padStart(2, '0');

  // Construit la chaîne au format YYYY-MM-DDTHH:mm:ss sans décalage
  return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
}
