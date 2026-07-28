/**
 * Comportements d'interface communs à toutes les pages.
 * Remplace les plugins JS de Bootstrap par les éléments natifs du navigateur.
 */

// --- Boîtes de dialogue -------------------------------------------------
// <button data-dialog="mon-id">  ouvre  <dialog id="mon-id">
// <button data-dialog-close>     ferme la boîte qui le contient
document.addEventListener('click', (event) => {

    const ouvrir = event.target.closest('[data-dialog]');

    if (ouvrir) {
        document.getElementById(ouvrir.dataset.dialog)?.showModal();
        return;
    }

    const fermer = event.target.closest('[data-dialog-close]');

    if (fermer) {
        fermer.closest('dialog')?.close();
        return;
    }

    // un clic sur le fond ferme la boîte, comme on s'y attend
    if (event.target.tagName === 'DIALOG') {

        const boite = event.target.getBoundingClientRect();

        const dehors = event.clientX < boite.left || event.clientX > boite.right
            || event.clientY < boite.top || event.clientY > boite.bottom;

        if (dehors) {
            event.target.close();
        }
    }
});

// --- Barre latérale sur mobile ------------------------------------------
const bascule = document.querySelector('[data-menu-toggle]');
const sidebar = document.getElementById('sidebar');
const voile = document.querySelector('[data-menu-backdrop]');

function basculerMenu(ouvrir) {
    sidebar.classList.toggle('-translate-x-full', !ouvrir);
    voile.classList.toggle('hidden', !ouvrir);
    bascule.setAttribute('aria-expanded', String(ouvrir));
}

if (bascule && sidebar && voile) {

    bascule.addEventListener('click', () => {
        basculerMenu(sidebar.classList.contains('-translate-x-full'));
    });

    voile.addEventListener('click', () => basculerMenu(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !voile.classList.contains('hidden')) {
            basculerMenu(false);
        }
    });
}
