export default function useGetElementsToken() {
  if (localStorage.getItem('token')) {
    const user = localStorage.getItem('token');
    const payload = user.split('.')[1];
    const decoded = atob(payload);
    return JSON.parse(decoded);
  }
  return null;
}
