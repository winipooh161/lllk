<div class="modal modal__deal" id="editModal" style="display: none;">
    <div class="modal-content">
        @if(isset($deal) && isset($dealFields))
            <div class="button__points">
                <span class="close-modal" id="closeModalBtn" title="Закрыть окно без сохранения изменений">&times;</span>
                <button data-target="Заказ" class="buttonSealaActive" title="Показать информацию о заказе">Заказ</button>
                <button data-target="Работа над проектом" title="Показать информацию о работе над проектом">Работа над проектом</button>
            @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                    <button data-target="Финал проекта" title="Показать информацию о финальной стадии проекта">Финал проекта</button>
            @endif
                <ul>
                    <li>
                        <a href="#" onclick="event.preventDefault(); copyRegistrationLink('{{ $deal->registration_token ? route('register_by_deal', ['token' => $deal->registration_token]) : '#' }}')" title="Скопировать регистрационную ссылку для клиента">
                            <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                        </a>
                    </li>
                    @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                        <li>
                            <a href="{{ route('deal.change_logs.deal', ['deal' => $deal->id]) }}" title="Просмотр истории изменений сделки">
                                <img src="/storage/icon/log.svg" alt="Логи">
                            </a>
                        </li>
                    @endif
                    @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                        <li>
                            <a href="{{ $deal->link ? url($deal->link) : '#' }}" title="Открыть бриф клиента">
                                <img src="/storage/icon/brif.svg" alt="Бриф клиента">
                            </a>
                        </li>
                    @endif
                    
                  
                </ul>
            </div>

           
            <!-- Форма редактирования сделки -->
            <form id="editForm" method="POST" enctype="multipart/form-data" action="{{ route('deal.update', $deal->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="deal_id" id="dealIdField" value="{{ $deal->id }}">
                @php
                    $userRole = Auth::user()->status;
                @endphp
                <!-- Модуль: Заказ -->
                <fieldset class="module__deal" id="module-zakaz"style="display: flex;"> 
                    <legend>Заказ</legend>
                    @foreach($dealFields['zakaz'] as $field)
                        <div class="form-group-deal">
                            <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                                @if(isset($field['icon']))
                                <i class="{{ $field['icon'] }}"></i>
                                @endif
                                {{ $field['label'] }}:
                                @if($field['name'] == 'client_city')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <select name="{{ $field['name'] }}" id="client_city" class="select2-field">
                                            <option value="">-- Выберите город --</option>
                                            @if(!empty($deal->client_city))
                                                <option value="{{ $deal->client_city }}" selected>{{ $deal->client_city }}</option>
                                            @endif
                                        </select>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'text')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @endif
                                @elseif($field['type'] == 'select')
                                    <!-- Для поля координатора - особая обработка -->
                                    @if($field['name'] == 'coordinator_id')
                                        @if(Auth::user()->status == 'partner')
                                            <!-- Партнер может только видеть поле координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(Auth::user()->status == 'coordinator')
                                            <!-- Координатор тоже не может менять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            <!-- Добавляем скрытое поле для сохранения значения -->
                                            <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <!-- Только администраторы могут изменять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @else
                                        <!-- Стандартное отображение для других полей select -->
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите значение --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'textarea')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @else
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @endif
                                @elseif($field['type'] == 'file')
                                    @if($field['name'] == 'avatar_path')
                                        <!-- Обычная загрузка только для аватара -->
                                        @if(!isset($field['role']) || (isset($field['role']) && in_array($userRole, $field['role'])))
                                            <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}" class="file-input">
                                            @if(!empty($deal->{$field['name']}))
                                                <div class="file-link">
                                                    <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank" title="Просмотреть загруженный аватар">
                                                        <i class="fas fa-image"></i> Просмотреть аватар
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            @if(!empty($deal->{$field['name']}))
                                                <div class="file-link">
                                                    <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотреть аватар</a>
                                                </div>
                                            @else
                                                <span class="no-file-message">Файл не загружен</span>
                                            @endif
                                        @endif
                                    @else
                                        <!-- Загрузка на Яндекс.Диск для всех остальных полей -->
                                        @php
                                            // Явно проверяем статус пользователя для coordinator и admin
                                            $canUpload = $userRole == 'coordinator' || $userRole == 'admin' || 
                                                        (!isset($field['role']) || (isset($field['role']) && in_array($userRole, $field['role'])));
                                        @endphp
                                        
                                        @if($canUpload)
                                            <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}" class="file-input yandex-upload">
                                            
                                            @php
                                                $yandexUrlField = 'yandex_url_' . $field['name'];
                                                $originalNameField = 'original_name_' . $field['name'];
                                            @endphp
                                            
                                            @if(!empty($deal->{$yandexUrlField}))
                                                <div class="file-link yandex-file-link">
                                                    <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                        <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            @php
                                                $yandexUrlField = 'yandex_url_' . $field['name'];
                                                $originalNameField = 'original_name_' . $field['name'];
                                            @endphp
                                            
                                            @if(!empty($deal->{$yandexUrlField}))
                                                <div class="file-link yandex-file-link">
                                                    <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                        <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="no-file-message">Файл не загружен</span>
                                            @endif
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'date')
                                    @if($field['name'] == 'created_date')
                                        @if(in_array($userRole, ['coordinator', 'admin']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <p class="deal-date-display">{{ \Carbon\Carbon::parse($deal->{$field['name']})->format('d.m.Y') }}</p>
                                        @endif
                                    @else
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'number')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                                    @else
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @endif
                            </label>
                        </div>
                    @endforeach
                </fieldset>
    
                <!-- Модуль: Работа над проектом -->
                <fieldset class="module__deal" id="module-rabota">
                    <legend>Работа над проектом</legend>
                    @foreach($dealFields['rabota'] as $field)
                        <div class="form-group-deal" data-select2-id="user-container-9d09g97uo" style="position: relative;">
                            <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                                @if(isset($field['icon']))
                                <i class="{{ $field['icon'] }}"></i>
                                @endif
                                {{ $field['label'] }}:
                                @if($field['name'] == 'architect_id' || $field['name'] == 'designer_id' || $field['name'] == 'visualizer_id')
                                    @if(in_array(Auth::user()->status, ['admin', 'coordinator']))
                                        @php
                                            $roleName = str_replace('_id', '', $field['name']); // architect, designer, visualizer
                                            switch($roleName) {
                                                case 'architect':
                                                    $userRole = 'architect';
                                                    $placeholderText = 'Выберите архитектора...';
                                                    break;
                                                case 'designer':
                                                    $userRole = 'designer';
                                                    $placeholderText = 'Выберите дизайнера...';
                                                    break;
                                                case 'visualizer':
                                                    $userRole = 'visualizer';
                                                    $placeholderText = 'Выберите визуализатора...';
                                                    break;
                                                default:
                                                    $userRole = $roleName;
                                                    $placeholderText = 'Выберите специалиста...';
                                            }
                                        @endphp
                                        <select name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-control select2-specialist" 
                                                data-role="{{ $userRole }}" data-placeholder="{{ $placeholderText }}">
                                            @if($deal->{$field['name']})
                                                @php
                                                    $selectedUser = \App\Models\User::find($deal->{$field['name']});
                                                @endphp
                                                @if($selectedUser)
                                                    <option value="{{ $selectedUser->id }}" selected>{{ $selectedUser->name }}</option>
                                                @endif
                                            @endif
                                        </select>
                                    @else
                                        @php
                                            $userId = $deal->{$field['name']};
                                            $userName = null;
                                            
                                            if ($userId) {
                                                $user = \App\Models\User::find($userId);
                                                $userName = $user ? $user->name : 'Не найден';
                                            } else {
                                                $userName = 'Не назначен';
                                            }
                                        @endphp
                                        <input type="text" value="{{ $userName }}" disabled class="form-control">
                                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $userId }}">
                                    @endif
                                @elseif($field['type'] == 'text')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @endif
                                @elseif($field['type'] == 'select')
                                    @if($field['name'] == 'coordinator_id')
                                        @if(Auth::user()->status == 'partner')
                                            <!-- Партнер может только видеть поле координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(Auth::user()->status == 'coordinator')
                                            <!-- Координатор тоже не может менять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                            <!-- Добавляем скрытое поле для сохранения значения -->
                                            <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <!-- Только администраторы могут изменять координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @else
                                        <!-- Стандартное отображение для других полей select -->
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите значение --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'textarea')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @else
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @endif
                                @elseif($field['type'] == 'file')
                                    <!-- Все файлы через Яндекс.Диск -->
                                    @php
                                        // Явно проверяем статус пользователя для coordinator и admin
                                        $canUpload = in_array(Auth::user()->status, ['coordinator', 'admin']) || 
                                                    (!isset($field['role']) || (isset($field['role']) && in_array($userRole, $field['role'])));
                                        
                                        // Получаем поля для Яндекс.Диска
                                        $yandexUrlField = 'yandex_url_' . $field['name'];
                                        $originalNameField = 'original_name_' . $field['name'];
                                    @endphp
                                    
                                    @if($canUpload)
                                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" 
                                               accept="{{ isset($field['accept']) ? $field['accept'] : '' }}" class="file-input yandex-upload">
                                        
                                        @if(!empty($deal->{$yandexUrlField}))
                                            <div class="file-link yandex-file-link">
                                                <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                    <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        @if(!empty($deal->{$yandexUrlField}))
                                            <div class="file-link yandex-file-link">
                                                <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                    <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @else
                                            <span class="no-file-message">Файл не загружен</span>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'date')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                    @else
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'number')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                                    @else
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @endif
                            </label>
                        </div>
                    @endforeach
                </fieldset>
    
                <!-- Модуль Финал проекта только для координаторов и администраторов -->
                @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                    <!-- Модуль: Финал проекта -->
                    <fieldset class="module__deal" id="module-final">
                        <legend>Финал проекта</legend>
                        @foreach($dealFields['final'] as $field)
                            <div class="form-group-deal {{ $field['name'] == 'coordinator_id' ? 'field-coordinator-id' : '' }} {{ $field['name'] == 'office_partner_id' ? 'field-office-partner-id' : '' }}">
                                <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                                    @if(isset($field['icon']))
                                    <i class="{{ $field['icon'] }}"></i>
                                    @endif
                                    {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}:
                                    @if($field['type'] == 'file')
                                        {{-- Исправляем условие для файлов финального раздела --}}
                                        {{-- Если текущий пользователь админ или координатор, показываем интерфейс загрузки файла --}}
                                        @if(in_array(Auth::user()->status, ['admin', 'coordinator']))
                                            <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" 
                                                   {{ isset($field['required']) && $field['required'] ? 'required' : '' }} 
                                                   {{ isset($field['accept']) ? 'accept='.$field['accept'] : '' }} 
                                                   class="file-input yandex-upload">
                                            
                                            @php
                                                $yandexUrlField = 'yandex_url_' . $field['name'];
                                                $originalNameField = 'original_name_' . $field['name'];
                                            @endphp
                                            
                                            @if(!empty($deal->{$yandexUrlField}))
                                                <div class="file-link yandex-file-link">
                                                    <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                        <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            {{-- Для других пользователей только просмотр существующего файла --}}
                                            @php
                                                $yandexUrlField = 'yandex_url_' . $field['name'];
                                                $originalNameField = 'original_name_' . $field['name'];
                                            @endphp
                                            
                                            @if(!empty($deal->{$yandexUrlField}))
                                                <div class="file-link yandex-file-link">
                                                    <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                        <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="no-file-message">Файл не загружен</span>
                                            @endif
                                        @endif
                                    @elseif($field['type'] == 'text')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }} class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'select')
                                        @if($field['name'] == 'coordinator_id')
                                            @if(Auth::user()->status == 'partner')
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field">
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="read-only-hint">Только для чтения</span>
                                            @elseif(Auth::user()->status == 'coordinator')
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field">
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="read-only-hint">Только для чтения</span>
                                                <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                            @else
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        @else
                                            @if(isset($field['role']) && in_array($userRole, $field['role']))
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                    <option value="">-- Выберите {{ strtolower($field['label'] ?? '') }} --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field">
                                                    <option value="">-- Выберите {{ strtolower($field['label'] ?? '') }} --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="read-only-hint">Только для чтения</span>
                                            @endif
                                        @endif
                                    @elseif($field['type'] == 'textarea')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                        @else
                                            <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled class="read-only-field" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'file')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['accept']) ? 'accept='.$field['accept'] : '' }} class="file-input yandex-upload">
                                            
                                            @php
                                                $yandexUrlField = 'yandex_url_' . $field['name'];
                                                $originalNameField = 'original_name_' . $field['name'];
                                            @endphp
                                            
                                            @if(!empty($deal->{$yandexUrlField}))
                                                <div class="file-link yandex-file-link">
                                                    <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                        <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            @php
                                                $yandexUrlField = 'yandex_url_' . $field['name'];
                                                $originalNameField = 'original_name_' . $field['name'];
                                            @endphp
                                            
                                            @if(!empty($deal->{$yandexUrlField}))
                                                <div class="file-link yandex-file-link">
                                                    <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                                        <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="no-file-message">Файл не загружен</span>
                                            @endif
                                        @endif
                                    @elseif($field['type'] == 'date')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                        @else
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'number')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['step']) ? 'step='.$field['step'] : '' }}>
                                        @else
                                            <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field" {{ isset($field['step']) ? 'step='.$field['step'] : '' }}>
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'email')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="email" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                        @else
                                            <input type="email" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @elseif($field['type'] == 'url')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="url" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }}>
                                        @else
                                            <input type="url" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled class="read-only-field">
                                            <span class="read-only-hint">Только для чтения</span>
                                        @endif
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </fieldset>
                @endif
                <div class="form-buttons">
                    <button type="submit" id="saveButton" title="Сохранить все изменения сделки">Сохранить</button>
                    
                    <!-- Добавляем кнопку удаления для администратора -->
                    @if (Auth::user()->status == 'admin')
                        <button type="button" class="delete-deal-button" onclick="confirmDeleteDeal({{ $deal->id }})" title="Удалить сделку">
                            Удалить сделку
                        </button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>

