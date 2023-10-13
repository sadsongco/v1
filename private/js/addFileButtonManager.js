const updateFileId = (id = null) => {
  const fileId = document.getElementById('fileId');
  fileId.value = parseInt(fileId.value) + 1;
};
const addFileButton = document.getElementById('addFileButton');
addFileButton.addEventListener('click', (e) => {
  updateFileId();
});
