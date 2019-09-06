
require('./bootstrap');

window.Vue = require('vue');

Vue.component('yandex-refresh-token', require('./components/Yandex/RefreshToken.vue'));


const app = new Vue({
    el: '#app',
    components: {
        YandexRefreshToken
    }
});
