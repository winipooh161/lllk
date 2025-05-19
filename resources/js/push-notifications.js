/**
 * Скрипт для работы с Push-уведомлениями
 */

// Получение VAPID публичного ключа из конфигурации
const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]').getAttribute('content');

/**
 * Запрос разрешения на уведомления и подписка на push
 */
async function requestNotificationPermission(registration) {
    if (!('Notification' in window)) {
        console.log('Браузер не поддерживает уведомления');
        return;
    }
    
    try {
        const permission = await Notification.requestPermission();
        
        if (permission === 'granted') {
            console.log('Разрешение на уведомления получено');
            
            // Подписываемся на push-уведомления
            if (registration && registration.pushManager) {
                const subscription = await subscribeToPush(registration);
                if (subscription) {
                    await sendSubscriptionToServer(subscription);
                }
            }
        } else {
            console.log('Разрешение не получено:', permission);
        }
    } catch (error) {
        console.error('Ошибка при запросе разрешения:', error);
    }
}

/**
 * Подписка на push-уведомления
 */
async function subscribeToPush(registration) {
    try {
        return await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
        });
    } catch (error) {
        console.error('Ошибка подписки:', error);
        return null;
    }
}

/**
 * Отправка подписки на сервер
 */
async function sendSubscriptionToServer(subscription) {
    try {
        const response = await fetch('/api/push-subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(subscription)
        });
        
        if (!response.ok) {
            throw new Error('Ошибка сохранения подписки');
        }
        
        console.log('Подписка успешно отправлена на сервер');
    } catch (error) {
        console.error('Ошибка отправки подписки:', error);
    }
}

/**
 * Преобразование base64-строки в массив Uint8Array для applicationServerKey
 */
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

// Регистрация сервис-воркера и настройка push-уведомлений
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('ServiceWorker зарегистрирован:', registration.scope);
            
            // Передаем CSRF токен в сервис-воркер
            if (registration.active) {
                registration.active.postMessage({
                    type: 'SET_CSRF_TOKEN',
                    token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                });
            }

            // Запрос разрешения на push-уведомления
            await requestNotificationPermission(registration);
        } catch (error) {
            console.error('Ошибка регистрации ServiceWorker:', error);
        }
    });
}

// Экспорт функций для использования в других модулях
export { 
    requestNotificationPermission, 
    subscribeToPush, 
    sendSubscriptionToServer 
};
