const sidebar = document.querySelector(".sidebar");
const toggleBtn = document.querySelector(".topbutton");
const sidebarMobile = window.matchMedia("(max-width: 768px)");

function updateSidebarToggle() {
    const collapsed = sidebar.classList.contains("collapsed");
    const expanded = sidebarMobile.matches ? collapsed : !collapsed;
    toggleBtn.setAttribute("aria-expanded", String(expanded));
    toggleBtn.setAttribute("aria-label", sidebarMobile.matches
        ? (expanded ? "Close sidebar" : "Open sidebar")
        : (expanded ? "Collapse sidebar" : "Expand sidebar"));
    toggleBtn.querySelector("i").className = expanded ? "bx bx-chevron-left" : "bx bx-chevron-right";
}

toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    updateSidebarToggle();
});
sidebarMobile.addEventListener("change", updateSidebarToggle);
updateSidebarToggle();
