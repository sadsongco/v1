const openAcc = () => {
  const accHead = event.target.parentElement;
  const accHide = accHead.nextElementSibling;
  const commentsContainer = accHide.nextElementSibling;
  const commentForm = commentsContainer.nextElementSibling;
  accHead.classList.add('hide');
  accHide.classList.remove('hide');
  commentsContainer.classList.remove('collapsed');
  commentForm.classList.remove('collapsed');
};

const closeAcc = () => {
  const accHide = event.target.parentElement;
  const accHead = accHide.previousElementSibling;
  const commentsContainer = accHide.nextElementSibling;
  const commentForm = commentsContainer.nextElementSibling;
  accHead.classList.remove('hide');
  accHide.classList.add('hide');
  commentsContainer.classList.add('collapsed');
  commentForm.classList.add('collapsed');
};
