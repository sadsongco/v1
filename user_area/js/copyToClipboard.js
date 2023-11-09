const alertMsg = (msg) => {
  const alertMsgContainer = document.getElementById('alertMsg');
  alertMsgContainer.innerHTML = msg;
  alertMsgContainer.classList.add('alertShow');
  alertMsgContainer.addEventListener('animationend', () => {
    unalertMsg(alertMsgContainer);
  });
};

const unalertMsg = (el) => {
  el.classList.remove('alertShow');
};

const copyToClipboard = async (e, tag) => {
  console.log(tag);
  e.preventDefault();
  e.stopPropagation();
  try {
    await navigator.clipboard.writeText(tag);
    alertMsg('article link copied to clipboard');
  } catch (e) {
    console.error(e);
    throw new Error('failed to copy');
  }
};
