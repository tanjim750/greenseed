import { getBehaviour } from './behaviours/registry.js';

function parseJsonDataset(root, key, fallback) {
    try {
        const value = root.dataset[key];

        if (!value) {
            return fallback;
        }

        return JSON.parse(value);
    } catch {
        root.dataset.runtimeError = `invalid-${key}`;

        return fallback;
    }
}

function isPlainObject(value) {
    return value
        && typeof value === 'object'
        && !Array.isArray(value);
}

function normalizeBehaviourDefinitions(value) {
    if (!Array.isArray(value)) {
        return [];
    }

    return value.filter((definition) => {
        return definition
            && typeof definition === 'object'
            && typeof definition.key === 'string'
            && definition.key !== '';
    });
}

function findComponentRoots(container) {
    const roots = [];

    if (container instanceof Element && container.matches('[data-landing-component]')) {
        roots.push(container);
    }

    container
        .querySelectorAll('[data-landing-component]')
        .forEach((root) => roots.push(root));

    return roots;
}

export function initializeLandingComponents(container = document) {
    findComponentRoots(container)
        .forEach((root) => {
            if (root.dataset.runtimeInitialized === 'true') {
                return;
            }

            delete root.dataset.runtimeError;

            const behaviourDefinitions = normalizeBehaviourDefinitions(
                parseJsonDataset(root, 'behaviours', [])
            );

            if (root.dataset.runtimeError) {
                return;
            }

            const runtimeConfig = parseJsonDataset(root, 'runtimeConfig', {});

            if (root.dataset.runtimeError) {
                return;
            }

            const cleanupHandlers = [];
            const runtime = {
                componentScope: root.dataset.componentScope ?? '',
                config: isPlainObject(runtimeConfig) ? runtimeConfig : {},
            };

            behaviourDefinitions.forEach(({ key, config }) => {
                const initializer = getBehaviour(key);

                if (!initializer) {
                    console.warn(`Unknown landing behaviour: ${key}`);

                    return;
                }

                let cleanup = null;

                try {
                    cleanup = initializer(
                        root,
                        isPlainObject(config) ? config : {},
                        runtime
                    );
                } catch (error) {
                    root.dataset.runtimeError = `behaviour-failed-${key}`;
                    console.warn(`Landing behaviour failed: ${key}`, error);

                    return;
                }

                if (typeof cleanup === 'function') {
                    cleanupHandlers.push(cleanup);
                }
            });

            root.landingCleanup = () => {
                cleanupHandlers.forEach((cleanup) => {
                    try {
                        cleanup();
                    } catch (error) {
                        console.warn('Landing behaviour cleanup failed', error);
                    }
                });
                cleanupHandlers.length = 0;
                root.dataset.runtimeInitialized = 'false';
            };

            root.dataset.runtimeInitialized = 'true';
        });
}

window.LandingPageRuntime = {
    initialize: initializeLandingComponents,
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initializeLandingComponents(), {
        once: true,
    });
} else {
    initializeLandingComponents();
}
