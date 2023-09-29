const userClicker = document.getElementById('user');
const userModal = document.getElementById('userModal');

userClicker.addEventListener('click', (e) => {
  e.preventDefault();
  if (userModal.classList.contains('userHide')) {
    userModal.classList.remove('userHide');
    userModal.classList.add('userShow');
  } else {
    userModal.classList.remove('userShow');
    userModal.classList.add('userHide');
  }
});
