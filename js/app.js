const validEmail = (mail) => {
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail)) {
    return true;
  }
  return false;
};

const submitEmail = async (form) => {
  const apiURL = './API/email_subscribe.php';
  const email = document.getElementById('email').value;
  const name = document.getElementById('name').value;
  const postObj = { email: email, name: name };
  try {
    const res = await fetch(apiURL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(postObj),
    });
    // return console.log(await res.text());
    return await res.json();
  } catch (err) {
    console.error(err);
  }
};

const submit = document.getElementById('emailSubmit');
submit.disabled = true;

document.getElementById('emailList').addEventListener('submit', async (e) => {
  e.preventDefault();
  submit.disabled = true;
  submit.value = '... processing';
  const res = await submitEmail(e.target);
  if (res.status == 'db_error') {
    submit.value = 'there was an error, please try again';
    submit.disabled = false;
    return;
  }
  if (res.status == 'exists') {
    submit.value = `you're already on the list!`;
  }
  if (res.success) {
    submit.value = 'thank you! check email for confirmation';
  }
  document.getElementById('email').disabled = true;
  document.getElementById('name').disabled = true;
});

document.getElementById('email').addEventListener('input', (e) => {
  if (validEmail(e.target.value)) {
    submit.disabled = false;
    submit.value = 'join the list';
  } else {
    submit.disabled = true;
    submit.value = 'enter your email';
  }
});

const announce = new Date('July 5, 2023 10:00:00 UTC+01:00');
const onsale = new Date('July 7, 2023 10:00:00 UTC+01:00');
const today = new Date();

if (today < announce) document.getElementById('date').style.display = 'flex';
else if (today > announce && today < onsale) {
  for (let elId of ['show', 'venue']) document.getElementById(elId).style.display = 'flex';
} else if (today > onsale) {
  for (let elId of ['show', 'venue']) document.getElementById(elId).style.display = 'flex';
}
