document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.querySelector("[data-menu-toggle]");
  const nav = document.querySelector("[data-nav]");
  toggle?.addEventListener("click", () => {
    const open = toggle.getAttribute("aria-expanded") !== "true";
    toggle.setAttribute("aria-expanded", String(open));
    nav?.classList.toggle("nav-open", open);
  });
  const userToggle = document.querySelector("[data-user-toggle]");
  userToggle?.addEventListener("click", () => {
    const open = userToggle.getAttribute("aria-expanded") !== "true";
    userToggle.setAttribute("aria-expanded", String(open));
    userToggle.closest(".user-menu")?.classList.toggle("open", open);
  });
  document.addEventListener("click", (event) => {
    if (!event.target.closest(".user-menu")) {
      userToggle?.setAttribute("aria-expanded", "false");
      userToggle?.closest(".user-menu")?.classList.remove("open");
    }
  });
  document
    .querySelectorAll("[data-dismiss]")
    .forEach((button) =>
      button.addEventListener("click", () =>
        button.closest(".alert")?.remove(),
      ),
    );
  document.querySelectorAll("[data-password]").forEach((button) =>
    button.addEventListener("click", () => {
      const field = document.getElementById(button.dataset.password);
      if (!field) return;
      field.type = field.type === "password" ? "text" : "password";
      button.textContent = field.type === "password" ? "Show" : "Hide";
    }),
  );
  document.querySelectorAll("[data-confirm]").forEach((form) =>
    form.addEventListener("submit", (event) => {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    }),
  );
  document.querySelectorAll("[data-count]").forEach((counter) => {
    const field = document.getElementById(counter.dataset.count);
    if (!field) return;
    const update = () => {
      counter.textContent = `${field.value.length} / ${field.maxLength}`;
    };
    field.addEventListener("input", update);
    update();
  });
  const searchInput = document.querySelector(
    '.nav-search input[type="search"]',
  );
  document.addEventListener("keydown", (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
      event.preventDefault();
      searchInput?.focus();
    }
  });
});
