const precacheVersion = 1;
const cacheName = `precache-v${precacheVersion}`;
const preCacheItems = [
  '../../Src/Pages/Error.html',
  '../../Utilities/Third-party/Bootstrap/css/bootstrap.css',
  '../../Utilities/Third-party/Bootstrap/css/color.css',
  '../../Utilities/Stylesheets/Fonts.css',
  '../../Assets/Fonts/Poppins/Poppins-Bold.ttf',
  '../../Assets/Fonts/Poppins/Poppins-Regular.ttf',
  '../../Assets/Images/Loader-v1.gif',
  '../../Assets/Icons/PWA-Icon/MainIcon.png',
  '../../Utilities/Scripts/HomeScript.js',
  '../../Utilities/Scripts/service-worker.js',
];

self.addEventListener("install", (event) => {
  console.log("Service worker install event!");

  self.skipWaiting();

  event.waitUntil(
    caches.open(cacheName).then((cache) => {
      console.log("[ServiceWorker] Pre-caching files");
      return cache.addAll(preCacheItems);
    })
  );
});

self.addEventListener("activate", (event) => {
  console.log("[ServiceWorker] Activated");

  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(cacheNames.map((cache) => {
          if (cache.includes("precache") && cache !== cacheName) {
            console.log(`[ServiceWorker] Removing cached files from ${cache}`);
            return caches.delete(cache);
          }
        })
      );
    })
  );
});

self.addEventListener("fetch", (event) => {
  console.log(`[ServiceWorker] Fetching ${event.request.url}`);

  event.respondWith(
    caches.match(event.request).then((response) => {
      if (response) {
        console.log(`[ServiceWorker] Found in cache ${event.request.url}`);
        return response;
      }

      console.log(`[ServiceWorker] Network request for ${event.request.url}`);
      return fetch(event.request)
        .then((response) => response)
        .catch((error) => {
          const isHTMLPage = event.request.method === "GET" && event.request.headers.get("accept").includes("text/html");
          if (isHTMLPage) {
            return caches.match("../../Src/Pages/Error.html");
          }
        });
    })
  );
});