<!-- Добавляем стили для кнопок удаления -->
<style>
    .delete-deal-btn img {
        width: 24px;
        height: 24px;
    }
    
    .delete-deal-button {
        background: #fff;
        color: #e74c3c;
        border: 1px solid #e74c3c;
        border-radius: 4px;
        padding: 10px 15px;
        margin-left: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .delete-deal-button:hover {
        background: #e74c3c !important;
        color: #fff;
    }
    
    /* Стили для поля имени клиента */
    input[name="client_name"] {
        font-weight: 500;
        border-color: #3498db;
    }
    
    input[name="client_name"]:focus {
        border-color: #2980b9;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    /* Группировка полей информации о клиенте */
    .form-group-deal label[for="client_name"],
    .form-group-deal label[for="client_phone"],
    .form-group-deal label[for="client_email"] {
        color: #3498db;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Функция копирования регистрационной ссылки
        window.copyRegistrationLink = function(regUrl) {
            if (regUrl && regUrl !== '#') {
                navigator.clipboard.writeText(regUrl).then(function() {
                    alert('Регистрационная ссылка скопирована: ' + regUrl);
                }).catch(function(err) {
                    console.error('Ошибка копирования ссылки: ', err);
                });
            } else {
                alert('Регистрационная ссылка отсутствует.');
            }
        };
        
        // Загрузка городов из JSON-файла для селекта
        if(document.getElementById('client_city')) {
            // Проверяем наличие jQuery перед использованием
            if (typeof $ === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js';
                script.onload = initializeSelect2;
                document.head.appendChild(script);
            } else {
                initializeSelect2();
            }
            
            function initializeSelect2() {
                // Проверяем наличие Select2 перед использованием
                if (typeof $.fn.select2 === 'undefined') {
                    var linkElement = document.createElement('link');
                    linkElement.rel = 'stylesheet';
                    linkElement.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
                    document.head.appendChild(linkElement);
                    
                    var script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                    script.onload = loadCitiesAndInitSelect2;
                    document.head.appendChild(script);
                } else {
                    loadCitiesAndInitSelect2();
                }
            }
            
            function loadCitiesAndInitSelect2() {
                $.getJSON('/public/cities.json', function(data) {
                    // Группируем города по региону
                    var groupedOptions = {};
                    data.forEach(function(item) {
                        var region = item.region;
                        var city = item.city;
                        if (!groupedOptions[region]) {
                            groupedOptions[region] = [];
                        }
                        // Форматируем данные для Select2
                        groupedOptions[region].push({
                            id: city,
                            text: city
                        });
                    });

                    // Преобразуем сгруппированные данные в массив для Select2
                    var select2Data = [];
                    for (var region in groupedOptions) {
                        select2Data.push({
                            text: region,
                            children: groupedOptions[region]
                        });
                    }

                    // Инициализируем Select2 с полученными данными и настройками доступности
                    $('#client_city').select2({
                        data: select2Data,
                        placeholder: "-- Выберите город --",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editModal'),
                        // Добавляем обработчики для доступности
                        closeOnSelect: true
                    }).on('select2:open', function() {
                        // Исправление проблемы aria-hidden
                        document.querySelector('.select2-container--open').setAttribute('inert', 'false');
                        
                        // Удаляем aria-hidden с dropdown контейнера и родителей
                        setTimeout(function() {
                            var dropdowns = document.querySelectorAll('.select2-dropdown');
                            dropdowns.forEach(function(dropdown) {
                                var parent = dropdown.parentElement;
                                while (parent) {
                                    if (parent.hasAttribute('aria-hidden')) {
                                        parent.removeAttribute('aria-hidden');
                                    }
                                    parent = parent.parentElement;
                                }
                            });
                            
                            // Дополнительно для модального окна
                            if (document.querySelector('.modal[aria-hidden="true"]')) {
                                document.querySelector('.modal[aria-hidden="true"]').removeAttribute('aria-hidden');
                            }
                        }, 10);
                    });
                    
                    // Если город уже был выбран, устанавливаем его
                    var currentCity = "{{ $deal->client_city ?? '' }}";
                    if(currentCity) {
                        // Создаем новый элемент option и добавляем к select
                        if($("#client_city").find("option[value='" + currentCity + "']").length === 0) {
                            var newOption = new Option(currentCity, currentCity, true, true);
                            $('#client_city').append(newOption);
                        }
                        $('#client_city').val(currentCity).trigger('change');
                    }
                })
                .fail(function(jqxhr, textStatus, error) {
                    console.error("Ошибка загрузки JSON файла городов: " + textStatus + ", " + error);
                    // Добавляем резервное решение при ошибке загрузки JSON
                    var currentCity = "{{ $deal->client_city ?? '' }}";                    if(currentCity) {                        var option = new Option(currentCity, currentCity, true, true);                        $('#client_city').append(option).trigger('change');
                    }
                });
            }
        }
        
        // Обработчик закрытия модального окна - убираем возможные остатки aria-hidden
        document.getElementById('closeModalBtn').addEventListener('click', function() {
            // Очистка атрибутов aria-hidden при закрытии
            var modal = document.getElementById('editModal');
            if (modal.hasAttribute('aria-hidden')) {
                modal.removeAttribute('aria-hidden');
            }
        });

        // Отслеживаем изменение статуса сделки на "Проект завершен"
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            const originalStatus = statusSelect.value;
            
            // При отправке формы проверяем, изменился ли статус на "Проект завершен"
            document.getElementById('editForm').addEventListener('submit', function(e) {
                if (statusSelect.value === 'Проект завершен' && originalStatus !== 'Проект завершен') {
                    // Сохраняем ID сделки для последующей проверки оценок
                    localStorage.setItem('completed_deal_id', '{{ $deal->id }}');
                    console.log('[Модальное окно] Сделка переведена в статус "Проект завершен", ID:', '{{ $deal->id }}');
                }
            });
        }

        // При открытии модального окна проверяем, нужно ли показать окно оценки
        const dealId = '{{ $deal->id }}';
        const dealStatus = '{{ $deal->status }}';
        const userStatus = '{{ Auth::user()->status }}';

        // Проверяем завершенные сделки для пользователей, которые должны оценивать
        if (dealStatus === 'Проект завершен' && 
            ['coordinator', 'partner', 'client', 'user'].includes(userStatus)) {
            console.log('[Модальное окно] Проверка оценок для завершенной сделки:', dealId);
            setTimeout(() => {
                if (typeof window.runRatingCheck === 'function') {
                    window.runRatingCheck(dealId);
                } else if (typeof window.checkPendingRatings === 'function') {
                    window.checkPendingRatings(dealId);
                } else {
                    console.error('[Модальное окно] Функции проверки рейтингов не найдены!');
                }
            }, 1000);
        }

        // Если есть ID завершенной сделки в localStorage, проверяем оценки
        const completedDealId = localStorage.getItem('completed_deal_id');
        if (completedDealId && 
            ['coordinator', 'partner', 'client', 'user'].includes(userStatus)) {
            console.log('[Модальное окно] Проверка оценок для сделки из localStorage:', completedDealId);
            setTimeout(() => {
                if (typeof window.runRatingCheck === 'function') {
                    window.runRatingCheck(completedDealId);
                } else if (typeof window.checkPendingRatings === 'function') {
                    window.checkPendingRatings(completedDealId);
                    localStorage.removeItem('completed_deal_id');
                } else {
                    console.error('[Модальное окно] Функции проверки рейтингов не найдены!');
                }
            }, 1500);
        }

        // Инициализация кастомных файловых инпутов
        function initCustomFileInputs() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                const originalLabel = input.parentNode.querySelector('label');
                const fieldName = input.id;
                const fileType = getFileType(input.accept);
                
                // Создаем новый контейнер для кастомного инпута
                const customContainer = document.createElement('div');
                customContainer.className = `custom-file-upload ${fileType}`;
                customContainer.id = `${fieldName}-container`;
                
                // Создаем лейбл для кнопки выбора файла
                const customLabel = document.createElement('label');
                customLabel.setAttribute('for', fieldName);
                
                // Определяем иконку в зависимости от типа файла
                let fileIcon = 'fa-file-upload';
                if (fileType === 'pdf') fileIcon = 'fa-file-pdf';
                else if (fileType === 'image') fileIcon = 'fa-file-image';
                
                customLabel.innerHTML = `<i class="fas ${fileIcon}"></i> Выбрать файл`;
                
                // Создаем контейнер для превью файла
                const previewContainer = document.createElement('div');
                previewContainer.className = 'file-preview';
                previewContainer.innerHTML = `
                    <i class="fas ${fileIcon}"></i>
                    <span class="file-name"></span>
                    <span class="remove-file"><i class="fas fa-times"></i></span>
                `;
                
                // Заменяем оригинальный контейнер на наш кастомный
                input.parentNode.insertBefore(customContainer, input);
                customContainer.appendChild(input);
                customContainer.appendChild(customLabel);
                customContainer.appendChild(previewContainer);
                
                // Если файл уже был загружен, показываем его в превью
                const fileLink = input.parentNode.querySelector('.file-link');
                if (fileLink) {
                    const fileName = fileLink.querySelector('a').textContent;
                    showFilePreview(input, fileName);
                }
                
                // Добавляем обработчики событий
                input.addEventListener('change', function() {
                    const fileName = this.files.length > 0 ? this.files[0].name : '';
                    showFilePreview(this, fileName);
                });
                
                // Добавляем обработчик для удаления файла
                const removeButton = previewContainer.querySelector('.remove-file');
                removeButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    input.value = '';
                    previewContainer.classList.remove('visible');
                });
            });
        }
        
        // Определяем тип файла по accept атрибуту
        function getFileType(accept) {
            if (!accept) return '';
            if (accept.includes('pdf')) return 'pdf';
            if (accept.includes('image')) return 'image';
            return '';
        }
        
        // Показываем превью выбранного файла
        function showFilePreview(input, fileName) {
            const container = input.closest('.custom-file-upload');
            const preview = container.querySelector('.file-preview');
            const nameElement = preview.querySelector('.file-name');
            
            if (fileName) {
                nameElement.textContent = fileName;
                preview.classList.add('visible');
            } else {
                preview.classList.remove('visible');
            }
        }
        
        // Вызываем инициализацию при загрузке модального окна
        $('#dealModalContainer').on('DOMNodeInserted', '#editModal', function() {
            setTimeout(function() {
                initCustomFileInputs();
            }, 100);
        });

        // Инициализация всплывающих подсказок
        if (typeof $().tooltip === 'function') {
            $('[title]').tooltip({
                placement: 'auto',
                trigger: 'hover',
                delay: {show: 1500, hide: 0}, // Changed to 1.5 seconds with no hide delay
                animation: false, // Disable animations
                container: 'body', // Ensure proper positioning
                template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });
        }
    });
