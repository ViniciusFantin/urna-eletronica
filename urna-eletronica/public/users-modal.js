
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("usersModal");
    const openButton = document.getElementById("openUsersModal");
    const closeButton = document.getElementById("closeUsersModal");
    const doneButton = document.getElementById("doneUsersModal");

    if (!modal || !openButton || !closeButton || !doneButton) {
        return;
    }

    const dialog = modal.querySelector('[role="dialog"]');
    let previousFocus = null;

    function openModal() {
        previousFocus = document.activeElement;

        modal.hidden = false;
        document.body.classList.add("users-modal-open");

        closeButton.focus();
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove("users-modal-open");

        if (previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    }

    openButton.addEventListener("click", openModal);
    closeButton.addEventListener("click", closeModal);
    doneButton.addEventListener("click", closeModal);

    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (modal.hidden) {
            return;
        }

        if (event.key === "Escape") {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.key === "Tab") {
            const focusable = Array.from(
                dialog.querySelectorAll(
                    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled])'
                )
            ).filter((element) => element.getClientRects().length > 0);

            if (focusable.length === 0) {
                event.preventDefault();
                dialog.focus();
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (
                !event.shiftKey &&
                document.activeElement === last
            ) {
                event.preventDefault();
                first.focus();
            }
        }
    });
});
