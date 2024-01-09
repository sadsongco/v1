const showAllArticles = (target) => {
  if (!target) return;
  target.remove();
  // close open comment accordions
  const commentExpanders = document.getElementsByClassName('commentAccExpand');
  const commentCollapsers = document.getElementsByClassName('commentAccCollapse');
  for (const commentExpander of commentExpanders) commentExpander.classList.remove('hide');
  for (const commentCollapser of commentCollapsers) commentCollapser.classList.add('hide');
  const commentContainers = document.getElementsByClassName('commentsContainer');
  for (const commentContainer of commentContainers) commentContainer.classList.add('collapsed');
  // show all articles
  const articleContainers = document.getElementsByClassName('articleContainer');
  for (const articleContainer of articleContainers) {
    articleContainer.classList.remove('hidden');
    if (articleContainer.getAttribute('hx-get')) {
      const hxget = articleContainer.getAttribute('hx-get');
      articleContainer.setAttribute('hx-get', hxget.replace('&hide=1', ''));
    }
  }
};