</script>

<!-- Добавляем улучшенные скрипты для Select2 -->
<script>
$(function() {
    // Инициализация поиска городов с использованием cities.json
    function initCitySearch() {
        $.getJSON('/cities.json', function(data) {
            // Группируем города по региону
            var grouped = {};
            // Индексируем города по регионам
            $.each(data, function(i, item) {
                // Проверяем наличие региона, иначе используем "Другие города"
                var region = item.region || "Другие города";
                grouped[region] = grouped[region] || [];
                grouped[region].push({
                    id: item.city,
                    text: item.city,
                    region: region
                });
            });
            
            // Преобразуем в формат для Select2
            var selectData = $.map(grouped, function(cities, region) {
                return {
                    text: region,
                    children: cities
                };
            });
            
            // Инициализация Select2 для полей с городами
            $('#client_timezone, #client_city, #cityField').each(function() {
                var $select = $(this);
                var selectedCity = $select.data('selected') || $select.val();
                
                // Находим правильный родительский элемент
                var $parent = $select.closest('.form-group-deal');
                if (!$parent.length) {
                    $parent = $select.parent();
                }
                
                // Устанавливаем position: relative для корректного позиционирования dropdown
                $parent.css('position', 'relative');
                
                $select.select2({
                    data: selectData,
                    placeholder: "Начните вводить название города или региона...",
                    allowClear: true,
                    minimumInputLength: 2,
                    dropdownParent: $parent, // Используем родительский элемент
                    templateResult: formatCityResult,
                    templateSelection: formatCitySelection,
                    matcher: customCityMatcher
                });
                
                // Устанавливаем выбранное значение, если оно есть
                if (selectedCity) {
                    var cityObj = findCityById(selectData, selectedCity);
                    if (cityObj) {
                        var option = new Option(cityObj.text, cityObj.id, true, true);
                        $select.append(option).trigger('change');
                    }
                }
                
                // Обработчик изменения города для сохранения ID
                $select.on('select2:select', function(e) {
                    var data = e.params.data;
                    if (data && data.id) {
                        $('#client_city_id').val(data.id);
                    }
                });
                
                $select.on('select2:clear', function() {
                    $('#client_city_id').val('');
                });
            });
        }).fail(function(err) {
            console.error("Ошибка загрузки городов", err);
        });
    }
    
    // Форматирование строки результата поиска города
    function formatCityResult(city) {
        if (!city.id) return city.text;
        return $('<span><strong>' + city.text + '</strong>' + (city.region ? ' <small>(' + city.region + ')</small>' : '') + '</span>');
    }
    
    // Форматирование выбранного города
    function formatCitySelection(city) {
        if (!city.id) return city.text;
        return city.text;
    }
    
    // Улучшенный поиск совпадений для городов, включая поиск по регионам
    function customCityMatcher(params, data) {
        // Если нет поискового запроса, возвращаем все элементы
        if ($.trim(params.term) === '') {
            return data;
        }
        
        // Проверка текущего уровня в иерархии данных
        if (data.children && data.children.length) {
            // Ищем совпадения в регионе
            var regionMatch = data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1;
            
            // Клонируем объект для избежания изменения оригинала
            var match = $.extend(true, {}, data);
            match.children = [];
            
            // Если регион совпадает, возвращаем все города из него
            if (regionMatch) {
                match.children = data.children;
                return match;
            }
            
            // Проверяем каждый город в регионе
            for (var c = 0; c < data.children.length; c++) {
                var child = data.children[c];
                var matches = customCityMatcher(params, child);
                
                if (matches) {
                    match.children.push(matches);
                }
            }
            
            // Если нашли совпадения в детях, возвращаем объект
            if (match.children.length > 0) {
                return match;
            }
            
        } else {
            // Ищем совпадения в городе
            if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                return data;
            }
        }
        
        return null;
    }
    
    // Функция для поиска города по его ID в исходных данных
    function findCityById(data, cityId) {
        for (var g = 0; g < data.length; g++) {
            var group = data[g];
            if (group.children) {
                for (var c = 0; c < group.children.length; c++) {
                    if (group.children[c].id === cityId) {
                        return group.children[c];
                    }
                }
            }
        }
        return null;
    }

    // Инициализация поиска пользователей через AJAX для полей сотрудников
    function initUserSearch() {
        // Списки полей сотрудников и их статусы
        var userFields = [
            { selector: '#architect_id', status: 'architect' },
            { selector: '#designer_id', status: 'designer' },
            { selector: '#visualizer_id', status: 'visualizer' },
            { selector: '#coordinator_id', status: 'coordinator' },
            { selector: '#office_partner_id', status: 'partner' },
            { selector: '#responsiblesField', status: ['coordinator', 'architect', 'designer', 'visualizer', 'partner'] }
        ];
        
        userFields.forEach(function(field) {
            var $select = $(field.selector);
            if (!$select.length) return;
            
            // Находим подходящий родительский элемент
            var $parent = $select.closest('.form-group-deal');
            if (!$parent.length) {
                $parent = $select.parent();
            }
            
            // Устанавливаем position: relative
            $parent.css('position', 'relative');
            
            var isMultiple = $select.prop('multiple');
            var preselectedData = [];
            var preselectedIds = isMultiple 
                ? $select.val() 
                : ($select.val() ? [$select.val()] : []);
            
            // Если есть предварительно выбранные значения - загружаем их данные
            if (preselectedIds.length) {
                preselectedIds.forEach(function(userId) {
                    $.ajax({
                        url: '/get-user/' + userId,
                        type: 'GET',
                        async: false,  // Синхронный запрос для загрузки начальных данных
                        success: function(userData) {
                            preselectedData.push({
                                id: userData.id,
                                text: userData.name,
                                email: userData.email,
                                status: userData.status,
                                avatar_url: userData.avatar_url || '/storage/default-avatar.png'
                            });
                        },
                        error: function() {
                            console.error('Не удалось загрузить данные пользователя ID: ' + userId);
                        }
                    });
                });
            }
            
            $select.select2({
                placeholder: 'Начните вводить имя сотрудника...',
                allowClear: true,
                minimumInputLength: 2,
                dropdownParent: $parent, // Используем родительский элемент
                templateResult: formatUserResult,
                templateSelection: formatUserSelection,
                multiple: isMultiple,
                ajax: {
                    url: '/search-users',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term,
                            status: field.status,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(data, function(user) {
                                return {
                                    id: user.id,
                                    text: user.name,
                                    email: user.email,
                                    status: user.status,
                                    avatar_url: user.avatar_url || '/storage/default-avatar.png'
                                };
                            }),
                            pagination: {
                                more: false
                            }
                        };
                    },
                    cache: true
                }
            });
            
            // Устанавливаем предварительно выбранные значения после инициализации
            if (preselectedData.length) {
                preselectedData.forEach(function(item) {
                    // Создаем новую опцию и добавляем в Select2
                    var option = new Option(item.text, item.id, true, true);
                    $select.append(option);
                });
                
                // Триггерим событие change для Select2
                $select.trigger('change');
            }
        });
    }
    
    // Форматирование результата поиска пользователя
    function formatUserResult(user) {
        if (!user.id) return user.text;
        
        var $result = $(
            '<div class="select2-user-result">' +
                '<div class="select2-user-avatar">' +
                    '<img src="' + (user.avatar_url || '/storage/default-avatar.png') + '" alt="Avatar" />' +
                '</div>' +
                '<div class="select2-user-info">' +
                    '<div class="select2-user-name">' + user.text + '</div>' +
                    '<div class="select2-user-details">' +
                        (user.email ? '<span class="select2-user-email">' + user.email + '</span>' : '') +
                        (user.status ? '<span class="select2-user-status">' + formatUserStatus(user.status) + '</span>' : '') +
                    '</div>' +
                '</div>' +
            '</div>'
        );
        
        return $result;
    }
    
    // Форматирование выбранного пользователя
    function formatUserSelection(user) {
        if (!user.id) return user.text;
        return user.text + (user.status ? ' (' + formatUserStatus(user.status) + ')' : '');
    }
    
    // Форматирование статуса пользователя для отображения
    function formatUserStatus(status) {
        var statusLabels = {
            'admin': 'Администратор',
            'coordinator': 'Координатор',
            'architect': 'Архитектор',
            'designer': 'Дизайнер',
            'visualizer': 'Визуализатор',
            'partner': 'Партнер',
            'client': 'Клиент',
            'user': 'Пользователь'
        };
        return statusLabels[status] || status;
    }
    
    // Инициализация при загрузке страницы
    $(document).ready(function() {
        // Устанавливаем таймаут для правильной инициализации модального окна
        setTimeout(function() {
            initCitySearch();
            initUserSelect2();
        }, 100);
    });

    // Функция для инициализации всех остальных Select2-полей
    function initUserSelect2() {
        // Инициализация Select2 для всех селектов внутри модального окна
        $('#editModal .select2-field').each(function() {
            var $this = $(this);
            
            if (!$this.data('select2')) {
                $this.select2({
                    dropdownParent: $('#editModal').find('.modal-content'),
                    placeholder: $this.attr('placeholder') || "Выберите значение",
                    allowClear: true,
                    width: '100%',
                    language: 'ru'
                });
            }
        });
        
        // Вызываем функцию поиска пользователей после инициализации
        initUserSearch();
    }

    // Обработчик события открытия модального окна
    $(document).on('shown.bs.modal', '#editModal', function() {
        // Переинициализируем Select2 после полного открытия модального окна
        initUserSelect2();
    });
});
</script>

