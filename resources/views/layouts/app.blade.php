<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title_site ?? 'Личный кабинет' }}</title>
    <link rel="stylesheet" href="{{ asset('/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/introjs.min.css') }}">

  
    <script src="{{ asset('/js/wow.js') }}"></script>
    <!-- Подключаем стили Intro.js -->


    <script src="{{ asset('/js/intro.min.js') }}"></script>


    <!-- Обязательный (и достаточный) тег для браузеров -->
    <link type="image/x-icon" rel="shortcut icon" href="{{ asset('/favicon.ico') }}">

    <!-- Дополнительные иконки для десктопных браузеров -->
    <link type="image/png" sizes="16x16" rel="icon" href="{{ asset('/icons/favicon-16x16.png') }}">
    <link type="image/png" sizes="32x32" rel="icon" href="{{ asset('/icons/favicon-32x32.png') }}">
    <link type="image/png" sizes="96x96" rel="icon" href="{{ asset('/icons/favicon-96x96.png') }}">
    <link type="image/png" sizes="120x120" rel="icon" href="{{ asset('/icons/favicon-120x120.png') }}">

    <!-- Иконки для Android -->
    <link type="image/png" sizes="72x72" rel="icon" href="{{ asset('/icons/android-icon-72x72.png') }}">
    <link type="image/png" sizes="96x96" rel="icon" href="{{ asset('/icons/android-icon-96x96.png') }}">
    <link type="image/png" sizes="144x144" rel="icon" href="{{ asset('/icons/android-icon-144x144.png') }}">
    <link type="image/png" sizes="192x192" rel="icon" href="{{ asset('/icons/android-icon-192x192.png') }}">
    <link type="image/png" sizes="512x512" rel="icon" href="{{ asset('/icons/android-icon-512x512.png') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">

    <!-- Иконки для iOS (Apple) -->
    <link sizes="57x57" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-57x57.png') }} ">
    <link sizes="60x60" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-60x60.png') }} ">
    <link sizes="72x72" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-72x72.png') }} ">
    <link sizes="76x76" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-76x76.png') }} ">
    <link sizes="114x114" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-114x114.png') }} ">
    <link sizes="120x120" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-120x120.png') }} ">
    <link sizes="144x144" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-144x144.png') }} ">
    <link sizes="152x152" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-152x152.png') }} ">
    <link sizes="180x180" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-180x180.png') }} ">

    <!-- Иконки для MacOS (Apple) -->
    <link color="#e52037" rel="mask-icon" href="./safari-pinned-tab.svg">

    <!-- Иконки и цвета для плиток Windows -->
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="msapplication-TileImage" content="./mstile-144x144.png">
    <meta name="msapplication-square70x70logo" content="./mstile-70x70.png">
    <meta name="msapplication-square150x150logo" content="./mstile-150x150.png">
    <meta name="msapplication-wide310x150logo" content="./mstile-310x310.png">
    <meta name="msapplication-square310x310logo" content="./mstile-310x150.png">
    <meta name="application-name" content="My Application">
    <meta name="msapplication-config" content="./browserconfig.xml">

    <!-- FontAwesome для звезд рейтинга -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- PWA скрипты -->
    <script>
        // Регистрация сервис-воркера для PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker зарегистрирован:', registration.scope);
                        
                        // Передаем CSRF токен в сервис-воркер
                        if (registration.active) {
                            registration.active.postMessage({
                                type: 'SET_CSRF_TOKEN',
                                token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            });
                        }

                        // Запрос разрешения на push-уведомления
                        requestNotificationPermission(registration);
                    })
                    .catch(error => {
                        console.error('Ошибка регистрации ServiceWorker:', error);
                    });
            });
        }

        // Запрос разрешения на уведомления и подписка на push
        function requestNotificationPermission(registration) {
            if ('Notification' in window) {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('Разрешение на уведомления получено');
                        
                        // Подписываемся на push-уведомления
                        if (registration && registration.pushManager) {
                            registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlBase64ToUint8Array(
                                    // Замените на ваш VAPID public key
                                    'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U'
                                )
                            }).then(subscription => {
                                // Отправляем подписку на сервер
                                sendSubscriptionToServer(subscription);
                            }).catch(err => console.error('Ошибка подписки:', err));
                        }
                    }
                });
            }
        }

        // Отправка подписки на сервер
        function sendSubscriptionToServer(subscription) {
            fetch('/api/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(subscription)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Не удалось отправить подписку на сервер');
                }
                console.log('Подписка успешно отправлена на сервер');
            })
            .catch(error => {
                console.error('Ошибка отправки подписки:', error);
            });
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
    </script>

    <script>
        wow = new WOW({
            boxClass: 'wow', // default
            animateClass: 'animated', // default
            offset: 0, // default
            mobile: true, // default
            live: true // default  
        })
        wow.init();
    </script>
    <script>
        function refreshCsrfToken() {
            fetch('{{ route('refresh-csrf') }}')
                .then(response => response.json())
                .then data => {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                    document.querySelectorAll('input[name="_token"]').forEach(input => input.value = data.token);
                });
        }

        setInterval(refreshCsrfToken, 60000); // Обновление каждые 10 минут
    </script>
    @include('layouts/style')

    <!-- Передача данных для JS -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'user' => Auth::check()
                ? [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'status' => Auth::user()->status,
                ]
                : null,
        ]) !!};
    </script>
    <!-- Дополнительные скрипты и стили в зависимости от страницы -->
    @yield('scripts')
    @yield('styles')
 <style>
     .profile-awards {
    width: 50px !important;
}
    </style>
    <!-- Добавляем улучшенный скрипт для раскрывающихся фильтров -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация раскрывающихся фильтров на всех страницах
            initCollapsibleFilters();
        });

        // Улучшенная функция инициализации раскрывающихся фильтров
        function initCollapsibleFilters() {
            const filterToggles = document.querySelectorAll('.filter-toggle');

            filterToggles.forEach(toggle => {
                // Получаем целевую панель либо из data-target атрибута, либо по умолчанию #filter-panel
                const targetSelector = toggle.getAttribute('data-target') || '#filter-panel';
                const panel = document.querySelector(targetSelector);
                const icon = toggle.querySelector('.filter-toggle-icon i');

                if (!panel) {
                    console.warn('Не найдена панель фильтра:', targetSelector);
                    return;
                }

                // Проверяем сохраненное состояние в localStorage
                const targetId = panel.id;
                const isExpanded = localStorage.getItem('filter_' + targetId) === 'expanded';

                // Устанавливаем начальное состояние
                if (isExpanded) {
                    panel.classList.add('expanded');
                    if (icon) icon.classList.add('rotated');
                }

                // Добавляем обработчик события для переключения состояния
                toggle.addEventListener('click', function(event) {
                    event.preventDefault(); // Предотвращаем действие по умолчанию

                    panel.classList.toggle('expanded');
                    if (icon) icon.classList.toggle('rotated');

                    // Сохраняем новое состояние в localStorage
                    if (panel.classList.contains('expanded')) {
                        try {
                            localStorage.setItem('filter_' + targetId, 'expanded');
                        } catch (e) {
                            console.warn('Не удалось сохранить состояние фильтра в localStorage');
                        }
                    } else {
                        try {
                            localStorage.setItem('filter_' + targetId, 'collapsed');
                        } catch (e) {
                            console.warn('Не удалось сохранить состояние фильтра в localStorage');
                        }
                    }
                });
            });

            // Проверяем, есть ли активные фильтры, и автоматически раскрываем панель
            const activeFilterInputs = document.querySelectorAll(
                '.filter input[value]:not([value=""]), .filter select option:checked:not([value=""])');
            if (activeFilterInputs.length > 0) {
                // Для каждой формы фильтров с активными фильтрами
                const filterForms = new Set();
                activeFilterInputs.forEach(input => {
                    const form = input.closest('form');
                    if (form) filterForms.add(form);
                });

                filterForms.forEach(form => {
                    const panel = form.closest('.filter-panel');
                    if (panel && !panel.classList.contains('expanded')) {
                        // Находим соответствующий toggle
                        const toggleId = panel.id;
                        const toggle = document.querySelector(`.filter-toggle[data-target="#${toggleId}"]`) ||
                            document.querySelector('.filter-toggle[data-target="#filter-panel"]');
                        if (toggle) {
                            // Эмулируем клик для раскрытия фильтра
                            setTimeout(() => {
                                if (!panel.classList.contains('expanded')) {
                                    toggle.click();
                                }
                            }, 100);
                        }
                    }
                });
            }

            // Обновляем счетчики активных фильтров
            updateFilterCounters();
        }

        // Функция для обновления счетчиков активных фильтров
        function updateFilterCounters() {
            const forms = document.querySelectorAll('.filter form');

            forms.forEach(form => {
                let count = 0;

                // Проверяем текстовые поля, даты и селекты
                const inputs = form.querySelectorAll('input[type="text"], input[type="date"], select');
                inputs.forEach(input => {
                    if (input.value && input.name !== 'view_type') {
                        count++;
                    }
                });

                // Обновляем счетчик для текущей формы
                const counterElements = form.closest('.filter-panel')?.parentNode.querySelectorAll(
                    '.filter-counter');
                if (counterElements && counterElements.length > 0) {
                    counterElements.forEach(counter => {
                        counter.textContent = count;
                        if (count > 0) {
                            counter.classList.add('active');
                        } else {
                            counter.classList.remove('active');
                        }
                    });
                }
            });
        }
    </script>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.6/jquery.simplePagination.min.js"></script>

    @vite(['resources/css/font.css', 'resources/js/ratings.js','resources/css/animation.css', 'resources/css/style.css', 'resources/css/element.css', 'resources/css/mobile.css', 'resources/js/bootstrap.js', 'resources/js/modal.js', 'resources/js/success.js', 'resources/js/mask.js'])

    <!-- Стили для модального окна рейтингов -->
