export function decode(token: string) {
  const base64Url = token.split('.')[1];
  return JSON.parse(window.atob(base64Url));
}