<!-- Обновляем инициализацию Select2, чтобы dropdown находился именно в родительском .form-group-deal -->
<script>
// Улучшенная функция инициализации Select2
function initSelect2() {
    // Инициализация для полей с городами и пользователями
    $('.select2-field:not(.select2-hidden-accessible)').each(function() {
        // Находим родительский контейнер для этого конкретного поля
        var $parent = $(this).closest('.form-group-deal');
        if (!$parent.length) {
            $parent = $(this).parent();
        }
        
        // Установка position: relative для правильного позиционирования
        $parent.css('position', 'relative');
        
        // Устанавливаем data-select2-id для родителя, если его нет
        if (!$parent.attr('data-select2-id')) {
            $parent.attr('data-select2-id', 'container-' + Math.random().toString(36).substr(2, 9));
        }
        
        // Инициализация Select2 с указанием dropdownParent
        $(this).select2({
            width: '100%',
            placeholder: $(this).attr('placeholder') || "Выберите значение",
            allowClear: true,
            dropdownParent: $parent, // Важно: устанавливаем родителя для dropdown
            language: 'ru'
        });
    });
    
    // Специфичная инициализация для поиска городов
    initCitySearch();
    
    // Специфичная инициализация для поиска пользователей
    initUserSearch();
}

// Функция для инициализации поиска городов
function initCitySearch() {
    $.getJSON('/cities.json', function(data) {
        // Обработка данных городов
        // ...existing code...
        
        $('#client_timezone, #client_city, #cityField').each(function() {
            var $select = $(this);
            if ($select.data('select2')) {
                $select.select2('destroy');
            }
            
            // Находим родительский контейнер
            var $parent = $select.closest('.form-group-deal');
            if (!$parent.length) {
                $parent = $select.parent();
            }
            
            // Установка position: relative для родителя
            $parent.css('position', 'relative');
            
            // Устанавливаем уникальный data-select2-id для родителя
            if (!$parent.attr('data-select2-id')) {
                $parent.attr('data-select2-id', 'city-container-' + Math.random().toString(36).substr(2, 9));
            }
            
            // Инициализация с правильным родителем
            $select.select2({
                // ...existing code...
                dropdownParent: $parent // Важно: устанавливаем родителя для dropdown
                // ...existing code...
            });
            
            // ...existing code...
        });
    });
}

