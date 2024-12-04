const addItem = (value) => {
  const newInput = document.createElement('input');
  newInput.id = 'item_' + value;
  newInput.type = 'hidden';
  newInput.name = 'orderItems[]';
  newInput.value = value;
  return newInput;
};

const removeItem = (itemID) => {
  document.getElementById('item_' + itemID).remove();
  document.getElementById('itemMsg_' + itemID).remove();
};

const addItemList = (itemText, itemID) => {
  const newItemMsg = document.createElement('div');
  newItemMsg.id = 'itemMsg_' + itemID;
  newItemMsg.innerHTML = itemText;
  const removeItemButton = document.createElement('button');
  removeItemButton.value = 'removeItem';
  removeItemButton.dataset.item = itemID;
  removeItemButton.innerHTML = 'Remove From Order';
  removeItemButton.addEventListener('click', (e) => {
    e.preventDefault();
    removeItem(e.target.dataset.item);
  });
  newItemMsg.appendChild(removeItemButton);
  return newItemMsg;
};
