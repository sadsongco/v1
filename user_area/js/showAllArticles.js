const showAllArticles = (event) => {
  event.target.remove();
  const articleContainers = document.getElementsByClassName('articleContainer');
  for (const articleContainer of articleContainers) {
    articleContainer.classList.remove('hidden');
    if (articleContainer.getAttribute('hx-get')) {
      const hxget = articleContainer.getAttribute('hx-get');
      articleContainer.setAttribute('hx-get', hxget.replace('&hide=1', ''));
    }
  }
};
