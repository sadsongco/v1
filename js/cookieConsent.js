const consentClose = document.getElementById('closeConsent');
const cookieConsent = document.getElementById('cookieConsent');
cookieConsent.classList.remove('consentHidden');
cookieConsent.classList.add('consentShow');
consentClose.addEventListener('click', (e) => {
  cookieConsent.classList.remove('consentShow');
  cookieConsent.classList.add('consentHidden');
});
