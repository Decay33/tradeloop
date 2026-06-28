export function appUrl(path = '') {
    const meta = document.querySelector('meta[name="app-base-path"]');
    const basePath = (meta?.content || '').replace(/\/+$/, '');
    const cleanPath = `/${String(path || '').replace(/^\/+/, '')}`;

    if (cleanPath === '/') {
        return basePath || '/';
    }

    return `${basePath}${cleanPath}`;
}