</head>

<body>

    <div id="loading-screen">
        <img src="/storage/icon/fool_logo.svg" alt="Loading">
    </div>
    <script>
        window.addEventListener('load', () => {
            const loadingScreen = document.getElementById('loading-screen');
            const content = document.getElementById('content');
            setTimeout(() => {
                loadingScreen.classList.add('hidden'); // Применяем класс для анимации исчезновения
                document.body.style.overflow = 'auto'; // Включаем прокрутку
                setTimeout(() => {
                    loadingScreen.style.display =
                    'none'; // Полностью убираем загрузку после анимации
                    content.style.opacity =
                    '1'; // Плавно показываем содержимое (контент уже анимируется в CSS)
                }, 1000); // Длительность анимации исчезновения (совпадает с fadeOut)
            }, 1000); // Задержка до начала исчезновения
        });
    </script>

    @if (session('success'))
        <div id="success-message" class="success-message">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div id="error-message" class="error-message">
            {{ session('error') }}
        </div>
    @endif
    <div id="messages"></div>

    <main>

        @yield('content')
        @include('layouts/mobponel')
    </main>

    

    <!-- Дополнительные скрипты в конце страницы -->
    @stack('scripts')

   
    <!-- Убедимся, что Bootstrap JS подключен -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('toggle-panel');
            const panel = document.querySelector('.main__ponel');

            // Проверяем наличие элементов перед работой с ними
            if (toggleButton && panel) {
                // Проверяем сохраненное состояние панели в localStorage
                const isCollapsed = localStorage.getItem('panelCollapsed') === 'true';
                if (isCollapsed) {
                    panel.classList.add('collapsed');
                }

                // Обработчик клика по кнопке переключения
                toggleButton.addEventListener('click', () => {
                    panel.classList.toggle('collapsed');
                    // Сохраняем текущее состояние панели в localStorage
                    const collapsed = panel.classList.contains('collapsed');
                    localStorage.setItem('panelCollapsed', collapsed);
                });
            } else {
                console.error('Toggle panel elements not found: toggleButton =', !!toggleButton, 'panel =', !!
                panel);
            }
        });
    </script>

    <!-- Модальное окно для отображения рейтингов -->
    <div id="rating-modal" class="rating-modal" style="display:none;">
        <div class="rating-modal-content">
            <h2>Оцените работу специалиста</h2>
            <div class="rating-progress">
                <span id="current-rating-index">1</span> из <span id="total-ratings">1</span>
            </div>
            
            <div class="rating-alert">
                Для продолжения работы необходимо оценить всех специалистов по данной сделке
            </div>
            
            <div class="rating-user-info">
                <img id="rating-user-avatar" src="/storage/icon/profile.svg" class="rating-avatar" alt="Аватар специалиста">
                <div>
                    <div id="rating-user-name" class="rating-name">Имя специалиста</div>
                    <div id="rating-user-role" class="rating-role">Должность</div>
                </div>
            </div>
            
            <div class="rating-instruction">
                Оцените качество работы специалиста от 1 до 5 звезд
            </div>
            
            <div class="rating-stars">
                <span class="star" data-value="1"><i class="fas fa-star"></i></span>
                <span class="star" data-value="2"><i class="fas fa-star"></i></span>
                <span class="star" data-value="3"><i class="fas fa-star"></i></span>
                <span class="star" data-value="4"><i class="fas fa-star"></i></span>
                <span class="star" data-value="5"><i class="fas fa-star"></i></span>
            </div>
            
            <div class="rating-comment">
                <label for="rating-comment">Комментарий (необязательно):</label>
                <textarea id="rating-comment" placeholder="Напишите ваш комментарий..."></textarea>
            </div>
            
            <button id="submit-rating" class="btn btn-primary">Оценить</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация всплывающих подсказок Bootstrap с задержкой в 2 секунды
            if (typeof $().tooltip === 'function') {
                $('[title]').tooltip({
                    placement: 'auto', 
                    trigger: 'hover',
                    delay: {show: 1000, hide: 100}, // Задержка в 1 секунду
                    template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });
                
                // Повторная инициализация подсказок после загрузки динамического контента
                $(document).ajaxComplete(function() {
                    setTimeout(function() {
                        $('[title]').tooltip({
                            placement: 'auto',
                            trigger: 'hover',
                            delay: {show: 1000, hide: 100}, // Такая же задержка для динамически загружаемого контента
                            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                        });
                    }, 500);
                });
            }
        });
    </script>

    <!-- Улучшение скрипта для всплывающих подсказок с поддержкой HTML-контента -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация всплывающих подсказок Bootstrap с задержкой в 1 секунду
            if (typeof $().tooltip === 'function') {
                $('[title]').tooltip({
                    placement: 'auto', 
                    trigger: 'hover',
                    delay: {show: 800, hide: 100},
                    html: true,
                    template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });
                
                // Специальная инициализация для наград
                $('.award-icon').tooltip({
                    placement: 'auto',
                    delay: {show: 500, hide: 100},
                    html: true,
                    template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });
                
                // Повторная инициализация подсказок после загрузки динамического контента
                $(document).ajaxComplete(function() {
                    setTimeout(function() {
                        $('[title]').tooltip({
                            placement: 'auto',
                            trigger: 'hover',
                            delay: {show: 800, hide: 100},
                            html: true,
                            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                        });
                        
                        $('.award-icon').tooltip({
                            placement: 'auto',
                            delay: {show: 500, hide: 100},
                            html: true, 
                            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                        });
                    }, 500);
                });
            }
        });
    </script>

   

</body>

</html>
