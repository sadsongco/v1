const itemForm = document.getElementById('orderForm');
const addItemButton = document.getElementById('add_item');
const itemSelect = document.getElementById('item');
const itemList = document.getElementById('items');

addItemButton.addEventListener('click', (e) => {
  e.preventDefault();
  itemForm.appendChild(addItem(itemSelect.value));
  itemList.appendChild(addItemList(itemSelect.options[itemSelect.selectedIndex].text, itemSelect.value));
});
