import 'axios';

require('./bootstrap');

window.Vue = require('vue');
window.axios = require('axios');


Vue.component('customer-room', require('./components/Customer/CustomerRoom.vue'));
Vue.component('profile-user', require('./components/Customer/ProfileUser.vue'));

export const eventBus = new Vue();
export default eventBus;

const app = new Vue({
    el: '#app',

});

$(function () {

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('#getCode').on('click', function () {
        var params = "menubar=no,location=no,resizable=no,scrollbars=no,status=no";
        window.open('https://oauth.yandex.ru/authorize?response_type=code&client_id='+$('#clientId').val(), 'Yandex', params);
    });

    $('#refreshToken').on('click', function () {

        if($('#code').val()==='') {
            alert('Для успешного обновления токена необходимо ввести код.');
        }
        else {
            console.log('start refresh token');
            $.ajax({
                url: "/admin/refresh_token",
                method: "POST",
                data: {
                    code: $('#code').val()
                },
                success: function (response) {
                    console.log('result', response);
                    alert('Токен успешно обновлен!',response.message);
                    document.location = '/admin/';
                },
                error: function (result) {
                    alert('Error refresh token', result);
                }
            });
        }

    });


});

