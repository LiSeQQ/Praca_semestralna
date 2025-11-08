// js/script.js
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('regForm');
  const p1 = document.getElementById('password');
  const p2 = document.getElementById('password2');
  const show = document.getElementById('showpass');

  show && show.addEventListener('change', function(){
    const type = this.checked ? 'text' : 'password';
    p1.type = type;
    p2.type = type;
  });

  form && form.addEventListener('submit', function(e){
    // sprawdzenie zgodności haseł po stronie klienta
    if (p1.value !== p2.value) {
      e.preventDefault();
      alert('Hasła muszą być identyczne.');
      p1.focus();
      return false;
    }
    if (p1.value.length < 6) {
      e.preventDefault();
      alert('Hasło musi mieć co najmniej 6 znaków.');
      p1.focus();
      return false;
    }
    return true;
  });
});