// Функция инициализации поиска пользователей
function initUserSearch() {
    // Списки полей сотрудников и их статусы
    var userFields = [
        { selector: '#architect_id', status: 'architect' },
        { selector: '#designer_id', status: 'designer' },
        { selector: '#visualizer_id', status: 'visualizer' },
        { selector: '#coordinator_id', status: 'coordinator' },
        { selector: '#office_partner_id', status: 'partner' },
        { selector: '#responsiblesField', status: ['coordinator', 'architect', 'designer', 'visualizer', 'partner'] }
    ];
    
    userFields.forEach(function(field) {
        var $select = $(field.selector);
        if (!$select.length) return;
        
        if ($select.data('select2')) {
            $select.select2('destroy');
        }
        
        // Находим родительский контейнер
        var $parent = $select.closest('.form-group-deal');
        if (!$parent.length) {
            $parent = $select.parent();
        }
        
        // Установка position: relative для родителя
        $parent.css('position', 'relative');
        
        // Устанавливаем уникальный data-select2-id для родителя
        if (!$parent.attr('data-select2-id')) {
            $parent.attr('data-select2-id', 'user-container-' + Math.random().toString(36).substr(2, 9));
        }
        
        // ...existing code...
        
        $select.select2({
            // ...existing code...
            dropdownParent: $parent, // Важно: устанавливаем родителя для dropdown
            // ...existing code...
        });
        
        // ...existing code...
    });
}

