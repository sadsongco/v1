const updateFileId = (id = null) => {
  const fileId = document.getElementById('fileId');
  fileId.value = parseInt(fileId.value) + 1;
};
