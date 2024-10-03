const updateCurrentMailout = async (mailout) => {
  const sendURL = `./api/dd_set_current_mailout.php?mailout=${mailout}`;
  emailsSent.appendChild(loading);
  const res = await fetch(sendURL);
  const output = await res.text();
  const status = output === 'SUCCESS' ? `Dive Dive Mailout ${mailout} has started sending` : `There was an error, please try again`;
  const newLine = document.createElement('p');
  newLine.innerHTML = status;
  loading.remove();
  emailsSent.appendChild(newLine);
  for (const formEl of mailoutForm.children) {
    formEl.disabled = true;
  }
};

const mailoutSubmit = async (data) => {
  for (const [name, value] of data) {
    if (name === 'mailout') updateCurrentMailout(value);
  }
};

const updatePreviews = (value) => {
  const previews = document.getElementById('previews');
  previews.innerHTML = '';
  const htmlPreview = document.createElement('a');
  htmlPreview.href = `assets/dd_mailout_bodies/html/${value}.html`;
  htmlPreview.target = '_blank';
  htmlPreview.innerHTML = 'Preview HTML';
  const textPreview = document.createElement('a');
  textPreview.href = `assets/dd_mailout_bodies/text/${value}.txt`;
  textPreview.target = '_blank';
  textPreview.innerHTML = 'Preview Text';
  const subjectPreview = document.createElement('a');
  subjectPreview.href = `assets/dd_mailout_bodies/subject/${value}.txt`;
  subjectPreview.target = '_blank';
  subjectPreview.innerHTML = 'Preview Subject';

  previews.appendChild(htmlPreview);
  previews.appendChild(document.createElement('br'));
  previews.appendChild(textPreview);
  previews.appendChild(document.createElement('br'));
  previews.appendChild(subjectPreview);
};

const populateMailoutSelect = async () => {
  const dataURL = './api/dd_mailoutOptions.php';
  const res = await fetch(dataURL);
  const mailoutOptions = await res.json();
  const select = document.getElementById('mailoutSelect');
  select.addEventListener('change', (e) => {
    updatePreviews(e.target.value);
  });
  for (let mailoutOption of mailoutOptions) {
    mailoutOption = mailoutOption.split('.')[0];
    const option = document.createElement('option');
    option.value = mailoutOption;
    option.innerHTML = mailoutOption;
    if (mailoutOption == 'test') option.selected = true;
    select.appendChild(option);
  }
  updatePreviews('test');
};

// save and remove loading gif
const loading = document.getElementById('loading');
const emailsSent = document.getElementById('emailsSent');
loading.remove();

// setup form
const mailoutForm = document.getElementById('mailoutForm');
mailoutForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const data = new FormData(mailoutForm);
  await mailoutSubmit(data);
});
await populateMailoutSelect();
