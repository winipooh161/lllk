(function(){document.addEventListener("DOMContentLoaded",function(){console.log("[Рейтинги] Инициализация системы рейтингов");const s=document.getElementById("rating-modal");if(!s){console.error("[Рейтинги] Не найден элемент модального окна #rating-modal");return}const a=s.querySelectorAll(".rating-stars .star"),c=document.getElementById("submit-rating");let r=0,d=[],l=0,u=null;function S(){console.log("[Рейтинги] Проверка необходимости оценки при загрузке страницы"),v().then(e=>{if(console.log("[Рейтинги] Получены сделки, требующие оценки:",e),e&&e.length>0)for(const o of e)y(o);const t=m();if(console.log("[Рейтинги] ID завершенных сделок из localStorage после обновления:",t),t.length>0){const o=t[0];h(o).then(n=>{console.log("[Рейтинги] Проверка существования сделки:",o,"Результат:",n),n?(console.log("[Рейтинги] Запуск проверки оценок для сделки:",o),typeof window.checkPendingRatings=="function"?window.checkPendingRatings(o):(console.warn("[Рейтинги] Функция checkPendingRatings не найдена, попытка инициализации через таймаут"),setTimeout(()=>{typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Функция найдена после таймаута, запуск"),window.checkPendingRatings(o)):console.error("[Рейтинги] Функция checkPendingRatings не определена при загрузке после таймаута")},1e3))):(console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",o),f(o),S())})}else console.log("[Рейтинги] Нет сохраненных ID завершенных сделок")}).catch(e=>{console.error("[Рейтинги] Ошибка при получении списка завершенных сделок:",e)})}function v(){return console.log("[Рейтинги] Запрос списка завершенных сделок, требующих оценки"),new Promise((e,t)=>{var n;const o=(n=document.querySelector('meta[name="csrf-token"]'))==null?void 0:n.getAttribute("content");fetch("/ratings/find-completed-deals",{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":o,Accept:"application/json"},credentials:"same-origin"}).then(i=>{if(!i.ok)throw new Error(`HTTP error! Status: ${i.status}`);return i.json()}).then(i=>{console.log("[Рейтинги] Получен ответ с списком сделок:",i),e(i.deals||[])}).catch(i=>{console.error("[Рейтинги] Ошибка при запросе списка завершенных сделок:",i),t(i)})})}function m(){const e=localStorage.getItem("completed_deal_ids");return e?JSON.parse(e):[]}function y(e){const t=m();t.includes(e)||(t.push(e),localStorage.setItem("completed_deal_ids",JSON.stringify(t)))}function f(e){const o=m().filter(n=>n!==e);localStorage.setItem("completed_deal_ids",JSON.stringify(o)),localStorage.removeItem(`pending_ratings_${e}`)}function _(){document.body.classList.add("rating-in-progress"),document.addEventListener("keydown",k),window.onbeforeunload=function(){return"Пожалуйста, оцените всех специалистов перед закрытием страницы."}}function k(e){if(e.key==="Escape"||e.key==="Tab"){e.preventDefault();const t=document.querySelector(".rating-alert");t&&(t.style.animation="none",setTimeout(()=>{t.style.animation="rating-alert-flash 0.5s ease-in-out"},10))}}function E(){document.body.classList.remove("rating-in-progress"),document.removeEventListener("keydown",k),window.onbeforeunload=null,localStorage.removeItem("pendingRatingsState")}function T(){localStorage.setItem("pendingRatingsState",JSON.stringify({pendingRatings:d,currentIndex:l,dealId:u}))}a.forEach(e=>{e.addEventListener("mouseover",function(){const t=parseInt(this.dataset.value);p(t)}),e.addEventListener("mouseout",function(){p(r)}),e.addEventListener("click",function(){r=parseInt(this.dataset.value),p(r)})});function p(e){a.forEach(t=>{parseInt(t.dataset.value)<=e?t.classList.add("active"):t.classList.remove("active")})}c&&c.addEventListener("click",function(){if(r===0){const n=document.querySelector(".rating-alert");n.textContent="Пожалуйста, выберите оценку от 1 до 5 звезд!",n.style.backgroundColor="#f8d7da",n.style.color="#721c24",n.style.borderColor="#f5c6cb",n.style.animation="none",setTimeout(()=>{n.style.animation="rating-alert-flash 0.5s ease-in-out"},10);return}const e=d[l],t=document.getElementById("rating-comment").value,o=document.querySelector('meta[name="csrf-token"]').getAttribute("content");fetch("/ratings/store",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":o,Accept:"application/json"},body:JSON.stringify({deal_id:u,rated_user_id:e.user_id,score:r,comment:t,role:e.role})}).then(n=>n.json()).then(n=>{if(n.success)if(l++,T(),l<d.length)R();else{s.style.display="none",L(),E(),f(u);const i=m();if(i.length>0)setTimeout(()=>{window.checkPendingRatings(i[0])},1500);else{const g=document.createElement("div");g.className="success-message",g.innerHTML="Спасибо за оценку всех специалистов!",document.body.appendChild(g),setTimeout(()=>{g.style.opacity="0",setTimeout(()=>{document.body.removeChild(g)},500)},3e3)}}else alert(n.message||"Произошла ошибка при сохранении оценки.")}).catch(n=>{console.error("[Рейтинги] Ошибка при отправке оценки:",n),alert("Произошла ошибка при сохранении оценки.")})});function R(){if(l>=d.length)return;const e=d[l];document.getElementById("rating-user-name").textContent=e.name,document.getElementById("rating-user-role").textContent=P(e.role),document.getElementById("rating-user-avatar").src=e.avatar_url||"/storage/icon/profile.svg",document.getElementById("current-rating-index").textContent=l+1,document.getElementById("total-ratings").textContent=d.length;const t=document.querySelector("#rating-modal h2"),o=document.querySelector(".rating-alert");e.role==="coordinator"?(t.textContent="Оцените качество планировочных координатора",document.querySelector(".rating-instruction").textContent="Оцените качество координации проекта от 1 до 5 звезд",o.textContent="Ваша оценка позволит улучшить работу координаторов"):e.role==="architect"?(t.textContent="Оценка работы архитектора",document.querySelector(".rating-instruction").textContent="Оцените качество планировочных решений от 1 до 5 звезд",o.textContent="Ваше мнение очень важно для нас и поможет улучшить качество работы архитекторов"):e.role==="designer"?(t.textContent="Оценка работы дизайнера",document.querySelector(".rating-instruction").textContent="Оцените качество дизайнерских решений от 1 до 5 звезд",o.textContent="Ваше мнение очень важно для нас и поможет улучшить качество работы дизайнеров"):e.role==="visualizer"?(t.textContent="Оценка работы визуализатора",document.querySelector(".rating-instruction").textContent="Оцените качество визуализаций от 1 до 5 звезд",o.textContent="Ваше мнение очень важно для нас и поможет улучшить качество работы визуализаторов"):(t.textContent="Оценка работы специалиста",document.querySelector(".rating-instruction").textContent="Оцените качество работы специалиста от 1 до 5 звезд",o.textContent="Для продолжения работы необходимо оценить всех специалистов по данной сделке"),r=0,p(0),document.getElementById("rating-comment").value=""}function P(e){return{architect:"Архитектор",designer:"Дизайнер",visualizer:"Визуализатор",coordinator:"Координатор",partner:"Партнер"}[e]||e}function L(){r=0,d=[],l=0,u=null,p(0),document.getElementById("rating-comment").value=""}if(typeof window.Laravel>"u"||!window.Laravel.user){console.error("[Рейтинги] Отсутствует объект window.Laravel или информация о пользователе");return}if(!window.Laravel.user.status||!window.Laravel.user.id){console.error("[Рейтинги] У пользователя отсутствует статус или ID");return}const C=["coordinator","partner","client","user"].includes(window.Laravel.user.status);if(console.log("[Рейтинги] Пользователь может оценивать:",C,"Статус:",window.Laravel.user.status),!C){console.log("[Рейтинги] Пользователь не может оценивать других по его статусу");return}window.checkPendingRatings=function(e){if(!e){console.warn("[Рейтинги] Вызов checkPendingRatings без dealId");return}console.log("[Рейтинги] Проверка ожидающих оценок для сделки:",e),h(e).then(t=>{var n;if(console.log("[Рейтинги] Проверка существования сделки перед запросом:",e,"Результат:",t),!t){console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",e),f(e);return}const o=(n=document.querySelector('meta[name="csrf-token"]'))==null?void 0:n.getAttribute("content");console.log("[Рейтинги] CSRF-токен для запроса:",o?"Получен":"Отсутствует"),fetch(`/ratings/check-pending?deal_id=${e}`,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":o,Accept:"application/json","Content-Type":"application/json"},credentials:"same-origin"}).then(i=>{if(console.log("[Рейтинги] Статус ответа API:",i.status),!i.ok)throw new Error(`HTTP error! Status: ${i.status}`);return i.json()}).then(i=>{if(console.log("[Рейтинги] Получены данные о необходимых оценках:",i),i.pending_ratings&&i.pending_ratings.length>0){console.log("[Рейтинги] Найдены пользователи для оценки:",i.pending_ratings.length),u=e,d=i.pending_ratings,l=0,localStorage.setItem(`pending_ratings_${e}`,JSON.stringify(d)),y(e);const g=document.getElementById("rating-modal");g?(console.log("[Рейтинги] Отображаем модальное окно для оценок"),_(),R(),g.style.display="flex",setTimeout(()=>{g.classList.add("show-rating-modal")},10)):console.error("[Рейтинги] Не найден элемент #rating-modal")}else{console.log("[Рейтинги] Нет пользователей для оценки или все уже оценены"),f(e);const g=m();g.length>0&&setTimeout(()=>{window.checkPendingRatings(g[0])},1e3)}}).catch(i=>{console.error("[Рейтинги] Ошибка при проверке ожидающих оценок:",i),f(e)})})};const b=document.createElement("style");b.textContent=`
            @keyframes rating-alert-flash {
                0% { transform: scale(1); }
                50% { transform: scale(1.03); background-color: #ffeeba; }
                100% { transform: scale(1); }
            }
            
            /* Стили для модального окна оценки */
            .rating-in-progress {
                overflow: hidden !important;
            }
            
            .rating-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .rating-modal-content {
                background: #fff;
                border-radius: 10px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            }
            
            .rating-user-info {
                display: flex;
                align-items: center;
                margin: 20px 0;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 8px;
            }
            
            .rating-avatar {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                margin-right: 15px;
                object-fit: cover;
            }
            
            .rating-stars {
                display: flex;
                justify-content: center;
                font-size: 30px;
                margin: 20px 0;
            }
            
            .star {
                cursor: pointer;
                color: #ddd;
                margin: 0 5px;
                transition: transform 0.2s;
            }
            
            .star:hover {
                transform: scale(1.2);
            }
            
            .star.active {
                color: #ffbf00;
            }
            
            .rating-comment textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                min-height: 100px;
                margin-top: 10px;
            }
            
            /* Информационные сообщения */
            .info-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: #e9f5ff;
                color: #0069d9;
                border: 1px solid #b8daff;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 9999;
                animation: fadeIn 0.3s ease-out;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `,document.head.appendChild(b);const x=m();x.length>0?(console.log("[Рейтинги] Найдены ID завершенных сделок в localStorage:",x),setTimeout(()=>{console.log("[Рейтинги] Запуск проверки оценок для первой сделки из списка после таймаута"),window.checkPendingRatings(x[0])},1500)):console.log("[Рейтинги] Нет ID завершенных сделок в localStorage"),S(),I(),setInterval(()=>{console.log("[Рейтинги] Запуск периодической проверки новых завершенных сделок"),v().then(e=>{if(e&&e.length>0){const t=m();let o=!1;for(const n of e)t.includes(n)||(y(n),o=!0);if(o){const n=m();n.length>0&&window.checkPendingRatings(n[0])}}})},5*60*1e3)}),window.runRatingCheck=function(s){if(console.log("[Рейтинги] Вызов runRatingCheck для сделки:",s),!s){console.error("[Рейтинги] Вызов runRatingCheck без ID сделки");return}const a=w();a.includes(s)||(a.push(s),localStorage.setItem("completed_deal_ids",JSON.stringify(a))),typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Запуск checkPendingRatings из runRatingCheck"),window.checkPendingRatings(s)):(console.error("[Рейтинги] Функция checkPendingRatings не определена"),setTimeout(()=>{typeof window.checkPendingRatings=="function"?(console.log("[Рейтинги] Функция найдена после таймаута, запуск"),window.checkPendingRatings(s)):console.error("[Рейтинги] Функция checkPendingRatings все еще не определена после таймаута")},2e3))};function w(){const s=localStorage.getItem("completed_deal_ids");return s?JSON.parse(s):[]}function I(){const s=[];for(let a=0;a<localStorage.length;a++){const c=localStorage.key(a);c&&(c.startsWith("pending_ratings_")||c==="completed_deal_ids")&&s.push(c)}s.forEach(a=>{if(a==="completed_deal_ids"){const c=JSON.parse(localStorage.getItem(a)||"[]"),r=[],d=c.map(l=>h(l).then(u=>{u&&r.push(l)}));Promise.all(d).then(()=>{localStorage.setItem("completed_deal_ids",JSON.stringify(r))})}else if(a.startsWith("pending_ratings_")){const c=a.replace("pending_ratings_","");h(c).then(r=>{if(!r){console.warn("[Рейтинги] Сделка не существует, очистка данных из хранилища:",c),localStorage.removeItem(a);const l=w().filter(u=>u!==c);localStorage.setItem("completed_deal_ids",JSON.stringify(l))}})}})}function h(s){return console.log("[Рейтинги] Проверка существования сделки:",s),s?new Promise(a=>{var c;fetch(`/deal/${s}/exists`,{method:"GET",headers:{"Content-Type":"application/json","X-Requested-With":"XMLHttpRequest","X-CSRF-TOKEN":(c=document.querySelector('meta[name="csrf-token"]'))==null?void 0:c.getAttribute("content"),Accept:"application/json"},credentials:"same-origin"}).then(r=>r.ok?r.json():(console.warn("[Рейтинги] Ошибка проверки сделки, HTTP-статус:",r.status),{exists:!1})).then(r=>{console.log("[Рейтинги] Результат проверки сделки:",r),a(r.exists===!0)}).catch(r=>{console.error("[Рейтинги] Ошибка при проверке сделки:",r),a(!1)})}):(console.error("[Рейтинги] Вызов verifyDealExists без ID сделки"),Promise.resolve(!1))}})();
