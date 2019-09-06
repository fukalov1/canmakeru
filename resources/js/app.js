import 'jquery'

require('./bootstrap');

window.Vue = require('vue');

import YandexRefreshToken from './components/Yandex/RefreshToken.vue';

Vue.component('yandex-refresh-token', YandexRefreshToken);


const app = new Vue({
    el: '#app',
    components: {
        YandexRefreshToken
    }
});
