1. галерея в проде не пашет и голосование. скорее всего, проблема в JS ошибке. Найди, зачем это нужно там и почини.
   Uncaught TypeError: can't access property "getMusicInfo", window.musicLogics is undefined
   initComponents https://zxart.ee/javascript/set:project/file:project--1771085114.js:632
   fireEvent https://zxart.ee/javascript/set:project/file:project--1771085114.js:70
   domLoadedHandler https://zxart.ee/javascript/set:project/file:project--1771085114.js:66
   invokeTask https://zxart.ee/js/ng-zxart/main.js:1
   runTask https://zxart.ee/js/ng-zxart/main.js:1
   invokeTask https://zxart.ee/js/ng-zxart/main.js:1
   ht https://zxart.ee/js/ng-zxart/main.js:1
   wt https://zxart.ee/js/ng-zxart/main.js:1
   Lt https://zxart.ee/js/ng-zxart/main.js:1
   on https://zxart.ee/js/ng-zxart/main.js:1
   scheduleTask https://zxart.ee/js/ng-zxart/main.js:1
   scheduleTask https://zxart.ee/js/ng-zxart/main.js:1
   scheduleEventTask https://zxart.ee/js/ng-zxart/main.js:1
   J https://zxart.ee/js/ng-zxart/main.js:1
   addHandler_standards https://zxart.ee/javascript/set:project/file:project--1771085114.js:44
   init https://zxart.ee/javascript/set:project/file:project--1771085114.js:66
   controller https://zxart.ee/javascript/set:project/file:project--1771085114.js:71
   <anonymous> https://zxart.ee/javascript/set:project/file:project--1771085114.js:66
2. В списке комментариев нельзя ответить на комментарий. Сделай, пусть оно так же деревом и отвечает до первого обновления.
3. Раньше на главной были у каждого блока ссылки типа "Посмотреть ещё". Надо восстановить. Ссылки шли из модулей главной в базе, сейчас мы их тупо впишем в конфиг на главной. Сделай сервис, я дам список ссылок для всех языков и модулей. Кнопку можно поставить в module-title каждого блока, secondary и мелкую.
4. на скриншотах софта на главной нет оверлеев с железом. глянь что там требует компонент и доделай чтоб с бэка приходило.