// Инициализация после загрузки DOM
$(document).ready(function() {
    // Задержка для обеспечения полной загрузки DOM
    setTimeout(function() {
        initSelect2();
    }, 100);
    
    // Также инициализируем при открытии модального окна
    $(document).on('shown.bs.modal', '#editModal', function() {
        setTimeout(function() {
            initSelect2();
        }, 200);
    });
});
</script>

<!-- Добавляем скрытое поле для хранения ID города -->
<input type="hidden" id="client_city_id" name="client_city_id" value="{{ $deal->client_city_id ?? '' }}">

<!-- Остальной HTML-код модального окна остается без изменений -->

<!-- Улучшенные скрипты инициализации Select2 -->
<script>
$(function() {
    // Функция для инициализации Select2 с правильными настройками позиционирования
    function initializeSelect2(selector, options) {
        $(selector).each(function() {
            var $select = $(this);
            
            // Проверяем, не был ли уже инициализирован Select2
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            
            // Находим родительский контейнер
            var $parent = $select.closest('.form-group-deal');
            if (!$parent.length) {
                $parent = $select.parent();
            }
            
            // Устанавливаем стили для родительского контейнера
            $parent.css({
                'position': 'relative',
                'overflow': 'visible',
                'width': '100%'
            });
            
            // Сохраняем ширину родительского контейнера
            var parentWidth = $parent.width();
            
            // Добавляем уникальный идентификатор
            var uniqueId = 'select2-parent-' + Math.random().toString(36).substr(2, 9);
            $parent.attr('data-select2-id', uniqueId);
            
            // Объединяем настройки по умолчанию с переданными опциями
            var defaultOptions = {
                width: '100%',
                dropdownParent: $parent,
                dropdownCssClass: 'select2-dropdown-fixed-width'
            };
            
            var selectOptions = $.extend({}, defaultOptions, options || {});
            
            // Инициализируем Select2
            $select.select2(selectOptions);
            
            // Устанавливаем корректную ширину выпадающего списка после открытия
            $select.on('select2:open', function() {
                setTimeout(function() {
                    $('.select2-container--open .select2-dropdown').css({
                        'width': parentWidth + 'px',
                        'min-width': parentWidth + 'px',
                        'max-width': parentWidth + 'px'
                    });
                }, 0);
            });
        });
    }

    // Инициализация поиска городов
    function initCitySearch() {
        $.getJSON('/cities.json', function(data) {
            // ...existing code...
            
            // Инициализация Select2 для полей с городами
            initializeSelect2('#client_timezone, #client_city, #cityField', {
                data: selectData,
                placeholder: "Начните вводить название города...",
                allowClear: true,
                minimumInputLength: 2,
                templateResult: formatCityResult,
                templateSelection: formatCitySelection,
                matcher: customCityMatcher
            });
        });
    }

    // Инициализация поиска пользователей
    function initUserSearch() {
        var userFields = [
            { selector: '#architect_id', status: 'architect' },
            { selector: '#designer_id', status: 'designer' },
            { selector: '#visualizer_id', status: 'visualizer' },
            { selector: '#coordinator_id', status: 'coordinator' },
            { selector: '#office_partner_id', status: 'partner' },
            { selector: '#responsiblesField', status: ['coordinator', 'architect', 'designer', 'visualizer', 'partner'] }
        ];
        
        userFields.forEach(function(field) {
            // ...existing code...
            
            initializeSelect2(field.selector, {
                placeholder: 'Начните вводить имя сотрудника...',
                allowClear: true,
                minimumInputLength: 2,
                templateResult: formatUserResult,
                templateSelection: formatUserSelection,
                multiple: isMultiple,
                ajax: {
                    // ...existing code...
                }
            });
        });
    }

    // Инициализация всех остальных Select2 полей
    function initAllSelect2() {
        initializeSelect2('.select2-field:not(.select2-hidden-accessible)', {
            placeholder: "Выберите значение",
            allowClear: true
        });
    }

    // Запускаем инициализацию при загрузке страницы
    $(document).ready(function() {
        setTimeout(function() {
            initCitySearch();
            initUserSearch();
            initAllSelect2();
        }, 100);
    });

    // Обработчик события открытия модального окна
    $(document).on('shown.bs.modal', '#editModal', function() {
        setTimeout(function() {
            initCitySearch();
            initUserSearch();
            initAllSelect2();
        }, 200);
    });
    
    // Обработчик изменения размера окна
    $(window).on('resize', function() {
        if ($('.select2-container--open').length) {
            $('.select2-hidden-accessible').select2('close');
            setTimeout(function() {
                initAllSelect2();
            }, 100);
        }
    });
});
</script>

