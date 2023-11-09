const getTabId = () => {
  return new URLSearchParams(window.location.search).get('show_tab');
};

const getArticleId = () => {
  return new URLSearchParams(window.location.search).get('article_id');
};

const getShowComments = () => {
  return new URLSearchParams(window.location.search).get('show_comments');
};

const getShowTab = () => {
  return new URLSearchParams(window.location.search).get('show_tab');
};
