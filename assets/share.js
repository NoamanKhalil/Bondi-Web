// Share buttons on blog and guide pages: the device's own share sheet where the browser has one (phones, Safari),
// and Copy link. The network buttons are plain links: nothing loads from any social network until someone clicks.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-share]').forEach(box => {
    const url = box.dataset.url, title = box.dataset.title, done = box.querySelector('[data-share-done]');
    const say = text => { done.textContent = text; setTimeout(() => { done.textContent = ''; }, 2500); };
    const native = box.querySelector('[data-share-native]');
    if (navigator.share) {
      native.hidden = false;
      box.classList.add('has-native'); // on phones, the share sheet stands in for the network buttons (see blog.css)
      native.addEventListener('click', () => navigator.share({ title, url }).catch(() => {}));
    }
    box.querySelector('[data-share-copy]').addEventListener('click', () => {
      (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(() => say('Link copied'), () => say(url));
    });
  });
});
