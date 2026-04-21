import { restoreState, initCategoryLogic } from './filters.js';

// AJAX Soft Navigation
export async function softReload(url) {
    // 1. Aktualizujemy URL w pasku przeglądarki bez przeładowania strony
    window.history.pushState({}, '', url);

    const mainContainer = document.querySelector('main.store-layout');
    if (!mainContainer) {
        window.location.href = url; // emergency fallback
        return;
    }

    // Subtle charging visual effect
    mainContainer.style.opacity = '0.5';
    mainContainer.style.transition = 'opacity 0.2s';

    try {
        // We are downloading a new page in the background
        const response = await fetch(url);
        const htmlString = await response.text();

        // We convert text into a virtual HTML document
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(htmlString, 'text/html');
        const newMain = newDoc.querySelector('main.store-layout');

        if (newMain) {
            // We are replacing the content of the page
            mainContainer.innerHTML = newMain.innerHTML;
            mainContainer.style.opacity = '1';

            // We restore checkboxes and inputs
            restoreState();
            initCategoryLogic();

            // We send a signal to main.js to reconnect the listeners (e.g. Load More buttons)
            document.dispatchEvent(new Event('content-reloaded'));
        } else {
            window.location.href = url;
        }
    } catch (e) {
        window.location.href = url;
    }
}

// Rebuilding the page when the user clicks the "Back/Next" button in the browser
window.addEventListener('popstate', () => {
    softReload(window.location.href);
});

// Tile Animation
export function animateTiles() {
    const cards = document.querySelectorAll('#store-grid .game-card:not(.hidden-game)');
    
    cards.forEach((card, index) => {
        // We immediately hide the element and move it down
        card.style.transition = 'none';
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        // We apply a delayed, smooth entry, simulating a cascade (50ms interval)
        setTimeout(() => {
            card.style.transition = 'opacity 0.4s ease, transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';

            setTimeout(() => {
                card.style.transition = '';
                card.style.transform = '';
            }, 400);
            
        }, (index % 8) * 50 + 10);
    });
}