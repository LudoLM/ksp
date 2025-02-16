export function useRemainingTime(dateDebut, remainingTime, timeLimiteToSubscribe) {

  // Capture la valeur initiale
  let initialTime = Date.now();
  const interval = setInterval(() => {
    initialTime += 1000;
    const tzOffset = new Date().getTimezoneOffset() * 60000; // Décalage en millisecondes
    remainingTime.value = dateDebut.value.getTime() - timeLimiteToSubscribe - initialTime + tzOffset;
    if (remainingTime.value <= 0) {
      clearInterval(interval);
    }

  }, 1000);

}


export function useRemainingTimeFormat(remainingTime) {
  const hours = Math.abs(Math.floor(remainingTime.value / (1000 * 60 * 60)));
  const minutes = Math.abs(Math.floor((remainingTime.value % (1000 * 60 * 60)) / (1000 * 60)));
  const seconds = Math.abs(Math.floor((remainingTime.value % (1000 * 60)) / 1000));

  const formattedMinutes = String(minutes).padStart(2, '0');
  const formattedSeconds = String(seconds).padStart(2, '0');
  return `${hours}h${formattedMinutes}m${formattedSeconds}s`;
}
