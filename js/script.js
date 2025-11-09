// === Pokaż / ukryj hasło ===
document.addEventListener("DOMContentLoaded", () => {
  const showPass = document.getElementById("showpass");
  const pass1 = document.getElementById("password");
  const pass2 = document.getElementById("password2");

  if (showPass) {
    showPass.addEventListener("change", () => {
      const type = showPass.checked ? "text" : "password";
      pass1.type = type;
      pass2.type = type;
    });
  }

  // === Walidacja hasła (JS) ===
  const form = document.getElementById("regForm");
  if (form) {
    form.addEventListener("submit", (e) => {
      const p1 = pass1.value.trim();
      const p2 = pass2.value.trim();

      if (p1.length < 6) {
        alert("Hasło musi mieć co najmniej 6 znaków.");
        e.preventDefault();
        return;
      }
      if (p1 !== p2) {
        alert("Hasła nie są identyczne.");
        e.preventDefault();
        return;
      }
    });
  }
});
