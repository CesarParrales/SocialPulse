import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

type TranslationTree = string | { [key: string]: TranslationTree };

function resolveKey(tree: TranslationTree, key: string): string | null {
    const parts = key.split('.');
    let current: TranslationTree = tree;

    for (const part of parts) {
        if (typeof current !== 'object' || current === null) {
            return null;
        }
        current = current[part];
    }

    return typeof current === 'string' ? current : null;
}

export function useTranslation() {
    const { translations } = usePage<PageProps>().props;

    const t = (
        key: string,
        replacements?: Record<string, string>,
        fallback?: string,
    ): string => {
        let text =
            resolveKey(translations as TranslationTree, key) ??
            fallback ??
            key;

        if (replacements) {
            for (const [placeholder, value] of Object.entries(replacements)) {
                text = text.replace(`:${placeholder}`, value);
            }
        }

        return text;
    };

    return { t };
}
