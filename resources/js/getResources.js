const getResources = async (resourceName) => {
  const url = `./api/get_resource.php?resource=${resourceName}`;
  const res = await fetch(url);
  const data = await res.json();
  return data;
};

const createImgLink = (path, urls, filename, msgs) => {
  const imgContainer = document.createElement('div');
  imgContainer.classList.add('imgContainer');
  const imgEl = document.createElement('img');
  imgEl.classList.add('previewImg');
  imgEl.src = path;
  imgContainer.appendChild(imgEl);
  for (let urlIdx in urls) {
    const linkEl = document.createElement('a');
    linkEl.classList.add('imgLink');
    linkEl.href = urls[urlIdx];
    linkEl.download = filename;
    linkEl.innerHTML = msgs[urlIdx];
    imgContainer.appendChild(linkEl);
  }
  return imgContainer;
};

const createResourceEl = (content) => {
  const resourceEl = document.createElement('section');
  resourceEl.classList.add('resource');
  const resourceHead = document.createElement('h2');
  resourceHead.innerHTML = content.name.replace('_', ' ');
  resourceEl.appendChild(resourceHead);
  for (let entry of content.resources) {
    let resourcePath = content.path;
    let entryEl;
    if (content.name === 'press_shots') {
      const webPath = resourcePath + 'web/';
      const fullresPath = resourcePath + 'full_res';
      resourcePath += 'thumbnail/';
      const urls = [webPath + entry, fullresPath + entry];
      const msgs = ['click to download web size image', 'click to download full res image'];
      resourceEl.appendChild(createImgLink(resourcePath + entry, urls, entry, msgs));
      continue;
    }
    if (content.name === 'logos') {
      entryEl = document.createElement('img');
      entryEl.classList.add('previewImg');
      entryEl.src = resourcePath + entry;
    } else {
      entryEl = document.createElement('p');
      entryEl.innerHTML = entry;
    }
    const entryLink = document.createElement('a');
    entryLink.href = content.path + entry;
    entryLink.download = entry;
    entryLink.appendChild(entryEl);
    resourceEl.appendChild(entryLink);
  }
  return resourceEl;
};

const resourceSections = ['bio', 'press_shots', 'logos', 'tech_spec'];
const container = document.getElementById('container');

for (let resourceSection of resourceSections) {
  const content = await getResources(resourceSection);
  console.log(content);
  const resourceEl = createResourceEl(content);
  container.appendChild(resourceEl);
}
