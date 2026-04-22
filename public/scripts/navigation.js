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
    // We check what page/tab we are on from the URL
    const params = new URLSearchParams(window.location.search);
    const isStore = document.getElementById('store-grid') !== null;
    
    // Default logic for the store (slower waterfall effect)
    if (isStore) {
        const storeCards = document.querySelectorAll('#store-grid .game-card:not(.hidden-game)');
        storeCards.forEach((card, index) => {
            card.style.transition = 'none';
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'opacity 0.4s ease, transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                setTimeout(() => {
                    card.style.transition = '';
                    card.style.transform = '';
                }, 400);
            }, (index % 8) * 60 + 10); // 60ms - return to the original pace
        });
    } 
    // Default logic for admin panel (faster, isolating tables)
    else {
        const activeTab = params.get('tab') || 'users'; 
        
        // We select only one specific table for animation - first card is Users, second is Games
        let activeSelector = '#admin-users-card .admin-table-row:not(.hidden-admin-row)';
        if (activeTab === 'games') {
            activeSelector = '#admin-games-card .admin-table-row:not(.hidden-admin-row)';
        }

        const adminRows = document.querySelectorAll(activeSelector);
        adminRows.forEach((row, index) => {
            row.style.transition = 'none';
            row.style.opacity = '0';
            row.style.transform = 'translateY(15px)';

            setTimeout(() => {
                row.style.transition = 'opacity 0.3s ease, transform 0.3s cubic-bezier(0.2, 0.8, 0.2, 1)';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
                setTimeout(() => {
                    row.style.transition = '';
                    row.style.transform = '';
                }, 300);
            }, (index % 15) * 30 + 10);
        });
    }
}