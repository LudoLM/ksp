export default function useGetElementsToken() {
    const user = localStorage.getItem('token');
    const payload = user.split('.')[1];
    const decoded = atob(payload);
    return JSON.parse(decoded);
}