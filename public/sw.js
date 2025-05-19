// Версия кеша для облегчения обновлений
const CACHE_VERSION = 'v1';
const CACHE_NAME = `dlk-cache-${CACHE_VERSION}`;

// Ресурсы для предварительного кеширования
const PRECACHE_URLS = [
  '/',
  '/favicon.ico',
  '/manifest.json',
  '/offline.html' // Страница для отображения в offline режиме
];

// Установка сервис-воркера и предварительное кеширование ресурсов
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
  );
});

// Активация сервис-воркера и очистка старых кешей
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(cacheName => cacheName.startsWith('dlk-cache-') && cacheName !== CACHE_NAME)
          .map(cacheName => caches.delete(cacheName))
      );
    }).then(() => self.clients.claim())
  );
});

// Обработка запросов
self.addEventListener('fetch', event => {
  // Стратегия сеть-первая, затем кеш
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Проверка успешного ответа для кеширования
        if (response && response.status === 200 && response.type === 'basic') {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });
        }
        return response;
      })
      .catch(() => {
        // При ошибке сети возвращаем из кеша
        return caches.match(event.request)
          .then(cachedResponse => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // Если ресурса нет в кеше и это HTML запрос, возвращаем offline.html
            if (event.request.headers.get('Accept')?.includes('text/html')) {
              return caches.match('/offline.html');
            }
            // Для других типов контента возвращаем ошибку
            return new Response('Нет соединения с сетью', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});

// Обработка push-уведомлений
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : {};
  const options = {
    body: data.body || 'Новое уведомление',
    icon: data.icon || '/icons/android-icon-192x192.png',
    badge: data.badge || '/icons/android-icon-96x96.png',
    data: {
      url: data.url || '/'
    }
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Уведомление', options)
  );
});

// Обработка клика по уведомлению
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  // Открытие указанного URL при клике на уведомление
  event.waitUntil(
    clients.openWindow(event.notification.data.url)
  );
});

// Хранение CSRF-токена для безопасной отправки запросов
let csrfToken = '';

// Прием сообщений от основного скрипта
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SET_CSRF_TOKEN') {
    csrfToken = event.data.token;
  }
  
  if (event.data && event.data.type === 'SUBSCRIBE_PUSH') {
    subscribeToPush().then(subscription => {
      if (subscription) {
        return sendSubscriptionToServer(subscription);
      }
    }).catch(error => console.error('Ошибка подписки на уведомления:', error));
  }
});

// Функция для подписки на push-уведомления
async function subscribeToPush() {
  try {
    return await self.registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(
        // Значение должно быть получено из переменной окружения
        '{{ env("VAPID_PUBLIC_KEY") }}'
      )
    });
  } catch (error) {
    console.error('Ошибка при подписке на push-уведомления:', error);
    return null;
  }
}

// Отправка подписки на сервер для сохранения
async function sendSubscriptionToServer(subscription) {
  const response = await fetch('/api/push-subscriptions', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify(subscription)
  });
  
  return response.ok;
}

// Преобразование base64-строки в массив Uint8Array для applicationServerKey
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  
  return outputArray;
}
