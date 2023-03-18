// @flow strict

export function unSlug(slug: string): string {
    return slug
        .split('_')
        .map(word => word[0].toUpperCase() + word.slice(1))
        .join(' ');
}