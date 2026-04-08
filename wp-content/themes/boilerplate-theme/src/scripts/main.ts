import { $scroll } from './stores/scroll';
import { Scroll } from './classes/Scroll';
import { Transitions } from './classes/Transitions';

import '@styles/main.css';

// Always included and initiated classes
import '@scripts/components/globals/Header';

// Initialize the Transitions class
const transitions = new Transitions();
transitions.init();

// Initialize the Scroll class
Scroll.init();

if (import.meta.env.DEV) {
    // Enable scroll restoration to avoid the page jumping to the top on page refresh
    history.scrollRestoration = 'auto';

    // Dynamically import the grid-helper only in development mode
    import('@locomotivemtl/grid-helper')
        .then(({ default: GridHelper }) => {
            new GridHelper({
                columns: 4,
                gutterWidth: 'var(--grid-gutter)',
                marginWidth: 'var(--grid-margin)',
                breakpoints: {
                    '700': { columns: 12, gutterWidth: 'var(--grid-gutter)' }
                }
            });
        })
        .catch((error) => {
            console.error('Failed to load the grid helper:', error);
        });
}

const isSafari = () => {
    document.documentElement.classList.toggle(
        'is-safari',
        navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Version') != -1
    );
};
isSafari();