<!-- Добавляем JavaScript для подтверждения удаления -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...
    
    // Проверяем, существует ли глобальная функция confirmDeleteDeal
    if (typeof window.confirmDeleteDeal !== 'function') {
        // Определяем функцию только если она еще не определена глобально
        window.confirmDeleteDeal = function(dealId) {
            if (confirm('ВНИМАНИЕ! Вы собираетесь удалить сделку. Это действие нельзя отменить.\n\nСвязи с брифами и другими элементами будут сохранены.\n\nВы уверены, что хотите удалить эту сделку?')) {
                console.log('Отправка запроса на удаление сделки #' + dealId);
                
                // Создаем форму для отправки запроса методом DELETE
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/deal/${dealId}/delete`;
                form.style.display = 'none';
                
                // Добавляем CSRF токен
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
                
                // Добавляем метод DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                // Добавляем форму в документ и отправляем
                document.body.appendChild(form);
                form.submit();
                
                // Показываем индикатор загрузки
                const loadingScreen = document.createElement('div');
                loadingScreen.style.position = 'fixed';
                loadingScreen.style.top = '0';
                loadingScreen.style.left = '0';
                loadingScreen.style.width = '100%';
                loadingScreen.style.height = '100%';
                loadingScreen.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
                loadingScreen.style.display = 'flex';
                loadingScreen.style.justifyContent = 'center';
                loadingScreen.style.alignItems = 'center';
                loadingScreen.style.zIndex = '9999';
                loadingScreen.innerHTML = '<div>Удаление сделки...</div>';
                document.body.appendChild(loadingScreen);
            }
        };
    }
});
</script>
